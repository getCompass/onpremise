package balancer

import (
	"fmt"
	"go_sender_balancer/api/includes/type/structures"
	"sync"
)

type _senderCacheStruct struct {
	nodeList map[int64]_senderNodeStruct
	mu       sync.RWMutex
}

type _senderNodeStruct struct {
	nodeId             int64
	host               string
	port               string
	userConnectionList map[int64]structures.UserConnectionStruct
}

var SenderCache _senderCacheStruct

// инициализируем пакет
func init() {

	SenderCache = _senderCacheStruct{}
	SenderCache.nodeList = make(map[int64]_senderNodeStruct)
}

// добавляем ноду в кэш
func (SenderCache *_senderCacheStruct) SetNode(nodeId int64, host string, port string) {

	// блокируем хранилище
	SenderCache.mu.Lock()
	defer SenderCache.mu.Unlock()

	SenderCache.nodeList[nodeId] = _senderNodeStruct{
		nodeId:             nodeId,
		host:               host,
		port:               port,
		userConnectionList: make(map[int64]structures.UserConnectionStruct),
	}
}

// добавляем ноду в кэш
func (SenderCache *_senderCacheStruct) Debug() {

	// блокируем хранилище
	SenderCache.mu.RLock()
	defer SenderCache.mu.RUnlock()

	fmt.Println("Nodes:")
	for _, item := range SenderCache.nodeList {

		fmt.Printf("\nid: %d\thost: %s\tport:%s", item.nodeId, item.host, item.port)
	}
}

// удаляем ноду из кеша
func (SenderCache *_senderCacheStruct) DeleteNode(nodeId int64) {

	// блокируем хранилище
	SenderCache.mu.Lock()
	defer SenderCache.mu.Unlock()

	delete(SenderCache.nodeList, nodeId)
}

// чистим ноду в кеше
func (SenderCache *_senderCacheStruct) ClearNode(nodeId int64) {

	// блокируем хранилище
	SenderCache.mu.Lock()
	defer SenderCache.mu.Unlock()

	// если такой ноды нет
	if !IsSenderNodeExistInCache(nodeId) {
		return
	}

	// перезаписываем данные о ноде с пустым пуллом соединений
	senderNode := SenderCache.nodeList[nodeId]
	SenderCache.nodeList[nodeId] = _senderNodeStruct{
		nodeId:             senderNode.nodeId,
		host:               senderNode.host,
		port:               senderNode.port,
		userConnectionList: make(map[int64]structures.UserConnectionStruct),
	}
}

// добавляем подключение пользователя к ноде
func (SenderCache *_senderCacheStruct) AddUserConnection(nodeId int64, userConnection structures.UserConnectionStruct) {

	// блокируем хранилище
	SenderCache.mu.Lock()
	defer SenderCache.mu.Unlock()

	// если такой ноды нет
	if !IsSenderNodeExistInCache(nodeId) {
		return
	}

	// докидываем соединение в конец map
	SenderCache.nodeList[nodeId].userConnectionList[userConnection.UserId] = userConnection
}

// убираем подключение пользователя к ноде
func (SenderCache *_senderCacheStruct) DeleteUserConnection(nodeId int64, userId int64) {

	// блокируем хранилище
	SenderCache.mu.Lock()
	defer SenderCache.mu.Unlock()

	// если такой ноды нет
	if !IsSenderNodeExistInCache(nodeId) {
		return
	}

	// проходимся по всем соединениям
	for k, v := range SenderCache.nodeList[nodeId].userConnectionList {

		if v.UserId == userId {
			delete(SenderCache.nodeList[nodeId].userConnectionList, k)
		}
	}
}

// получаем подключение по userId
func (SenderCache *_senderCacheStruct) GetUserConnectionListByUserId(userId int64) ([]structures.UserConnectionStruct, bool) {

	// блокируем хранилище
	SenderCache.mu.RLock()
	defer SenderCache.mu.RUnlock()

	// проходимся по всем соединениям
	userConnectionList := _getUserConnectionList(userId, SenderCache.nodeList)

	// если список соединений пуст
	if len(userConnectionList) < 1 {
		return []structures.UserConnectionStruct{}, false
	}

	return userConnectionList, true
}

// формируем список соединений пользователя
func _getUserConnectionList(userId int64, nodeList map[int64]_senderNodeStruct) []structures.UserConnectionStruct {

	// инициализируем массив под ответ
	var userConnectionList []structures.UserConnectionStruct

	// проходимся по всем соединениям
	for _, nodeItem := range nodeList {

		for _, connectionItem := range nodeItem.userConnectionList {

			if connectionItem.UserId == userId {
				userConnectionList = append(userConnectionList, connectionItem)
			}
		}
	}

	return userConnectionList
}
