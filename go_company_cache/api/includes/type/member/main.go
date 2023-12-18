package member

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/includes/type/db/company_data"
)

// GetList получаем информацию о нескольких пользовтелях из кэша
func (store *Storage) GetList(ctx context.Context, companyDataConn *company_data.DbConn, userList []int64) ([]*company_data.MemberRow, []int64) {

	var userListStruct []*company_data.MemberRow

	// делим пользователей на тех которых надо получить из базы и получили из кэша
	needFetchUserIdList, userListStruct := store.splitUserList(userList)
	subChannelList := store.doSubUserListOnChanList(ctx, needFetchUserIdList, companyDataConn)
	notFoundUserList := make([]int64, 0)
	for k, v := range subChannelList {

		err := waitUntilMemberAddedToCache(v, k)
		if err != nil {

			notFoundUserList = append(notFoundUserList, k)
			continue
		}

		userInfo, isExist := store.mainStorage.getMemberItemFromCache(k)
		if !isExist {

			log.Errorf("Не получили пользователя %d из кэша после ожидания канала", k)
			notFoundUserList = append(notFoundUserList, k)
			continue
		}

		userListStruct = append(userListStruct, userInfo.memberRow)
	}
	return userListStruct, notFoundUserList
}

// GetOne получаем информацию об одном пользователе из кэша
func (store *Storage) GetOne(ctx context.Context, companyDataConn *company_data.DbConn, userId int64) (*company_data.MemberRow, bool) {

	// пытаемся получить пользователя из кэша
	memberItem, exist := store.mainStorage.getMemberItemFromCache(userId)

	// если нашли пользователя в кэше, просто его возвращаем
	if exist {

		return memberItem.memberRow, exist
	}

	// подписываем пользователя на канал и ждем, пока получим его с базы
	subChannel := store.doSubUserOnChan(ctx, userId, companyDataConn)

	_ = waitUntilMemberAddedToCache(subChannel, userId)

	memberItem, exist = store.mainStorage.getMemberItemFromCache(userId)
	if !exist {

		log.Errorf("Не получили пользователя %d из кэша после ожидания канала", userId)
		return nil, false
	}

	return memberItem.memberRow, exist
}

// делим пользователей на тех которых надо получить из базы и получили из кэша
func (store *Storage) splitUserList(userList []int64) ([]int64, []*company_data.MemberRow) {

	needFetchUserIdList := make([]int64, 0)
	var userListStruct []*company_data.MemberRow
	for _, userId := range userList {

		memberItem, exist := store.mainStorage.getMemberItemFromCache(userId)
		if !exist {

			needFetchUserIdList = append(needFetchUserIdList, userId)
			continue
		}

		userListStruct = append(userListStruct, memberItem.memberRow)
	}

	return needFetchUserIdList, userListStruct
}

// DeleteMemberFromCache удаляем из кэша по userID
func (store *Storage) DeleteMemberFromCache(userID int64) {

	store.mainStorage.deleteFromStore(userID)
}

// DeleteMemberListFromCache удаляем из кэша по списку user_id
func (store *Storage) DeleteMemberListFromCache(userIdList []int64) {

	store.mainStorage.deleteListFromStore(userIdList)
}

// очистить кэш пользователей
func (store *Storage) ClearCache() {

	store.mainStorage.clear()
}
