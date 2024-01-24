package balancer

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender_balancer/api/conf"
	"go_sender_balancer/api/includes/type/structures"
	"sync"
)

// -------------------------------------------------------
// пакет, отвечающий за балансировку пользовательских
// подключений между имеющимися go_sender нодами
// -------------------------------------------------------

const (
	balanceMethodLeastConnection = "least_connection" // пользователя отправляют на менее нагруженную ноду go_sender
)

var (
	// метод балансировки
	balanceMethod = balanceMethodLeastConnection

	// хранилище для подсчета соединений go_sender
	counterStore = counterStorage{
		cache: make(map[int64]*nodeCountInfo),
	}
)

// структура хранилища для подсчета соединений go_sender
type counterStorage struct {
	mu    sync.RWMutex
	cache map[int64]*nodeCountInfo
}

// информация о кол-ве соединений в ноде
type nodeCountInfo struct {
	count int64
	limit int64
}

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// получаем список нод на которых есть пользователь
func GetSenderNodeIdListForUser(userId int64) []int64 {

	var senderNodeIdList []int64
	var userConnectionList []structures.UserConnectionStruct
	userConnectionList, _ = SenderCache.GetUserConnectionListByUserId(userId)

	// пробегаемся по массиву пользовательских соединений
	for _, item := range userConnectionList {

		senderNodeIdList = append(senderNodeIdList, item.SenderNodeId)
	}

	if len(senderNodeIdList) == 0 {
		return []int64{}
	}

	return senderNodeIdList
}

// получаем идентификатор ноды, к которой направляем пользовательское соединение
func GetSenderNodeId(skippedNodeIdList []int64) (senderNodeId int64) {

	// в зависимости от текущего метода балансировки
	switch balanceMethod {
	case balanceMethodLeastConnection:

		// выбираем менее нагруженный go_sender
		senderNodeId = counterStore.getLeastLoadedNodeId(skippedNodeIdList)
	}

	return senderNodeId
}

// проверяем существует ли нода в кэше
func IsSenderNodeExistInCache(nodeId int64) bool {

	// проходимся по всем нодам
	for _, item := range SenderCache.nodeList {

		// если нашли нужную
		if item.nodeId == nodeId {
			return true
		}
	}

	return false
}

// добавляем пользовательское подключение
func AddUserConnection(userId int64, senderNodeId int64) {

	// в зависимости от текущего метода балансировки
	switch balanceMethod {
	case balanceMethodLeastConnection:

		// увеличиваем количество пользовательских подключений для ноды
		counterStore.inc(senderNodeId)

		// добавляем ноду в кэш, если ее там нет
		_addSenderNodeInCacheIfNotExist(senderNodeId)

		userConnection := structures.UserConnectionStruct{
			UserId:       userId,
			SenderNodeId: senderNodeId,
		}

		// добавляем ноду к пользовательским подключениям
		SenderCache.AddUserConnection(senderNodeId, userConnection)
	}
}

// записываем новую ноду в кэш, если ее нет
func _addSenderNodeInCacheIfNotExist(nodeId int64) {

	// если нода уже есть в кэше
	if IsSenderNodeExistInCache(nodeId) {
		return
	}

	// получаем ноды из конфига
	actualSenderItem, isExist := GetSenderItem(nodeId)

	// если не нашли ноду в конфиге
	if !isExist {
		return
	}

	SenderCache.SetNode(nodeId, actualSenderItem.Host, actualSenderItem.Port)
}

// удаляем пользовательское подключение
func RemoveUserConnection(userId int64, senderNodeId int64) {

	// в зависимости от текущего метода балансировки
	switch balanceMethod {
	case balanceMethodLeastConnection:

		// уменьшаем количество пользовательских подключений для ноды
		counterStore.dec(senderNodeId)

		// удаляем пользовательское соединение
		SenderCache.DeleteUserConnection(senderNodeId, userId)
	}
}

// декрементим соединения ноды
func DecrementUserConnections(senderNodeId int64) {

	// в зависимости от текущего метода балансировки
	switch balanceMethod {
	case balanceMethodLeastConnection:

		// уменьшаем количество пользовательских подключений для ноды
		counterStore.dec(senderNodeId)
	}
}

// сбрасываем счетчик ноды
func ClearNode(nodeId int64) {

	// в зависимости от текущего метода балансировки
	switch balanceMethod {
	case balanceMethodLeastConnection:

		// сбрасываем
		counterStore.clearNodeCounter(nodeId)

		SenderCache.ClearNode(nodeId)
	}
}

