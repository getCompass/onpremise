package usercache

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pivot_cache/api/includes/type/db/pivot_user"
	"sync"
)

// структура хранилища с подписчиками
type subStorage struct {
	mu    sync.RWMutex
	cache map[int64]chan bool
}

// инициализируем кэш подписчиков
var userSubStore = subStorage{
	cache: make(map[int64]chan bool),
}

// функция для подписки на канал
func doSubOnChan(ctx context.Context, userId int64) chan bool {

	userSubStore.mu.Lock()
	defer userSubStore.mu.Unlock()
	subChannel, exist := userSubStore.cache[userId]

	// если уже существует то просто отдаем канал
	if exist {
		return subChannel
	}

	subChannel = make(chan bool, 1)

	// создаем рутину на получение инфы из бд
	go addUserToMainStore(ctx, userId)

	userSubStore.cache[userId] = subChannel
	return subChannel
}

// добавляем пользователя в основное хранилище
func addUserToMainStore(ctx context.Context, userId int64) {

	var userInfoRow map[string]string
	var err error

	userInfoRow, err = pivot_user.GetUserRowFromDb(ctx, userId)

	defer closeChannel(userId)

	if userInfoRow == nil {

		log.Errorf("Не нашли в базе: %v", err)
		return
	}

	// сохраняем user_row в кэш
	mainUserStore.doCacheUserInfoItem(userId, userInfoRow, err)
}

// функция для подписки на канал получения списка пользователей
func doSubOnChanList(ctx context.Context, userIdList []int64) chan bool {

	userSubStore.mu.Lock()
	defer userSubStore.mu.Unlock()
	subChannel, exist := userSubStore.cache[userIdList[0]]

	// если уже существует то просто отдаем канал
	if exist {
		return subChannel
	}

	subChannel = make(chan bool, 1)

	// создаем рутину на получение инфы из бд
	go addUserListToMainStore(ctx, userIdList)

	userSubStore.cache[userIdList[0]] = subChannel
	return subChannel
}

// добавляем список пользователей в основное хранилище
func addUserListToMainStore(ctx context.Context, userIdList []int64) {

	userInfoRows, err := pivot_user.GetUserListFromDb(ctx, userIdList)

	// сохраняем массив user_row в кэш
	for _, userRow := range userInfoRows {
		userId := functions.StringToInt64(userRow["user_id"])
		mainUserStore.doCacheUserInfoItem(userId, userRow, err)
	}

	userSubStore.mu.Lock()
	defer userSubStore.mu.Unlock()

	// достаем канал из кэша и закрываем его
	subChannel, exist := userSubStore.cache[userIdList[0]]
	if !exist {
		return
	}
	close(subChannel)
	delete(userSubStore.cache, userIdList[0])
}

// закрываем канал для юзера
func closeChannel(userId int64) {

	userSubStore.mu.Lock()
	defer userSubStore.mu.Unlock()

	// достаем канал из кэша и закрываем его
	subChannel, exist := userSubStore.cache[userId]
	if !exist {
		return
	}

	subChannel <- true
	close(subChannel)
	delete(userSubStore.cache, userId)
}
