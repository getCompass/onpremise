package member

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/conf"
	"go_company_cache/api/includes/type/db/company_data"
	"sync"
	"time"
)

// структура хранилища с подписчиками
type subStorage struct {
	mu    sync.RWMutex
	cache map[int64]chan bool
}

// функция для подписки на канал одного пользователя
func (store *Storage) doSubUserOnChan(ctx context.Context, userId int64, companyDataConn *company_data.DbConn) chan bool {

	// если уже существует то добавляем в массив канал иначем созадем
	store.subStorage.mu.Lock()
	subChannel, isExist := store.subStorage.cache[userId]
	if isExist {

		store.subStorage.mu.Unlock()
		return subChannel
	}

	// создаем канал
	subChannel = make(chan bool, 1)
	store.subStorage.cache[userId] = subChannel
	store.subStorage.mu.Unlock()

	go store.addUserToMainStore(ctx, userId, companyDataConn)
	return subChannel
}

// добавляем одного пользователя в основное хранилище
func (store *Storage) addUserToMainStore(ctx context.Context, userId int64, companyDataConn *company_data.DbConn) {

	member, err := companyDataConn.GetMemberRow(ctx, userId)

	defer store.subStorage.closeChannel(userId)

	// если появилась ошибка
	if err != nil {

		log.Errorf("Error found %v", err)
		return
	}

	// если не нашли пользователя
	if member == nil {

		log.Errorf("User not found %d", userId)
		return
	}

	store.mainStorage.doCacheMemberItem(userId, member)
}

// функция для подписки на канал
func (store *Storage) doSubUserListOnChanList(ctx context.Context, userIdList []int64, companyDataConn *company_data.DbConn) map[int64]chan bool {

	subChannelList := make(map[int64]chan bool)
	var needFetchUserIdList []int64
	for _, userId := range userIdList {

		// если уже существует то добавляем в массив канал иначем созадем
		store.subStorage.mu.Lock()
		subChannel, isExist := store.subStorage.cache[userId]
		if isExist {

			subChannelList[userId] = subChannel
			store.subStorage.mu.Unlock()
			continue
		}

		// создаем канал
		subChannelList[userId] = make(chan bool, 1)
		store.subStorage.cache[userId] = subChannelList[userId]
		store.subStorage.mu.Unlock()
		needFetchUserIdList = append(needFetchUserIdList, userId)
	}

	if len(needFetchUserIdList) > 0 {
		go store.addUserListToMainStore(ctx, needFetchUserIdList, companyDataConn)
	}
	return subChannelList
}

// добавляем пользователей в основное хранилище
func (store *Storage) addUserListToMainStore(ctx context.Context, needFetchUserIdList []int64, companyDataConn *company_data.DbConn) {

	// по окончанию функции закрываем все каналы
	defer store.subStorage.closeChannelList(needFetchUserIdList)

	memberList, err := companyDataConn.GetMemberList(ctx, needFetchUserIdList)

	if err != nil {

		log.Errorf("Exit with error %s", err.Error())
		return
	}

	if len(memberList) < 1 {

		log.Errorf("Users not found")
		return
	}

	for _, row := range memberList {
		store.mainStorage.doCacheMemberItem(row.UserId, row)
	}
}

// функция для закрытия множества каналов
func (subStorage *subStorage) closeChannelList(userIdList []int64) {

	for _, userId := range userIdList {
		subStorage.closeChannel(userId)
	}
}

// закрываем канал для юзера
func (subStorage *subStorage) closeChannel(userId int64) {

	subStorage.mu.Lock()
	defer subStorage.mu.Unlock()

	// достаем канал из кэша и закрываем его
	subChannel, exist := subStorage.cache[userId]
	if !exist {
		return
	}

	subChannel <- true
	close(subChannel)
	delete(subStorage.cache, userId)
}

// получаем ждем пока в кэше 2 появтися инфа
func waitUntilMemberAddedToCache(sub chan bool, userId int64) error {

	config, err := conf.GetConfig()

	if err != nil {
		return fmt.Errorf("invalid config")
	}

	select {
	case <-sub:

		return nil

		// добавляем timeout для прослушки
	case <-time.After(time.Millisecond * config.GetMemberTimeoutMs):

		err := fmt.Errorf("не смогли получить из канала: %v для userId: %d за установленный timeout", sub, userId)
		log.Errorf("%v", err)
		return err
	}
}
