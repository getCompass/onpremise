package session

// пакет, в который вынесена вся бизнес-логика группы методов session
import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_pivot_cache/api/includes/type/session"
	"go_pivot_cache/api/includes/type/usercache"
	errorStatus "google.golang.org/grpc/status"
)

// получаем информацию по сессии
func GetInfo(ctx context.Context, sessionUniq string, shardID string, tableID string) (int64, int32, error) {

	// получаем пользовательскую сессию и информацию о ней
	userSessionRow, userId, err := session.GetUserSessionRow(ctx, sessionUniq, tableID, shardID)
	if err != nil {
		return 0, 0, errorStatus.Error(500, "database error")
	}
	if userSessionRow == nil {
		return 0, 0, errorStatus.Error(902, "session not found")
	}

	return userId, functions.StringToInt32(userSessionRow["refreshed_at"]), nil
}

// удалить все сессии пользователя по его userID
func DeleteByUserId(userId int64) {

	session.DeleteUserSessionList(userId)

	// удаляем объект пользователя из кэша
	usercache.DeleteFromCache(userId)
}

// удалить конкретную сессию из кэша
func DeleteBySessionUniq(sessionUniq string) {

	// удаляем сессию из кеша по sessionUniq
	session.DeleteSessionItem(sessionUniq)
}

// удаляет информацио о пользователе, не трогает сессию
func DeleteUserInfo(userId int64) {

	// удаляем объект пользователя из кэша
	usercache.DeleteFromCache(userId)
}

// метод для получения статуса сессии в кэше
func GetSessionCacheStatus(sessionUniq string) (isExist bool) {

	return session.GetSessionCacheStatus(sessionUniq)
}

// сбрасывает кэш сессий
func ResetCache() {

	session.ResetCache()
}
