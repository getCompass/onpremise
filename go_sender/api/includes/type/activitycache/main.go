package activitycache

import (
	"fmt"
)

// ---------------------------------------------
// PUBLIC
// ---------------------------------------------

// AddActivity добавляет данные активности пользователя в кэш
func AddActivity(userId int64, activityTimestamp int64, sessionUniq string) error {

	var err error

	if userId < 1 {
		return fmt.Errorf("invalid userId")
	}

	// добавляем или обновляем запись в кэш addedUserStore
	mainActivityStore.doCacheActivity(userId, activityTimestamp, sessionUniq, err)

	return nil
}

// функция получает все записи на добавление активности
func GetAllActivity() [][3]interface{} {

	mainActivityStore.mu.RLock()
	defer mainActivityStore.mu.RUnlock()

	// создаём слайс для хранения троек [userID, sessionUniq, timestamp]
	activities := make([][3]interface{}, 0)

	// перебираем все записи из кэша
	for userID, sessions := range mainActivityStore.cache {
		for sessionUniq, timestamp := range sessions {
			activities = append(activities, [3]interface{}{userID, sessionUniq, timestamp})
		}
	}

	return activities
}

// ResetCache сбрасываем кеш
func ResetCache() {

	mainActivityStore.reset()
}
