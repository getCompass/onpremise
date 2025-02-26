package session

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"sync"
)

// основное хранилище с сессиями
type mainSessionStorage struct {
	mu    sync.RWMutex
	cache map[string]mainSessionStruct
}

// структура объекта с сессией
type mainSessionStruct struct {
	userSessionRow map[string]string
	userId         int64
	shardID        string
	tableID        string

	// системные метрики
	dateCached int64

	// проброс ошибки из БД
	err error
}

// костанты
const (
	cacheExpireTime           = 24 * 3600 // время протухания объекта пользователя в кэше
	sessionExpirationInterval = 12 * 3600 // если сессия не запрашивается больше этого времени, то удаляем ее из памяти
)

// инициализируем кэш сессий
var mainSessionStore = mainSessionStorage{
	cache: make(map[string]mainSessionStruct),
}

// -------------------------------------------------------
// session interface methods
// -------------------------------------------------------

// получаем сессию из кеша
func (s *mainSessionStorage) getSessionItemFromCache(sessionUniq string) (mainSessionStruct, bool) {

	// обновляем последнее использование сессии
	defer setLastSessionUsed(sessionUniq)

	// получаем сессию из cache
	currentTimeStamp := functions.GetCurrentTimeStamp()
	s.mu.RLock()
	sessionItem, exist := s.cache[sessionUniq]
	s.mu.RUnlock()

	getLastUsedSession := getLastSessionUsed(sessionUniq)
	if !exist || getLastUsedSession < (currentTimeStamp-cacheExpireTime) {
		return mainSessionStruct{}, false
	}

	return sessionItem, true
}

// сохраняем сессию
func (s *mainSessionStorage) doCacheUserSessionItem(sessionUniq string, userSessionRow map[string]string, shardId string, tableId string, err error) {

	// получаем объект из хранилища
	sessionItem := mainSessionStruct{}

	sessionItem.dateCached = functions.GetCurrentTimeStamp()
	sessionItem.userId = functions.StringToInt64(userSessionRow["user_id"])
	sessionItem.shardID = shardId
	sessionItem.tableID = tableId
	sessionItem.userSessionRow = userSessionRow
	sessionItem.err = err

	s.mu.Lock()
	s.cache[sessionUniq] = sessionItem
	s.mu.Unlock()

	// кэшируем в хранилище для поиска пользовательских сессий
	sessionUniqObj.add(sessionItem.userId, sessionUniq)

	// делаем пометку о последнем использовании
	setLastSessionUsed(sessionUniq)
}

// удаляем кеш по sessionUniq
func (s *mainSessionStorage) deleteBySessionUniq(sessionUniq string) {

	s.mu.Lock()
	defer s.mu.Unlock()

	// удаляем sessionUniq из хранилища сессий
	delete(s.cache, sessionUniq)
}

// функция для удаления по времени
func (s *mainSessionStorage) deleteUnusedSessions(timestamp int64) {

	s.mu.Lock()
	defer s.mu.Unlock()

	// проходимся по массиву сессий из хранилища
	for key := range s.cache {

		// если сессию не запрашивали больше timestamp
		if getLastSessionUsed(key) < timestamp {

			// удаляем сессию
			delete(s.cache, key)
		}
	}

}

// получаем сессию по sessionUniq
func (s *mainSessionStorage) get(sessionUniq string) (mainSessionStruct, bool) {

	s.mu.RLock()
	defer s.mu.RUnlock()

	// получаем сессию из хранилища
	sessionItemObj, exist := s.cache[sessionUniq]

	// если сессии не существует
	if !exist {

		log.Error(fmt.Sprintf("session.get не найдена сессия в хранилище: %v", sessionUniq))
		return mainSessionStruct{}, false
	}

	return sessionItemObj, true
}

// функция для очистки всего кэша
func (s *mainSessionStorage) reset() {

	s.mu.Lock()
	defer s.mu.Unlock()

	// просто заменяем старую на новую
	s.cache = make(map[string]mainSessionStruct)

	// так же поступаем к кэшем недавно использованных
	resetLastUsed()
}

// функция для обновления last_online_at сессии
func (s *mainSessionStorage) updateLastOnlineAt() {

	s.mu.Lock()
	defer s.mu.Unlock()

	updateLastOnlineAt()
}