// -------------------------------------------------------
// METHODS FOR counterStore
// -------------------------------------------------------

// получаем минимально нагруженную ноду
func (counterStore *counterStorage) getLeastLoadedNodeId(skippedNodeIdList []int64) int64 {

	// блокируем хранилище
	counterStore.mu.RLock()
	defer counterStore.mu.RUnlock()

	// объявляем значения для отбора
	var minCount int64 = -1
	var leastLoadedNodeId int64 = -1

	// проходимся по всем нодам
	for nodeId, countInfo := range counterStore.cache {

		// если нода не определена или счетчик минимально нагруженной больше счетчика итерации
		// а также лимит не превышен или не установлен и она не находидтся в списке пропуска
		if (minCount < 0 || minCount > countInfo.count) &&
			(countInfo.limit == 0 || countInfo.count < countInfo.limit) &&
			!isInSkippedList(skippedNodeIdList, nodeId) {

			// устанавливаем минимальный счетчик и ноду
			minCount = countInfo.count
			leastLoadedNodeId = nodeId
		}
	}

	return leastLoadedNodeId
}

// проверяем, есть ли nodeId в списке пропускаемых нод
func isInSkippedList(skippedList []int64, nodeId int64) bool {

	for _, skippedNodeId := range skippedList {
		if nodeId == skippedNodeId {
			return true
		}
	}
	return false
}

// увеличиваем значение счетчика
func (counterStore *counterStorage) inc(nodeId int64) {

	// блокируем хранилище
	counterStore.mu.Lock()

	// получаем и увеличиваем значение счетчика
	nodeCount, ok := counterStore.cache[nodeId]

	// если значения еще нет, то создаем и указываем лимит
	if !ok {
		nodeConf, isExist := GetConfigNode(nodeId)
		if !isExist {
			log.Errorf("не найдена конфигурация для go_sender node #%d", nodeId)
			return
		}
		counterStore.cache[nodeId] = &nodeCountInfo{
			count: 1,
			limit: nodeConf.Limit,
		}
	} else {
		nodeCount.count++
	}

	// разблокируем хранилище
	counterStore.mu.Unlock()
}

// уменьшаем значение счетчика
func (counterStore *counterStorage) dec(nodeId int64) {

	// блокируем хранилище
	counterStore.mu.Lock()

	// разблокируем хранилище при выходе из функции
	defer counterStore.mu.Unlock()

	// определяем нужно ли менять значение счетчика
	countInfo, isExist := counterStore.cache[nodeId]
	if countInfo.count < 1 || !isExist {
		return
	}

	// уменьшаем значение счетчика
	counterStore.cache[nodeId].count--
}

// обновляем кэш хранилища
func (counterStore *counterStorage) update(senderMap map[int64]SenderStruct) {

	// блокируем хранилище
	counterStore.mu.Lock()

	// записываем пустой map
	counterStore.cache = make(map[int64]*nodeCountInfo)

	// сохраняем новые ноды
	for nodeId := range senderMap {

		nodeConf, isExist := GetConfigNode(nodeId)
		if !isExist {
			log.Errorf("не найдена конфигурация для go_sender node #%d", nodeId)
			return
		}
		counterStore.cache[nodeId] = &nodeCountInfo{
			count: 0,
			limit: nodeConf.Limit,
		}
	}

	// разблокируем хранилище
	counterStore.mu.Unlock()
}

// сбрасываем значение счетчика для ноды
func (counterStore *counterStorage) clearNodeCounter(nodeId int64) {

	// блокируем хранилище
	counterStore.mu.Lock()

	// разблокируем хранилище при выходе из функции
	defer counterStore.mu.Unlock()

	// определяем нужно ли менять значение счетчика
	countInfo, isExist := counterStore.cache[nodeId]
	if countInfo.count < 1 || !isExist {
		return
	}

	// обнуляем счетчик
	counterStore.cache[nodeId].count = 0
}

// получаем нужную нам ноду из слайса
func GetConfigNode(nodeId int64) (conf.GoNodeShardingStruct, bool) {

	// получаем конфигурацию ноды
	nodes := conf.GetShardingConfig().Go["sender"].Nodes
	for _, item := range nodes {

		if item.Id == nodeId {
			return item, true
		}
	}

	return conf.GoNodeShardingStruct{}, false
}
