package usercache

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"sync"
)

// основное хранилище с пользователями
type mainUserStorage struct {
	mu    sync.RWMutex
	cache map[int64]userActivityStruct
}

// структура объекта пользователя
type userActivityStruct struct {

	// системные метрики
	dateCached int64

	// информация о пользователе из бд
	userRow map[string]string

	err error
}

// инициализируем кэш пользователей
var mainUserStore = mainUserStorage{
	cache: make(map[int64]userActivityStruct),
}

const (
	cacheExpireTime = 8 * 3600 // время протухания объекта пользователя в кэше
)

// получаем пользователя из кеша
func (s *mainUserStorage) getUserFromCache(userId int64) (userActivityStruct, bool) {

	s.mu.RLock()
	defer s.mu.RUnlock()
	defer setLastUserUsed(userId)

	currentTimeStamp := functions.GetCurrentTimeStamp()
	userActivity, exist := s.cache[userId]

	getLastUsedUser := getLastUserUsed(userId)
	if !exist || getLastUsedUser < (currentTimeStamp-cacheExpireTime) {
		return userActivityStruct{}, false
	}

	return userActivity, true
}

// сохраняем пользователя
func (s *mainUserStorage) doCacheUserItem(userId int64, userRow map[string]string, err error) {

	s.mu.Lock()
	defer s.mu.Unlock()

	userCache, exist := s.cache[userId]
	if !exist || err == nil {
		userCache = userActivityStruct{}
	}

	userCache.dateCached = functions.GetCurrentTimeStamp()
	userCache.userRow = userRow
	userCache.err = err
	s.cache[userId] = userCache
}

// функция для очистки всего кэша
func (s *mainUserStorage) reset() {

	s.mu.Lock()
	defer s.mu.Unlock()

	// просто заменяем старую на новую
	s.cache = make(map[int64]userActivityStruct)

	// так же поступаем к кэшем недавно использованных
	resetLastUsed()
}
