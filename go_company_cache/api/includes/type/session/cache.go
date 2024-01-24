package session

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/includes/type/db/company_data"
	"sync"
)

// основное хранилище с сессиями
type Storage struct {
	mainStorage          *mainStorage
	subStorage           *subStorage
	sessionUniqStorage   *sessionUniqStorage
	lastSessionUsedStore *lastSessionUsedStorage
}

type mainStorage struct {
	mu    sync.RWMutex
	cache map[string]*mainSessionStruct
}

// структура объекта с сессией
type mainSessionStruct struct {
	userSessionRow *company_data.SessionRow
	userId         int64

	// системные метрики
	dateCached int64

	// проброс ошибки из БД
	err error
}

// хранилище с последними сессиями
type lastSessionUsedStorage struct {
	mu    sync.RWMutex
	cache map[string]int64
}

// костанты
const (
	cacheExpireTime           = 24 * 3600 // время протухания объекта пользователя в кэше
	sessionExpirationInterval = 12 * 3600 // если сессия не запрашивается больше этого времени, то удаляем ее из памяти
)

func MakeStore() *Storage {

	mainStore := mainStorage{
		mu:    sync.RWMutex{},
		cache: make(map[string]*mainSessionStruct, 0),
	}
	subStore := subStorage{
		mu:    sync.RWMutex{},
		cache: make(map[string]chan bool),
	}
	sessionUniq := sessionUniqStorage{
		mu:    sync.RWMutex{},
		cache: make(map[int64][]string),
	}
	lastSessionUsedStore := lastSessionUsedStorage{
		mu:    sync.RWMutex{},
		cache: make(map[string]int64),
	}
	return &Storage{
		mainStorage:          &mainStore,
		subStorage:           &subStore,
		sessionUniqStorage:   &sessionUniq,
		lastSessionUsedStore: &lastSessionUsedStore,
	}
}

// -------------------------------------------------------
// session interface methods
// -------------------------------------------------------

// получаем сессию из кеша
func (store *Storage) getSessionItemFromCache(sessionUniq string) (*mainSessionStruct, bool) {

	// обновляем последнее использование сессии
	defer store.setLastSessionUsed(sessionUniq)

	// получаем сессию из cache
	currentTimeStamp := functions.GetCurrentTimeStamp()
	store.mainStorage.mu.RLock()
	sessionItem, exist := store.mainStorage.cache[sessionUniq]
	store.mainStorage.mu.RUnlock()

	getLastUsedSession := store.getLastSessionUsed(sessionUniq)
	if !exist || getLastUsedSession < (currentTimeStamp-cacheExpireTime) {
		return &mainSessionStruct{}, false
	}

	return sessionItem, true
}

// сохраняем сессию
func (store *Storage) doCacheUserSessionItem(sessionUniq string, userSessionRow *company_data.SessionRow, err error) {

	// получаем объект из хранилища
	sessionItem := &mainSessionStruct{}

	sessionItem.dateCached = functions.GetCurrentTimeStamp()
	sessionItem.userId = userSessionRow.UserId
	sessionItem.userSessionRow = userSessionRow
	sessionItem.err = err

	store.mainStorage.mu.Lock()
	_, exist := store.mainStorage.cache[sessionUniq]
	if !exist || err == nil {
		store.mainStorage.cache[sessionUniq] = sessionItem
	}
	store.mainStorage.mu.Unlock()

	// кэшируем в хранилище для поиска пользовательских сессий
	store.sessionUniqStorage.add(sessionItem.userId, sessionUniq)

	// делаем пометку о последнем использовании
	store.setLastSessionUsed(sessionUniq)
}

// удаляем кеш по sessionUniq
func (store *Storage) deleteBySessionUniq(sessionUniq string) {

	store.mainStorage.mu.Lock()
	defer store.mainStorage.mu.Unlock()

	// удаляем sessionUniq из хранилища сессий
	delete(store.mainStorage.cache, sessionUniq)
}

// функция для удаления по времени
func (store *Storage) deleteUnusedSessions(timestamp int64) {

	store.mainStorage.mu.Lock()
	defer store.mainStorage.mu.Unlock()

	// проходимся по массиву сессий из хранилища
	for key := range store.mainStorage.cache {

		// если сессию не запрашивали больше timestamp
		if store.getLastSessionUsed(key) < timestamp {

			// удаляем сессию
			delete(store.mainStorage.cache, key)
		}
	}

}

// получаем сессию по sessionUniq
func (store *Storage) get(sessionUniq string) (*mainSessionStruct, bool) {

	store.mainStorage.mu.RLock()
	defer store.mainStorage.mu.RUnlock()

	// получаем сессию из хранилища
	sessionItemObj, exist := store.mainStorage.cache[sessionUniq]

	// если сессии не существует
	if !exist {

		log.Error(fmt.Sprintf("session.get не найдена сессия в хранилище: %v", sessionUniq))
		return nil, false
	}

	return sessionItemObj, true
}

// очистить кэш с сессиями
func (store *Storage) clear() {

	store.mainStorage.mu.Lock()
	store.mainStorage.cache = make(map[string]*mainSessionStruct)
	store.mainStorage.mu.Unlock()
}
