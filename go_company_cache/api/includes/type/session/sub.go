package session

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/conf"
	"go_company_cache/api/includes/type/db/company_data"
	"sync"
	"time"
)

// структура хранилища с подписчиками
type subStorage struct {
	mu    sync.RWMutex
	cache map[string]chan bool
}

// функция для подписки на канал
func (store *Storage) doSubOnChan(ctx context.Context, sessionUniq string, companyDataConn *company_data.DbConn) chan bool {

	store.subStorage.mu.Lock()
	defer store.subStorage.mu.Unlock()
	subChannel, exist := store.subStorage.cache[sessionUniq]

	// если уже существует то просто отдаем канал
	if exist {
		return subChannel
	}

	subChannel = make(chan bool, 1)

	// создаем рутину на получение инфы из бд
	go store.addSessionToMainStore(ctx, sessionUniq, companyDataConn)

	store.subStorage.cache[sessionUniq] = subChannel
	return subChannel
}

// добавляем сессию в основное хранилище
func (store *Storage) addSessionToMainStore(ctx context.Context, sessionUniq string, companyDataConn *company_data.DbConn) {

	userSessionRow, err := companyDataConn.GetActiveSessionRow(ctx, sessionUniq)
	defer store.subStorage.closeChannel(sessionUniq)

	if err != nil {

		log.Errorf("Exit with error %s", err.Error())
		return
	}

	if userSessionRow == nil {

		log.Errorf("Session not found")
		return
	}

	// сохраняем sessionItem в кэш
	store.doCacheUserSessionItem(sessionUniq, userSessionRow, err)
}

// закрываем канал
func (subStorage *subStorage) closeChannel(sessionUniq string) {

	subStorage.mu.Lock()
	defer subStorage.mu.Unlock()

	// достаем канал из кэша и закрываем его
	subChannel, exist := subStorage.cache[sessionUniq]
	if !exist {
		return
	}

	subChannel <- true
	close(subChannel)
	delete(subStorage.cache, sessionUniq)
}

// получаем ждем пока в кэше 2 появтися инфа
func (store *Storage) waitUntilSessionAddedToCache(ctx context.Context, sessionUniq string, companyDataConn *company_data.DbConn) {

	sub := store.doSubOnChan(ctx, sessionUniq, companyDataConn)

	config, err := conf.GetConfig()

	if err != nil {
		return
	}

	select {
	case <-sub:

		return

		// добавляем timeout для прослушки
	case <-time.After(time.Millisecond * config.GetMemberTimeoutMs):

		log.Errorf("не смогли получить из канала: %v для sessionUniq: %s", sub, sessionUniq)
		return
	}
}
