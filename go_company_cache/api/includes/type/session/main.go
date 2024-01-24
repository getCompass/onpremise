package session

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_company_cache/api/includes/type/db/company_data"
)

// получение пользовательской сессии
func (store *Storage) GetUserSessionRow(ctx context.Context, sessionUniq string, companyDataConn *company_data.DbConn) (*company_data.SessionRow, int64, error) {

	// получаем инфу из основного хранилища
	sessionItem, exist := store.getSessionItemFromCache(sessionUniq)

	//  запрос заново в случае отсутствия данных или наличия ошибки
	if !exist || sessionItem.err != nil {

		// подписываем на канал и ждем пока сессия добавится в кэш
		store.waitUntilSessionAddedToCache(ctx, sessionUniq, companyDataConn)

		// если так и не появилась
		sessionItem, exist = store.getSessionItemFromCache(sessionUniq)
		if sessionItem.err != nil {
			return nil, 0, sessionItem.err
		}
	}

	// обработка случая когда сессия не найдена
	if !exist {
		return nil, 0, nil
	}

	return sessionItem.userSessionRow, sessionItem.userId, sessionItem.err
}

// удаляем сессии из кэша по userID
func (store *Storage) DeleteUserSessionList(userID int64) {

	// получаем список всех сессий пользователя uniq.go
	sessionUniqSlice := store.sessionUniqStorage.get(userID)

	// пробегаемся по полученному списку сессий
	for _, sessionUniq := range sessionUniqSlice {
		store.deleteBySessionUniq(sessionUniq)
	}

	// удаляем userID => []string uniq.go
	store.sessionUniqStorage.delete(userID)
}

// получить список сессий пользователя в кэше
func (store *Storage) GetListByUserId(userID int64) []string {

	// получаем список всех сессий пользователя
	return store.sessionUniqStorage.get(userID)

}

// удялем сессию из кеша по sessionUniq
func (store *Storage) DeleteSessionItem(sessionUniq string) int64 {

	// получаем сессию по из хранилища
	sessionItem, exist := store.get(sessionUniq)

	// если сессия не существует, нечего не делаем
	if !exist {
		return sessionItem.userId
	}

	// удаляем sessionUniq из слайса по UserID
	store.sessionUniqStorage.deleteSessionUniq(sessionItem.userId, sessionUniq)

	// удаляем сессию из хранилища
	store.deleteBySessionUniq(sessionUniq)

	return sessionItem.userId
}

// удаляем каждый час сессии которые не были использованы давно
func (store *Storage) DeleteUnusedSessions() {

	// получаем временную метку, по которой потом произойдет удаление из хранилища
	timestamp := functions.GetCurrentTimeStamp() - sessionExpirationInterval
	store.deleteUnusedSessions(timestamp)
}

// очистить кэш
func (store *Storage) ClearCache() {

	store.clear()
}
