package user_notification

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/db/company_data"
	"sync"
	"time"
)

func MakeUserNotificationStorage() *UserNotificationStorage {

	return &UserNotificationStorage{
		mu:    sync.RWMutex{},
		cache: make(map[int64]*userNotificationStruct),
	}
}

// получаем информацию о нескольких пользователях из кэша
func (store *UserNotificationStorage) GetList(ctx context.Context, userNotificationSubStore *SubStorage, companyDataDbConn *company_data.DbConn,
	userList []int64) ([]*company_data.NotificationRow, []int64) {

	var userListStruct []*company_data.NotificationRow

	// делим пользователей на тех которых надо получить из базы и получили из кэша
	needFetchUserIdList, userListStruct := store.splitUserList(userList)
	subChannelList := userNotificationSubStore.doSubUserListOnChanList(ctx, store, companyDataDbConn, needFetchUserIdList)
	notFoundUserList := make([]int64, 0)
	for k, v := range subChannelList {

		err := waitUntilUserNotificationAddedToCache(v, k)
		if err != nil {

			notFoundUserList = append(notFoundUserList, k)
			continue
		}

		userInfo, isExist := store.getUserNotificationItemFromCache(k)
		if !isExist {

			log.Errorf("Не получили пользователя %d из кэша после ожидания канала", k)
			notFoundUserList = append(notFoundUserList, k)
			continue
		}

		userListStruct = append(userListStruct, userInfo.userNotificationRow)
	}

	return userListStruct, notFoundUserList
}

// делим пользователей на тех которых надо получить из базы и получили из кэша
func (store *UserNotificationStorage) splitUserList(userList []int64) ([]int64, []*company_data.NotificationRow) {

	needFetchUserIdList := make([]int64, 0)
	var userListStruct []*company_data.NotificationRow
	for _, userId := range userList {

		userNotificationItem, exist := store.getUserNotificationItemFromCache(userId)
		if !exist {

			needFetchUserIdList = append(needFetchUserIdList, userId)
			continue
		}

		userListStruct = append(userListStruct, userNotificationItem.userNotificationRow)
	}

	return needFetchUserIdList, userListStruct
}

// получаем ждем пока в кэше 2 появтися инфа
func waitUntilUserNotificationAddedToCache(sub chan bool, userId int64) error {

	select {
	case <-sub:

		return nil

		// добавляем timeout для прослушки
	case <-time.After(time.Millisecond * 1500):

		err := fmt.Errorf("не смогли получить из канала: %v для userId: %d за 2 секунды", sub, userId)
		log.Errorf("%v", err)
		return err
	}
}

// удаляем из кэша по userID
func (store *UserNotificationStorage) DeleteUserNotificationFromCache(userID int64) {

	store.deleteFromStore(userID)
}
