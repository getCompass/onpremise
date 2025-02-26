package session

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pivot_cache/api/conf"
	"time"
)

// получение пользовательской сессии
func GetUserSessionRow(ctx context.Context, sessionUniq string, tableID string, shardID string) (map[string]string, int64, error) {

	// получаем инфу из основного хранилища
	sessionItem, exist := mainSessionStore.getSessionItemFromCache(sessionUniq)

	//  запрос заново в случае отсутствия данных или наличия ошибки
	if !exist || sessionItem.err != nil {

		// подписываем на канал и ждем пока сессия добавится в кэш
		// ожидание вернет ошибку, если случился таймаут
		err := waitUntilSessionAddedToCache(ctx, shardID, tableID, sessionUniq)
		if err != nil {
			return nil, 0, err
		}

		// если так и не появилась
		sessionItem, exist = mainSessionStore.getSessionItemFromCache(sessionUniq)
		if sessionItem.err != nil {
			return nil, 0, sessionItem.err
		}
	}

	// обработка случая когда сессия не найдена
	if !exist {
		return nil, 0, nil
	}

	return sessionItem.userSessionRow, sessionItem.userId, nil
}

// получаем ждем пока в кэше 2 появится инфа
// если ожидание отваливается по таймауту, отдаем ошибку
func waitUntilSessionAddedToCache(ctx context.Context, shardID string, tableID string, sessionUniq string) error {

	sub := doSubOnChan(ctx, shardID, tableID, sessionUniq)
	select {
	case <-sub:

		return nil

		// добавляем timeout для прослушки
	case <-time.After(time.Millisecond * conf.GetConfig().GetUserTimeoutMs):

		log.Errorf("не смогли получить из канала: %v для sessionUniq: %s", sub, sessionUniq)
		return fmt.Errorf("не смогли получить из канала: %v для sessionUniq: %s", sub, sessionUniq)
	}
}

// удаляем сессии из кэша по userID
func DeleteUserSessionList(userID int64) {

	// получаем список всех сессий пользователя uniq.go
	sessionUniqSlice := sessionUniqObj.get(userID)

	// пробегаемся по полученному списку сессий
	for _, sessionUniq := range sessionUniqSlice {
		mainSessionStore.deleteBySessionUniq(sessionUniq)
	}

	// удаляем userID => []string uniq.go
	sessionUniqObj.delete(userID)
}

// удялем сессию из кеша по sessionUniq
func DeleteSessionItem(sessionUniq string) int64 {

	// получаем сессию по из хранилища
	sessionItem, exist := mainSessionStore.get(sessionUniq)

	// если сессия не существует, нечего не делаем
	if !exist {
		return sessionItem.userId
	}

	// удаляем sessionUniq из слайса по UserID
	sessionUniqObj.deleteSessionUniq(sessionItem.userId, sessionUniq)

	// удаляем сессию из хранилища
	mainSessionStore.deleteBySessionUniq(sessionUniq)

	return sessionItem.userId
}

// удаляем каждый час сессии которые не были использованы давно
func DeleteUnusedSessions() {

	// получаем временную метку, по которой потом произойдет удаление из хранилища
	timestamp := functions.GetCurrentTimeStamp() - sessionExpirationInterval
	mainSessionStore.deleteUnusedSessions(timestamp)
}

// получение статуса сессии
func GetSessionCacheStatus(sessionUniq string) bool {

	mainSessionStore.mu.Lock()

	defer mainSessionStore.mu.Unlock()

	// получаем сессию из cache
	_, exist := mainSessionStore.cache[sessionUniq]
	return exist
}

// сбросить кэш сессий
func ResetCache() {

	mainSessionStore.reset()
}

// обновить last_online_at сессий
func UpdateLastOnlineAt() {

	mainSessionStore.updateLastOnlineAt()
}
