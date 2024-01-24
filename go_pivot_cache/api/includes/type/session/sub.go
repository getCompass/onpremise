package session

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pivot_cache/api/includes/type/db/pivot_user"
	"sync"
)

// структура хранилища с подписчиками
type subStorage struct {
	mu    sync.RWMutex
	cache map[string]chan bool
}

// инициализируем кэш подписчиков
var sessionSubStore = subStorage{
	cache: make(map[string]chan bool),
}

// функция для подписки на канал
func doSubOnChan(ctx context.Context, shardId string, tableId string, sessionUniq string) chan bool {

	cacheKey := getSubCacheKey(shardId, tableId, sessionUniq)

	sessionSubStore.mu.Lock()
	defer sessionSubStore.mu.Unlock()
	subChannel, exist := sessionSubStore.cache[cacheKey]

	// если уже существует то просто отдаем канал
	if exist {
		return subChannel
	}

	subChannel = make(chan bool, 1)

	// создаем рутину на получение инфы из бд
	go addSessionToMainStore(ctx, shardId, tableId, sessionUniq)

	sessionSubStore.cache[cacheKey] = subChannel
	return subChannel
}

// добавляем сессию в основное хранилище
func addSessionToMainStore(ctx context.Context, shardId string, tableId string, sessionUniq string) {

	var userSessionRow map[string]string
	var err error

	defer closeChannel(shardId, tableId, sessionUniq)

	// получаем из базы pivot_user_{10m}.session_active_list_{1}
	userSessionRow, err = pivot_user.GetActiveSessionRow(ctx, shardId, tableId, sessionUniq)

	if userSessionRow == nil {
		log.Errorf("Не нашли в базе: %v", err)
	}

	// сохраняем sessionItem в кэш
	mainSessionStore.doCacheUserSessionItem(sessionUniq, userSessionRow, shardId, tableId, err)
}

// функция для получения ключа кэша подписчиков
func getSubCacheKey(shardID string, tableID string, sessionUniq string) string {

	return shardID + "_" + tableID + "_" + sessionUniq
}

// закрываем канал для юзера
func closeChannel(shardID string, tableID string, sessionUniq string) {

	subCacheKey := getSubCacheKey(shardID, tableID, sessionUniq)
	sessionSubStore.mu.Lock()
	defer sessionSubStore.mu.Unlock()

	// достаем канал из кэша и закрываем его
	subChannel, exist := sessionSubStore.cache[subCacheKey]
	if !exist {
		return
	}

	subChannel <- true
	close(subChannel)
	delete(sessionSubStore.cache, subCacheKey)
}
