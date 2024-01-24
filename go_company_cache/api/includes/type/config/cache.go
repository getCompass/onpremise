package config

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/includes/type/db/company_data"
	"sync"
)

// Storage основное хранилище
type Storage struct {
	mainStorage *mainStorage
	subStorage  *subStorage
}

type mainStorage struct {
	mu    sync.RWMutex
	cache map[string]*keyStruct
}

// Структура объекта
type keyStruct struct {
	key        string
	dateCached int64
	keyRow     *company_data.KeyRow

	// Проброс ошибки из БД
	err error
}

func MakeStore() *Storage {

	mainStore := mainStorage{
		mu:    sync.RWMutex{},
		cache: make(map[string]*keyStruct, 0),
	}
	subStore := subStorage{
		mu:    sync.RWMutex{},
		cache: make(map[string]chan bool),
	}
	return &Storage{
		mainStorage: &mainStore,
		subStorage:  &subStore,
	}
}

// -------------------------------------------------------
// config interface methods
// -------------------------------------------------------

// Получаем из кеша
func (store *mainStorage) getKeyItemFromCache(key string) (*keyStruct, bool) {

	// получаем из cache
	store.mu.RLock()
	keyItem, isExist := store.cache[key]
	store.mu.RUnlock()

	return keyItem, isExist
}

// Сохраняем в кэш
func (store *mainStorage) doCacheKeyItem(key string, keyRow *company_data.KeyRow) {

	// получаем объект из хранилища
	keyItem := &keyStruct{}

	keyItem.dateCached = functions.GetCurrentTimeStamp()
	keyItem.key = keyRow.Key
	keyItem.keyRow = keyRow

	store.mu.Lock()
	_, exist := store.cache[key]
	if !exist {
		store.cache[key] = keyItem
	}
	store.mu.Unlock()
}

// Удаляем кеш по key
func (store *mainStorage) deleteFromStore(key string) {

	store.mu.Lock()
	defer store.mu.Unlock()

	// удаляем из хранилища
	delete(store.cache, key)
}

// Получаем запись конфига по ключу по key
func (store *mainStorage) get(key string) (*keyStruct, bool) {

	store.mu.RLock()
	defer store.mu.RUnlock()

	// получаем из хранилища
	item, exist := store.cache[key]

	// если не существует
	if !exist {

		log.Error(fmt.Sprintf("не найден key в хранилище: %s", key))
		return nil, false
	}

	return item, true
}

// Очистить кэш с ключами
func (store *mainStorage) clear() {

	store.mu.Lock()
	store.cache = make(map[string]*keyStruct, 0)
	store.mu.Unlock()
}
