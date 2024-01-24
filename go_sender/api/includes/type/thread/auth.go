package thread

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"sync"
)

// структура задачи для крона
type threadAuthStruct struct {
	needWork  int64
	userID    int64
	threadKey string
}

type AuthStore struct {
	store map[string]*threadAuthStruct
	mx    sync.RWMutex
}

func MakeThreadAuthStore() *AuthStore {

	return &AuthStore{
		store: make(map[string]*threadAuthStruct),
		mx:    sync.RWMutex{},
	}
}

const timeToClean = 3

// функция для очистки старрых тредов
func (taStore *AuthStore) ClearOldThreadAuth(tucStore *UserConnectionStore) {

	currentTime := functions.GetCurrentTimeStamp()
	for k, v := range taStore.store {

		if v.needWork < currentTime {

			tucStore.cleanThreadStorage(v.threadKey, v.userID)
			delete(taStore.store, k)
		}
	}
}

// добавляет задачу для воркеров
func (taStore *AuthStore) addTaskThreadAuthStore(userID int64, threadKey string) {

	// собираем задачу для воркеров
	taskCheckUserConnection := &threadAuthStruct{
		needWork:  functions.GetCurrentTimeStamp() + timeToClean,
		userID:    userID,
		threadKey: threadKey,
	}
	uuid := functions.GenerateUuid()

	taStore.mx.Lock()
	taStore.store[uuid] = taskCheckUserConnection
	taStore.mx.Unlock()
}

// убираем задачу для воркеров
func (taStore *AuthStore) removeTaskThreadAuthStore(userID int64, threadKey string) {

	taStore.mx.Lock()
	defer taStore.mx.Unlock()
	for k, v := range taStore.store {

		// ищем нужный воркер и удаляем
		if v.userID == userID && v.threadKey == threadKey {
			delete(taStore.store, k)
		}
	}
}
