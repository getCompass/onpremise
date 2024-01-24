package session

import (
	"sync"
)

// основное хранилище с sessionUniq по userId
type sessionUniqStorage struct {
	mu    sync.RWMutex
	cache map[int64][]string
}

var (
	// инициализируем кэш sessionUniq с ключом userId
	sessionUniqObj = sessionUniqStorage{
		cache: make(map[int64][]string),
	}
)

// -------------------------------------------------------
// uniq interface methods
// -------------------------------------------------------

// получение []sessionUniq по userID
func (s *sessionUniqStorage) get(userID int64) []string {

	// блокировка хранилища
	s.mu.RLock()

	// разблокировка хранилища
	defer s.mu.RUnlock()

	// получаем срез из хранилища
	obj, exist := s.cache[userID]

	// если среза не существует
	if !exist {

		return []string{}
	}

	return obj
}

// вставка sessionUniq по userID
func (s *sessionUniqStorage) add(userID int64, sessionUniq string) {

	// блокировка хранилища
	s.mu.Lock()

	// разблокировка хранилища
	defer s.mu.Unlock()

	// получаем срез из хранилища
	sessionUniqSlice, exist := s.cache[userID]

	// если среза не существует
	if !exist {
		sessionUniqSlice = []string{}
	}

	// аппенд в объект
	sessionUniqSlice = append(sessionUniqSlice, sessionUniq)

	// запись в хранилище
	s.cache[userID] = sessionUniqSlice
}

// удаление []sessionUniq по userID
func (s *sessionUniqStorage) delete(userID int64) {

	// блокировка хранилища
	s.mu.Lock()

	// разблокировка хранилища
	defer s.mu.Unlock()

	delete(s.cache, userID)
}

// удаление sessionUniq по userID
func (s *sessionUniqStorage) deleteSessionUniq(userID int64, sessionUniq string) {

	// блокировка хранилища
	s.mu.Lock()

	// разблокировка хранилища
	defer s.mu.Unlock()

	// формируем новый срез
	var newSlice []string

	// проходимся по старому срезу, и заполняем новый, исключая sessionUniq
	for _, val := range s.cache[userID] {

		// если значение в слайсе не равно sessionUniq
		if val != sessionUniq {
			newSlice = append(newSlice, val)
		}
	}

	// запись в хранилище
	s.cache[userID] = newSlice
}
