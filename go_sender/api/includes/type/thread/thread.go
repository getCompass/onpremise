package thread

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"sync"
)

// ---------------------------------------------
// STRUCTURES FOR STORAGE
// ---------------------------------------------

type UserConnectionStore struct {
	store map[string]map[int64][]int64
	mx    sync.RWMutex
}

func MakeThreadUserConnectionStore() *UserConnectionStore {

	return &UserConnectionStore{
		store: make(map[string]map[int64][]int64),
		mx:    sync.RWMutex{},
	}
}

// функция проверяет, является ли пользователь участником треда
func (tucStore *UserConnectionStore) IsUserThreadListener(threadKey string, userId int64) bool {

	tucStore.mx.RLock()
	defer tucStore.mx.RUnlock()

	// получаем пользователей по ключу threadKey
	temp, isExist := tucStore.store[threadKey]
	if !isExist {

		log.Debug("Треда нет в хранилище")
		return false
	}

	// проверяем привязан ли пользователь к треду
	_, isExist = temp[userId]
	if !isExist {

		log.Debug("Пользователя нет в хранилище")
		return false
	}
	return true
}

// добавляет пользователя в список читателей треда
func (tucStore *UserConnectionStore) AddUserToThread(taStore *AuthStore, threadKey string, userId int64) {

	tucStore.mx.Lock()
	defer tucStore.mx.Unlock()

	// получаем пользователей по ключу threadKey
	users, isExist := tucStore.store[threadKey]
	if !isExist {
		users = make(map[int64][]int64)
	}

	// получаем соединения, по userId, если нет, создаем пустую запись
	connections, exist := users[userId]
	if !exist {
		connections = make([]int64, 0)
	}

	// добавляем к пользователю соединения в массив пользователей
	users[userId] = connections

	// добавляем пользователей в массив тредов, записываем в хранилище
	tucStore.store[threadKey] = users

	// отдаем задачу воркерам, чтобы проверить, что подтверждение открытия треда было
	taStore.addTaskThreadAuthStore(userId, threadKey)
}

type KeyStore struct {
	store map[int64]string
	mx    sync.RWMutex
}

func MakeThreadKeyStore() *KeyStore {

	return &KeyStore{
		store: make(map[int64]string),
		mx:    sync.RWMutex{},
	}
}

// удаляет соединение из хранилищ ThreadStorage, ConnectionList
func (kStore *KeyStore) DeleteConnectionFromThread(tucStore *UserConnectionStore, userId int64, connectionId int64) {

	kStore.mx.Lock()
	defer kStore.mx.Unlock()

	// получаем threadKey для удаления из хранилища ThreadStorage
	threadKey, isExist := kStore.store[connectionId]
	if !isExist {
		return
	}

	// удаляем соединение из хранилища ConnectionList
	delete(kStore.store, connectionId)

	// удаляем соединение из хранилища ThreadStorage
	tucStore.deleteListenerConnection(threadKey, userId, connectionId)
}

// функция удаляющая соединение из хранилища ThreadStorage
func (tucStore *UserConnectionStore) deleteListenerConnection(threadKey string, userId int64, connectionID int64) {

	tucStore.mx.Lock()
	defer tucStore.mx.Unlock()

	// получаем пользователей по ключу threadKey
	users, isExist := tucStore.store[threadKey]
	if !isExist {
		return
	}

	// получаем соединения по ключу userId
	connections, isExist := users[userId]
	if !isExist {
		return
	}

	// удаляем соединение из массива соединений
	for key, val := range connections {

		if val == connectionID {
			connections = append(connections[:key], connections[key+1:]...)
		}
	}
	users[userId] = connections
	tucStore.store[threadKey] = users
	tucStore.cleanThreadStorage(threadKey, userId)
}

// добавляет соединение в хранилища ThreadStorage, ConnectionList
func AddConnectionToThread(threadStore *KeyStore, tucStore *UserConnectionStore, taStore *AuthStore, threadKey string, userId int64, ConnectionID int64) bool {

	// проверяем что пользователь имеет доступ к треду
	if !tucStore.IsUserThreadListener(threadKey, userId) {

		return false
	}

	// добавляем соединение в хранилище ThreadStorage
	tucStore.addListenerConnection(threadKey, userId, ConnectionID)

	// собавляем соединение в хранилище ConnectionList
	threadStore.addConnection(threadKey, ConnectionID)

	// удаляем задачу из воркера, чтобы он не сработал
	taStore.removeTaskThreadAuthStore(userId, threadKey)

	return true
}

// функция добавляющая соединение пользователя в хранилище ThreadStorage
func (tucStore *UserConnectionStore) addListenerConnection(threadKey string, userId int64, connectionId int64) {

	tucStore.mx.Lock()
	defer tucStore.mx.Unlock()

	users, isExist := tucStore.store[threadKey]
	if !isExist {
		users = make(map[int64][]int64)
	}

	connections, isExist := users[userId]
	if !isExist {
		connections = make([]int64, 0, 1)
	}

	for _, val := range connections {

		if val == connectionId {
			return
		}
	}
	connections = append(connections, connectionId)
	users[userId] = connections
	tucStore.store[threadKey] = users
}

// функция добавляет соединение в хранилище ConnectionList
func (kStore *KeyStore) addConnection(threadKey string, connectionID int64) {

	kStore.mx.Lock()
	kStore.store[connectionID] = threadKey
	kStore.mx.Unlock()
}

// получаем слушателей треда
func (tucStore *UserConnectionStore) GetThreadListeners(threadKey string) map[int64][]int64 {

	tucStore.mx.Lock()
	defer tucStore.mx.Unlock()

	// проверяем существует ли тред
	connectionListGroupByUserId, isExist := tucStore.store[threadKey]
	if !isExist {
		return map[int64][]int64{}
	}

	return connectionListGroupByUserId
}

// блокируем Lock
func (tucStore *UserConnectionStore) Lock() {

	tucStore.mx.Lock()
}

// разблокируем UnLock
func (tucStore *UserConnectionStore) UnLock() {

	tucStore.mx.Unlock()
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// функция очищает пустые массивы хранилища
// !!! mutex не ставим так как он поставлен функцией выше
func (tucStore *UserConnectionStore) cleanThreadStorage(threadKey string, userId int64) {

	// получаем пользователей по ключу threadKey
	users, isExist := tucStore.store[threadKey]
	if !isExist {
		return
	}

	connections, isExist := users[userId]
	if !isExist {
		return
	}

	// если массив соединений пустой, то удаляем пользователя из массива пользователей
	if len(connections) < 1 {
		delete(users, userId)
	}

	if len(users) < 1 {

		delete(tucStore.store, threadKey)
		return
	}
	tucStore.store[threadKey] = users
}
