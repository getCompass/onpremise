package activitycache

import (
	"sync"
)

// основное хранилище
type mainActivityStorage struct {
	mu    sync.RWMutex
	cache map[int64]map[string]int64 // Вложенная карта для userId и sessionUniq
}

// инициализируем кэш активности
var mainActivityStore = mainActivityStorage{
	cache: make(map[int64]map[string]int64),
}

// сохраняем активность
func (s *mainActivityStorage) doCacheActivity(userId int64, activityTimestamp int64, sessionUniq string, err error) {

	s.mu.Lock()
	defer s.mu.Unlock()

	// Игнорируем обновление, если есть ошибка
	if err != nil {
		return
	}

	// инициализируем мап для сессий, если его ещё нет
	if _, exist := s.cache[userId]; !exist {
		s.cache[userId] = make(map[string]int64)
	}

	// Обновляем время активности для указанной sessionUniq
	s.cache[userId][sessionUniq] = activityTimestamp
}

// функция для очистки всего кэша
func (s *mainActivityStorage) reset() {

	s.mu.Lock()
	defer s.mu.Unlock()

	// чистим
	s.cache = make(map[int64]map[string]int64)
}
