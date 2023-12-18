package user_notification

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/db/company_data"
	"sync"
)

// основное хранилище
type UserNotificationStorage struct {
	mu    sync.RWMutex
	cache map[int64]*userNotificationStruct
}

// структура объекта
type userNotificationStruct struct {
	userId              int64
	dateCached          int64
	userNotificationRow *company_data.NotificationRow

	// проброс ошибки из БД
	err error
}

// получаем из кеша
func (store *UserNotificationStorage) getUserNotificationItemFromCache(userId int64) (*userNotificationStruct, bool) {

	// получаем из cache
	store.mu.RLock()
	userNotificationItem, isExist := store.cache[userId]
	store.mu.RUnlock()

	return userNotificationItem, isExist
}

// сохраняем в кэш
func (store *UserNotificationStorage) doCacheUserNotificationItem(userId int64, userNotificationRow *company_data.NotificationRow, err error) {

	// получаем объект из хранилища
	userNotificationItem := &userNotificationStruct{}

	userNotificationItem.dateCached = functions.GetCurrentTimeStamp()
	userNotificationItem.userId = userNotificationRow.UserId
	userNotificationItem.userNotificationRow = userNotificationRow
	userNotificationItem.err = err

	store.mu.Lock()
	_, exist := store.cache[userId]
	if !exist || err == nil {
		store.cache[userId] = userNotificationItem
	}
	store.mu.Unlock()
}

// удаляем кеш по userId
func (store *UserNotificationStorage) deleteFromStore(userId int64) {

	store.mu.Lock()
	defer store.mu.Unlock()

	// удаляем из хранилища
	delete(store.cache, userId)
}

// получаем пользователя по userId
func (store *UserNotificationStorage) get(userId int64) (*userNotificationStruct, bool) {

	store.mu.RLock()
	defer store.mu.RUnlock()

	// получаем из хранилища
	item, exist := store.cache[userId]

	// если не существует
	if !exist {

		log.Error(fmt.Sprintf("не найден userId в хранилище: %d", userId))
		return nil, false
	}

	return item, true
}
