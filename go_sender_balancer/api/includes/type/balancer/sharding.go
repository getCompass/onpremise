package balancer

import (
	"go_sender_balancer/api/conf"
	"sync"
)

// -------------------------------------------------------
// взаимодействие с конфигурацией
// -------------------------------------------------------

// структура sender
type senderStorage struct {
	mu    sync.RWMutex
	cache map[int64]SenderStruct
}

// структура sender
type SenderStruct struct {
	Id    int64  `json:"id"`
	Host  string `json:"host"`
	Port  string `json:"port"`
	Limit int64  `json:"limit"`
}

var (

	// хранилище sender нод
	senderStore = senderStorage{
		cache: make(map[int64]SenderStruct),
	}
)

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// обновляем конфигурацию
func UpdateConfig() {

	config := conf.GetShardingConfig().Go

	// обновляем конфиги
	updateSenderConfig(config["sender"].Nodes)
}

// получаем конфигурацию sender ноды
func GetSenderItem(nodeId int64) (SenderStruct, bool) {

	return senderStore.get(nodeId)
}

// получаем список sender нод
func GetSenderIdList() []int64 {

	// блокируем хранилище
	senderStore.mu.RLock()

	// получаем массив sender нод
	senderItemMap := senderStore.cache

	// разблокируем хранилище при выходе из функции
	senderStore.mu.RUnlock()

	// формируем массив идентификаторов нод
	var senderIdList []int64
	for item := range senderItemMap {
		senderIdList = append(senderIdList, item)
	}

	return senderIdList
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// получаем массивы для хранилищ
func updateSenderConfig(nodes []conf.GoNodeShardingStruct) {

	// получаем массив sender нод
	senderMap := make(map[int64]SenderStruct)
	for _, value := range nodes {

		senderMap[value.Id] = SenderStruct{
			Id:    value.Id,
			Host:  value.Host,
			Port:  value.Port,
			Limit: value.Limit,
		}
	}

	// сохраняем запись в хранилище нод
	senderStore.set(senderMap)

	// обновляем счетчик подключений к нодам
	counterStore.update(senderMap)
}

// -------------------------------------------------------
// METHODS FOR senderStore
// -------------------------------------------------------

// получаем запись из хранилища
func (senderStore *senderStorage) get(nodeId int64) (SenderStruct, bool) {

	// блокируем хранилище
	senderStore.mu.RLock()

	// разблокируем хранилище при выходе из функции
	defer senderStore.mu.RUnlock()

	// сохраняем в хранилище
	senderItem, isExit := senderStore.cache[nodeId]
	if !isExit {
		return SenderStruct{}, false
	}

	return senderItem, true
}

// сохраняем запись в хранилище
func (senderStore *senderStorage) set(senderMap map[int64]SenderStruct) {

	// блокируем хранилище
	senderStore.mu.Lock()

	// сохраняем в хранилище
	senderStore.cache = senderMap

	// разблокируем хранилище
	senderStore.mu.Unlock()
}
