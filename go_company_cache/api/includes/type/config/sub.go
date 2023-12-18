package config

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/conf"
	"go_company_cache/api/includes/type/db/company_data"
	"sync"
	"time"
)

// Структура хранилища с подписчиками
type subStorage struct {
	mu    sync.RWMutex
	cache map[string]chan bool
}

// Функция для подписки на канал одного значения конфига
func (store *Storage) doSubKeyOnChan(ctx context.Context, key string, companyDataConn *company_data.DbConn) chan bool {

	// если уже существует, то добавляем в массив канал иначе создаем
	store.subStorage.mu.Lock()
	subChannel, isExist := store.subStorage.cache[key]
	if isExist {

		store.subStorage.mu.Unlock()
		return subChannel
	}

	// создаем канал
	subChannel = make(chan bool, 1)
	store.subStorage.cache[key] = subChannel
	store.subStorage.mu.Unlock()

	go store.addKeyToMainStore(ctx, key, companyDataConn)
	return subChannel
}

// Добавляем одну запись в основное хранилище
func (store *Storage) addKeyToMainStore(ctx context.Context, key string, companyDataConn *company_data.DbConn) {

	configValue, err := companyDataConn.GetKeyRow(ctx, key)

	defer store.subStorage.closeChannel(key)

	// если появилась ошибка
	if err != nil {

		log.Errorf("Error found %v", err)
		return
	}

	// если не нашли запись
	if configValue == nil {

		log.Errorf("Config value not found %d", key)
		return
	}

	store.mainStorage.doCacheKeyItem(key, configValue)
}

// Функция для подписки на канал
func (store *Storage) doSubKeyListOnChanList(ctx context.Context, keyList []string, companyDataConn *company_data.DbConn) map[string]chan bool {

	subChannelList := make(map[string]chan bool)
	var needFetchKeyList []string
	for _, key := range keyList {

		// если уже существует, то добавляем в массив канал иначе создаем
		store.subStorage.mu.Lock()
		subChannel, isExist := store.subStorage.cache[key]
		if isExist {

			subChannelList[key] = subChannel
			store.subStorage.mu.Unlock()
			continue
		}

		// создаем канал
		subChannelList[key] = make(chan bool, 1)
		store.subStorage.cache[key] = subChannelList[key]
		store.subStorage.mu.Unlock()
		needFetchKeyList = append(needFetchKeyList, key)
	}

	if len(needFetchKeyList) > 0 {
		go store.addKeyListToMainStore(ctx, needFetchKeyList, companyDataConn)
	}
	return subChannelList
}

// Добавляем значения конфига в основное хранилище
func (store *Storage) addKeyListToMainStore(ctx context.Context, needFetchKeyList []string, companyDataConn *company_data.DbConn) {

	// по окончанию функции закрываем все каналы
	defer store.subStorage.closeChannelList(needFetchKeyList)

	keyList, err := companyDataConn.GetKeyList(ctx, needFetchKeyList)

	if err != nil {

		log.Errorf("Exit with error %s", err.Error())
		return
	}

	if len(keyList) < 1 {

		log.Errorf("Config row not found")
		return
	}

	for _, row := range keyList {
		store.mainStorage.doCacheKeyItem(row.Key, row)
	}
}

// Функция для закрытия множества каналов
func (subStorage *subStorage) closeChannelList(keyList []string) {

	for _, key := range keyList {
		subStorage.closeChannel(key)
	}
}

// Закрываем канал для ключа
func (subStorage *subStorage) closeChannel(key string) {

	subStorage.mu.Lock()
	defer subStorage.mu.Unlock()

	// достаем канал из кэша и закрываем его
	subChannel, exist := subStorage.cache[key]
	if !exist {
		return
	}

	subChannel <- true
	close(subChannel)
	delete(subStorage.cache, key)
}

// Получаем ждем пока в кэше 2 появится запись
func waitUntilKeyAddedToCache(sub chan bool, key string) error {

	config, err := conf.GetConfig()

	if err != nil {
		return fmt.Errorf("invalid config")
	}

	select {
	case <-sub:

		return nil

		// добавляем timeout для прослушки
	case <-time.After(time.Millisecond * config.GetMemberTimeoutMs):

		err := fmt.Errorf("не смогли получить из канала: %v для userId: %s за установленный timeout", sub, key)
		log.Errorf("%v", err)
		return err
	}
}
