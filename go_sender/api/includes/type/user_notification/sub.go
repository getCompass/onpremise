package user_notification

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/db/company_data"
	"sync"
)

// структура хранилища с подписчиками
type SubStorage struct {
	mu    sync.RWMutex
	cache map[int64]chan bool
}

func MakeSubStorage() *SubStorage {

	return &SubStorage{
		mu:    sync.RWMutex{},
		cache: make(map[int64]chan bool),
	}
}

// функция для подписки на канал
func (userNotificationSubStore *SubStorage) doSubUserListOnChanList(ctx context.Context, store *UserNotificationStorage, companyDataDbConn *company_data.DbConn,
	userIdList []int64) map[int64]chan bool {

	subChannelList := make(map[int64]chan bool)
	var needFetchUserIdList []int64
	for _, userId := range userIdList {

		// если уже существует то добавляем в массив канал иначем созадем
		userNotificationSubStore.mu.Lock()
		subChannel, isExist := userNotificationSubStore.cache[userId]
		if isExist {

			subChannelList[userId] = subChannel
			userNotificationSubStore.mu.Unlock()
			continue
		}

		// создаем канал
		subChannelList[userId] = make(chan bool, 1)
		userNotificationSubStore.cache[userId] = subChannelList[userId]
		userNotificationSubStore.mu.Unlock()
		needFetchUserIdList = append(needFetchUserIdList, userId)
	}

	go func() {

		log.Errorf("companyDataDbConn %v", companyDataDbConn)
		store.addUserNotificationListToMainStore(ctx, needFetchUserIdList, companyDataDbConn)
		userNotificationSubStore.closeChannelList(needFetchUserIdList)
	}()
	return subChannelList
}

// добавляем пользователей в основное хранилище
func (store *UserNotificationStorage) addUserNotificationListToMainStore(ctx context.Context, needFetchUserIdList []int64, companyDataDbConn *company_data.DbConn) {

	if len(needFetchUserIdList) < 1 {
		return
	}

	userNotificationList, err := companyDataDbConn.GetMemberNotificationList(ctx, needFetchUserIdList)

	for _, row := range userNotificationList {
		store.doCacheUserNotificationItem(row.UserId, row, err)
	}
}

// функция для закрытия множества каналов
func (userNotificationSubStore *SubStorage) closeChannelList(userIdList []int64) {

	for _, userId := range userIdList {
		userNotificationSubStore.closeChannel(userId)
	}
}

// закрываем канал для юзера
func (userNotificationSubStore *SubStorage) closeChannel(userId int64) {

	userNotificationSubStore.mu.Lock()
	defer userNotificationSubStore.mu.Unlock()

	// достаем канал из кэша и закрываем его
	subChannel, exist := userNotificationSubStore.cache[userId]
	if !exist {
		return
	}

	close(subChannel)
	delete(userNotificationSubStore.cache, userId)
}
