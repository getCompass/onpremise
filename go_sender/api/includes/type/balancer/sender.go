package balancer

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/structures"
	"sync"
)

type senderCacheStruct struct {
	node senderNodeStruct
	mu   sync.RWMutex
}

type senderNodeStruct struct {
	nodeId             int64
	host               string
	port               string
	userConnectionList map[int64]bool
}

var SenderCache senderCacheStruct

// инициализируем пакет
func init() {

	SenderCache = senderCacheStruct{}
	SenderCache.node = senderNodeStruct{}
}

// SetNode добавляем ноду в кэш
func (SenderCache *senderCacheStruct) SetNode(nodeId int64, host string, port string) {

	// блокируем хранилище
	SenderCache.mu.Lock()
	defer SenderCache.mu.Unlock()

	SenderCache.node = senderNodeStruct{
		nodeId:             nodeId,
		host:               host,
		port:               port,
		userConnectionList: make(map[int64]bool),
	}
}

// Debug добавляем ноду в кэш
func (SenderCache *senderCacheStruct) Debug() {

	// блокируем хранилище
	SenderCache.mu.RLock()
	defer SenderCache.mu.RUnlock()

	log.Infof("Node:")
	item := SenderCache.node

	log.Infof("\nid: %d\thost: %s\tport:%s", item.nodeId, item.host, item.port)
}

// DeleteNode удаляем ноду из кеша
func (SenderCache *senderCacheStruct) DeleteNode() {

	// блокируем хранилище
	SenderCache.mu.Lock()
	defer SenderCache.mu.Unlock()

	SenderCache.node = senderNodeStruct{}
}

// AddUserConnection добавляем подключение пользователя к ноде
func (SenderCache *senderCacheStruct) AddUserConnection(userConnection structures.UserConnectionStruct) {

	// блокируем хранилище
	SenderCache.mu.Lock()
	defer SenderCache.mu.Unlock()

	SenderCache.node = senderNodeStruct{}

	SenderCache.node.userConnectionList = make(map[int64]bool, 0)

	// докидываем соединение в конец map
	SenderCache.node.userConnectionList[userConnection.UserId] = true
}

// DeleteUserConnection убираем подключение пользователя к ноде
func (SenderCache *senderCacheStruct) DeleteUserConnection(userId int64) {

	// блокируем хранилище
	SenderCache.mu.Lock()
	defer SenderCache.mu.Unlock()

	_, ok := SenderCache.node.userConnectionList[userId]

	if ok {
		delete(SenderCache.node.userConnectionList, userId)
	}
}

// GetUserConnectionListByUserId получаем подключение по userId
func (SenderCache *senderCacheStruct) GetUserConnectionListByUserId(userId int64) bool {

	// блокируем хранилище
	SenderCache.mu.RLock()
	defer SenderCache.mu.RUnlock()

	_, ok := SenderCache.node.userConnectionList[userId]

	return ok
}
