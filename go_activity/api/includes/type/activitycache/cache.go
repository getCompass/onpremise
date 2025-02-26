package activitycache

import (
	"sync"
)

// основное хранилище
type mainActivityStorage struct {
	mu    sync.RWMutex
	cache map[int64]map[string]int64
}

// инициализируем кэш активности
var mainActivityStore = mainActivityStorage{
	cache: make(map[int64]map[string]int64),
}

// сохраняем активность
func (s *mainActivityStorage) doCacheActivity(userId int64, activityTimestamp int64, sessionUniq string, err error) {

	s.mu.Lock()
	defer s.mu.Unlock()

	// инициализируем вложенный map, если его ещё нет
	if _, exist := s.cache[userId]; !exist || err == nil {
		s.cache[userId] = make(map[string]int64)
	}

	// обновляем время активности для указанного sessionUniq
	s.cache[userId][sessionUniq] = activityTimestamp
}

// функция для удаления активности из кеша
func (s *mainActivityStorage) delete(userID int64) {

	s.mu.Lock()
	defer s.mu.Unlock()
	_, exist := s.cache[userID]
	if !exist {
		return
	}
	delete(s.cache, userID)
}

// функция для очистки всего кэша
func (s *mainActivityStorage) reset() {

	s.mu.Lock()
	defer s.mu.Unlock()

	// очищаем всю структуру кэша
	s.cache = make(map[int64]map[string]int64)
}
