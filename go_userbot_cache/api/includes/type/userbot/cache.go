package userbot

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"sync"
)

// основное хранилище
type userbotStorage struct {
	mu    sync.RWMutex
	cache map[string]userbotStruct
}

// структура объекта
type userbotStruct struct {
	userbotId  string
	dateCached int64
	userbotRow map[string]string

	// проброс ошибки из БД
	err error
}

// инициализируем кэш
var store = userbotStorage{
	cache: make(map[string]userbotStruct),
}

// -------------------------------------------------------
// interface methods
// -------------------------------------------------------

// получаем из кеша
func (s *userbotStorage) getUserbotItemFromCache(token string) (userbotStruct, bool) {

	// получаем из cache
	s.mu.RLock()
	userbotItem, isExist := s.cache[token]
	s.mu.RUnlock()

	return userbotItem, isExist
}

// сохраняем в кэш
func (s *userbotStorage) doCacheUserbotItem(token string, userbotRow map[string]string, err error) {

	// получаем объект из хранилища
	userbotItem := userbotStruct{}

	userbotItem.dateCached = functions.GetCurrentTimeStamp()
	userbotItem.userbotId = userbotRow["userbot_id"]
	userbotItem.userbotRow = userbotRow
	userbotItem.err = err

	s.mu.Lock()
	_, exist := s.cache[token]
	if !exist || err == nil {
		s.cache[token] = userbotItem
	}
	s.mu.Unlock()
}

// удаляем кеш по token
func (s *userbotStorage) deleteFromStore(token string) {

	s.mu.Lock()
	defer s.mu.Unlock()

	// удаляем из хранилища
	delete(s.cache, token)
}
