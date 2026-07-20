package apitoken

import (
	"database/sql"
	"encoding/json"
	"go_auth/internal/database"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

// InitCacheManager инициализировать менеджер кеша
func InitCacheManager(db *database.Database, cache *ApiTokenCache) *ApiTokenCacheManager {

	defer log.Success("api token cache manager loaded")

	cm := &ApiTokenCacheManager{
		apiTokenCache: cache,
		db:            db,
		invalidateCh:  make(chan InvalidationEvent, 1000),
		stopCh:        make(chan struct{}),
	}

	cm.wg.Add(3)
	go cm.processInvalidations()
	go cm.periodicCleanup()
	go cm.periodicDeleteExpired()

	return cm
}

// получить токен
func (cm *ApiTokenCacheManager) Get(userId int64, apiToken string) (*ApiToken, error) {

	key := ApiTokenKey{UserId: userId, ApiToken: apiToken}

	if cachedApiToken, err := cm.apiTokenCache.Get(key); err == nil {

		// если записи не существует, возвращаем ошибку, что токена не существует
		if !cachedApiToken.Exists {
			return nil, ErrApiTokenNotFound
		}

		return cachedApiToken, nil
	}

	// промах по кешу, идем в базу
	apiTokenRecord, err := cm.db.GetApiToken(key.UserId, key.ApiToken)

	// если не нашли в базе, добавляем запись об этом в кеш
	if err == sql.ErrNoRows {

		cm.apiTokenCache.Set(key, &ApiToken{
			UserId:   key.UserId,
			ApiToken: key.ApiToken,
			Exists:   false,
			CachedAt: time.Now(),
		})

		return nil, ErrApiTokenNotFound
	}

	// если ошибка не в том, что не нашли, пишем в лог ошибку доступа к БД
	if err != nil {
		return nil, ErrDatabase
	}

	// добавляем в кеш информацию о токене
	var sli ScopeListInt

	err = json.Unmarshal(apiTokenRecord.ScopeListInt, &sli)

	if err != nil {
		return nil, ErrInvalidScopeList
	}

	var e ApiTokenExtra

	err = json.Unmarshal(apiTokenRecord.Extra, &e)

	if err != nil {
		return nil, err
	}

	apiTokenStruct := &ApiToken{
		UserId:       apiTokenRecord.UserId,
		ApiToken:     apiTokenRecord.ApiToken,
		CreatedAt:    time.Unix(apiTokenRecord.CreatedAt, 0),
		UpdatedAt:    time.Unix(apiTokenRecord.UpdatedAt, 0),
		ExpiresAt:    time.Unix(apiTokenRecord.ExpiresAt, 0),
		Name:         apiTokenRecord.Name,
		ScopeListInt: sli,
		Extra:        e,
		Exists:       true,
		CachedAt:     time.Now(),
	}

	cm.apiTokenCache.Set(key, apiTokenStruct)

	return apiTokenStruct, nil
}

// получить количество активных токенов для пользователя
func (cm *ApiTokenCacheManager) GetActiveCount(userId int64) (int, error) {

	// список в кеш не попадает, незачем
	apiTokenRecordList, err := cm.db.GetApiTokenList(userId)

	// если ошибка не в том, что не нашли, пишем в лог ошибку доступа к БД
	if err != nil {

		log.Errorf("%v", err)
		return 0, ErrDatabase
	}

	count := 0
	for _, record := range apiTokenRecordList {

		if time.Now().Unix() > record.ExpiresAt {
			continue
		}

		count++
	}
	return count, nil
}

// получить список токенов
func (cm *ApiTokenCacheManager) GetList(userId int64) ([]*ApiToken, error) {

	// список в кеш не попадает, незачем
	apiTokenRecordList, err := cm.db.GetApiTokenList(userId)

	// если ошибка не в том, что не нашли, пишем в лог ошибку доступа к БД
	if err != nil {
		return nil, ErrDatabase
	}

	apiTokenList := make([]*ApiToken, 0, len(apiTokenRecordList))

	for _, record := range apiTokenRecordList {

		// формируем информацию о токене
		var sli ScopeListInt

		err = json.Unmarshal(record.ScopeListInt, &sli)

		if err != nil {

			log.Errorf("Invalid scope list for user_id = %d api_token = %s", record.UserId, record.ApiToken)
			continue
		}

		var e ApiTokenExtra

		err = json.Unmarshal(record.Extra, &e)

		if err != nil {

			log.Errorf("Invalid extra for user_id = %d api_token = %s", record.UserId, record.ApiToken)
			continue
		}

		apiTokenList = append(apiTokenList, &ApiToken{
			UserId:       record.UserId,
			ApiToken:     record.ApiToken,
			CreatedAt:    time.Unix(record.CreatedAt, 0),
			UpdatedAt:    time.Unix(record.UpdatedAt, 0),
			ExpiresAt:    time.Unix(record.ExpiresAt, 0),
			Name:         record.Name,
			ScopeListInt: sli,
			Extra:        e,
			Exists:       true,
			CachedAt:     time.Unix(0, 0),
		})
	}

	return apiTokenList, nil
}

// создать токен
func (cm *ApiTokenCacheManager) Create(apiToken *ApiToken) (*ApiToken, error) {

	scopeListJson, err := json.Marshal(apiToken.ScopeListInt)

	if err != nil {
		return nil, ErrInvalidScopeList
	}

	extraJson, err := json.Marshal(apiToken.Extra)

	if err != nil {
		return nil, err
	}

	apiTokenRecord := &database.ApiTokenRecord{
		UserId:       apiToken.UserId,
		ApiToken:     apiToken.ApiToken,
		CreatedAt:    apiToken.CreatedAt.Unix(),
		UpdatedAt:    apiToken.UpdatedAt.Unix(),
		ExpiresAt:    apiToken.ExpiresAt.Unix(),
		Name:         apiToken.Name,
		ScopeListInt: scopeListJson,
		Extra:        extraJson,
	}
	_, err = cm.db.CreateApiToken(apiTokenRecord)

	if err != nil {
		return nil, err
	}

	// отправляем ивент для инвалидации текущей записи в кеше
	cm.invalidateCh <- InvalidationEvent{
		UserId:    apiToken.UserId,
		ApiToken:  apiToken.ApiToken,
		Reason:    "create",
		CreatedAt: time.Now(),
	}

	return apiToken, nil
}

// обновить токен
func (cm *ApiTokenCacheManager) Update(apiToken *ApiToken) error {

	_, err := cm.db.GetApiToken(apiToken.UserId, apiToken.ApiToken)

	if err != nil && err != sql.ErrNoRows {
		return err
	}

	scopeListJson, err := json.Marshal(apiToken.ScopeListInt)

	if err != nil {
		return err
	}

	extraJson, err := json.Marshal(apiToken.Extra)

	if err != nil {
		return err
	}

	apiTokenRecord := &database.ApiTokenRecord{
		UserId:       apiToken.UserId,
		ApiToken:     apiToken.ApiToken,
		CreatedAt:    apiToken.CreatedAt.Unix(),
		UpdatedAt:    apiToken.UpdatedAt.Unix(),
		ExpiresAt:    apiToken.ExpiresAt.Unix(),
		Name:         apiToken.Name,
		ScopeListInt: scopeListJson,
		Extra:        extraJson,
	}

	if _, err = cm.db.UpdateApiToken(apiTokenRecord); err != nil {
		return err
	}

	cm.invalidateCh <- InvalidationEvent{
		UserId:    apiToken.UserId,
		ApiToken:  apiToken.ApiToken,
		Reason:    "update",
		CreatedAt: time.Now(),
	}

	return nil
}

// удалить токен
func (cm *ApiTokenCacheManager) Delete(userId int64, apiToken string) error {

	apiTokenRecord, err := cm.db.GetApiToken(userId, apiToken)

	if err != nil && err != sql.ErrNoRows {
		return err
	}

	if err := cm.db.DeleteApiToken(userId, apiToken); err != nil {
		return err
	}

	// если запись в базе была - отправляем задачу на инвалидацию
	if apiTokenRecord != nil {

		cm.invalidateCh <- InvalidationEvent{
			UserId:    apiTokenRecord.UserId,
			ApiToken:  apiTokenRecord.ApiToken,
			Reason:    "delete",
			CreatedAt: time.Now(),
		}
	}

	return nil

}

// рутина для обработки событий инвалидаций токенов
func (cm *ApiTokenCacheManager) processInvalidations() {

	defer cm.wg.Done()

	for {
		select {
		case <-cm.stopCh:
			return
		case event := <-cm.invalidateCh:
			cm.handleInvalidation(event)
		}
	}
}

// обработать инвалидацию токена
func (cm *ApiTokenCacheManager) handleInvalidation(event InvalidationEvent) {

	key := ApiTokenKey{event.UserId, event.ApiToken}
	cm.apiTokenCache.Delete(key)
}

// рутина для очистки кеша от неиспользуемых токенов
func (cm *ApiTokenCacheManager) periodicCleanup() {

	defer cm.wg.Done()

	ticker := time.NewTicker(1 * time.Minute)

	for {
		select {
		case <-cm.stopCh:
			return
		case <-ticker.C:
			cm.apiTokenCache.Cleanup()
		}
	}
}

// рутина для удаления просроченных токенов
func (cm *ApiTokenCacheManager) periodicDeleteExpired() {

	defer cm.wg.Done()

	ticker := time.NewTicker(1 * time.Hour)

	for {
		select {
		case <-cm.stopCh:
			return
		case t := <-ticker.C:

			// работаем в час ночи
			if t.Hour() != 1 {
				continue
			}

			// удаляем из базы
			now := time.Now()
			count := cm.db.CountApiTokensByExpiresAt(now.Unix())
			err := cm.db.DeleteApiTokensByExpiresAt(now.Unix(), count)

			if err != nil {
				log.Errorf("couldnt delete expired tokens because of error: %v", err)
			}

			// удаляем из кеша
			cm.apiTokenCache.DeleteExpired()

			// оптимизируем таблицу после удаления
			err = cm.db.OptimizeApiTokenTable()

			if err != nil {
				log.Errorf("couldnt optimize table after deletion: %v", err)
			}
		}
	}
}
