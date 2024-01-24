package usercache

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"sync"
)

// основное хранилище с пользователями
type mainUserStorage struct {
	mu    sync.RWMutex
	cache map[int64]userInfoStruct
}

// структура объекта с пользовательской информацией
type userInfoStruct struct {

	// системные метрики
	dateCached int64

	// информация о пользователе из бд
	userRow map[string]string

	err error
}

// инициализируем кэш пользователей
var mainUserStore = mainUserStorage{
	cache: make(map[int64]userInfoStruct),
}

const (
	cacheExpireTime = 24 * 3600 // время протухания объекта пользователя в кэше
)

// получаем пользователя из кеша
func (s *mainUserStorage) getUserInfoFromCache(userId int64) (userInfoStruct, bool) {

	s.mu.RLock()
	defer s.mu.RUnlock()
	defer setLastUserUsed(userId)

	currentTimeStamp := functions.GetCurrentTimeStamp()
	userInfo, exist := s.cache[userId]

	getLastUsedUser := getLastUserUsed(userId)
	if !exist || getLastUsedUser < (currentTimeStamp-cacheExpireTime) {
		return userInfoStruct{}, false
	}

	return userInfo, true
}

// сохраняем пользователя
func (s *mainUserStorage) doCacheUserInfoItem(userId int64, userRow map[string]string, err error) {

	s.mu.Lock()
	defer s.mu.Unlock()

	userInfo, exist := s.cache[userId]
	if !exist || err == nil {
		userInfo = userInfoStruct{}
	}

	userInfo.dateCached = functions.GetCurrentTimeStamp()
	userInfo.userRow = userRow
	userInfo.err = err
	s.cache[userId] = userInfo
}

// функция для удаления пользовательского объекта из кэша
func (s *mainUserStorage) delete(userID int64) {

	s.mu.Lock()
	defer s.mu.Unlock()
	_, exist := s.cache[userID]
	if !exist {
		return
	}
	delete(s.cache, userID)
}

// функция для очистки всего кэша
func (s *mainUserStorage) reset() {

	s.mu.Lock()
	defer s.mu.Unlock()

	// просто заменяем старую на новую
	s.cache = make(map[int64]userInfoStruct)

	// так же поступаем к кэшем недавно использованных
	resetLastUsed()
}
