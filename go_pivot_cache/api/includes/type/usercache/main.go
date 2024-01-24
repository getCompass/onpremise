package usercache

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pivot_cache/api/conf"
	"time"
)

// ---------------------------------------------
// PUBLIC
// ---------------------------------------------

// получение пользовательскя
func GetUserInfoRow(ctx context.Context, userId int64) (map[string]string, error) {

	// получаем инфу из основного хранилища
	userInfo, exist := mainUserStore.getUserInfoFromCache(userId)
	if !exist || userInfo.err != nil {

		// подписываем на канал и ждем пока пользователь добавится в кэш
		err := waitUntilUserAddedToCache(ctx, userId)
		if err != nil {

			log.Errorf("error: %v", err)
			return nil, err
		}

		// если так и не появилась
		userInfo, exist = mainUserStore.getUserInfoFromCache(userId)
		if !exist {
			return nil, fmt.Errorf("не смогли получить данные для userId: %d", userId)
		}
	}

	// обработка случая когда пользователь не найден
	if len(userInfo.userRow) < 1 {
		return nil, fmt.Errorf("не смогли получить данные для userId: %d", userId)
	}

	return userInfo.userRow, nil
}

// получить список информации о пользователях
func GetUserInfoRows(ctx context.Context, userIdList []int64) ([]map[string]string, error) {

	// получаем инфу из основного хранилища
	userListInfo := make([]map[string]string, 0)
	userIdListSorted := make([]int64, 0)

	// перебираем список и делим его на два - те, которые есть в кэше, и нет
	for _, userId := range userIdList {
		userInfo, exist := mainUserStore.getUserInfoFromCache(userId)

		//если записи в кэше не существует, сохраняем id для запроса в базу
		if !exist && userInfo.err == nil {
			userIdListSorted = append(userIdListSorted, userId)

			//иначе сохраняем в итоговый список
		} else if exist && userInfo.err == nil {
			userListInfo = append(userListInfo, userInfo.userRow)
		}
	}

	//если есть id для запроса в базу, запускаем поиск
	if len(userIdListSorted) > 0 {
		err := waitUntilUserListAddedToCache(ctx, userIdList)
		if err != nil {
			return nil, err
		}

		// если так и не появилась
		for _, userId := range userIdListSorted {
			userInfo, exist := mainUserStore.getUserInfoFromCache(userId)
			if !exist {

				continue
			}

			//добавляем запись в итоговый список
			userListInfo = append(userListInfo, userInfo.userRow)
		}
	}
	return userListInfo, nil
}

// получаем ждем пока в кэше 2 появтися инфа
func waitUntilUserAddedToCache(ctx context.Context, userId int64) error {

	sub := doSubOnChan(ctx, userId)
	select {
	case <-sub:

		return nil

		// добавляем timeout для прослушки
	case <-time.After(time.Millisecond * conf.GetConfig().GetUserTimeoutMs):

		return fmt.Errorf("не смогли получить из канала: %v для userId: %d", sub, userId)
	}
}

// получаем ждем пока в кэше появтися инфа о списке пользователей
func waitUntilUserListAddedToCache(ctx context.Context, userIdList []int64) error {

	sub := doSubOnChanList(ctx, userIdList)
	select {
	case <-sub:

		return nil

		// добавляем timeout для прослушки
	case <-time.After(time.Millisecond * conf.GetConfig().GetUserTimeoutMs):

		return fmt.Errorf("не смогли получить из канала")
	}
}

// удаляем информацию о пользователе из кэша
func DeleteFromCache(userId int64) {

	mainUserStore.delete(userId)
}

func ResetCache() {

	mainUserStore.reset()
}
