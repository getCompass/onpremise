package ws

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/balancer"
	"sync"
)

type UserConnectionStore struct {
	store        map[int64]*connectionList
	mx           sync.RWMutex
	balancerConn *balancer.Conn
}

type connectionList struct {
	connList map[int64]*ConnectionStruct
	mx       sync.RWMutex
}

func MakeUserConnectionStore(balancerConn *balancer.Conn) *UserConnectionStore {

	return &UserConnectionStore{
		store:        make(map[int64]*connectionList),
		mx:           sync.RWMutex{},
		balancerConn: balancerConn,
	}
}

// закрываем соедиения пользователя
func (ucStore *UserConnectionStore) CloseConnectionsByUserID(userId int64) {

	// получаем соединения из хранилища
	ucStore.mx.RLock()
	connList, isExist := ucStore.store[userId]
	ucStore.mx.RUnlock()
	if !isExist {
		return
	}

	connList.mx.Lock()
	for k, v := range connList.connList {

		// закрываем подключение пользователя
		log.Infof("Закрываем соединение с подключением пользователя и удаляем из хранилища %d %d", v.UserId, v.ConnId)

		closeConnection(v)
		delete(connList.connList, k)
	}
	connList.mx.Unlock()

	// удаляем объект со всеми подключениями пользователя
	ucStore.mx.Lock()
	delete(ucStore.store, userId)
	ucStore.mx.Unlock()

	ucStore.balancerConn.DeleteConnectionFromBalancer(userId, true)

}

// закрываем соедиения пользователя по конкретному устройству
func (ucStore *UserConnectionStore) CloseConnectionsByDeviceID(userId int64, deviceId string) {

	// получаем соединения из хранилища
	ucStore.mx.RLock()
	userConnectionList, isExist := ucStore.store[userId]
	ucStore.mx.RUnlock()
	if !isExist {
		return
	}

	userConnectionList.mx.Lock()
	defer userConnectionList.mx.Unlock()

	for k, v := range userConnectionList.connList {

		if v.deviceId != deviceId {
			continue
		}
		closeConnection(v)
		delete(userConnectionList.connList, k)
	}
	if len(userConnectionList.connList) > 0 {
		ucStore.balancerConn.DeleteConnectionFromBalancer(userId, false)
	} else {
		ucStore.balancerConn.DeleteConnectionFromBalancer(userId, true)
	}
}

// Получаем информацию о подключении пользователя
func (ucStore *UserConnectionStore) IsUserOnline(userId int64) bool {

	ucStore.mx.RLock()
	defer ucStore.mx.RUnlock()
	_, exist := ucStore.store[userId]
	return exist
}

// получить версии подключений пользователя
func (ucStore *UserConnectionStore) GetConnectionVersions(userId int64) []int {

	// получаем объект пользователя
	ucStore.mx.RLock()
	userConnectionList, exist := ucStore.store[userId]
	ucStore.mx.RUnlock()
	if !exist {
		return []int{}
	}

	userConnectionList.mx.RLock()
	defer userConnectionList.mx.RUnlock()

	connectionVersions := make([]int, 0, len(userConnectionList.connList))
	for _, v := range userConnectionList.connList {
		connectionVersions = append(connectionVersions, v.HandlerVersion)
	}

	// возвращаем срез
	return connectionVersions
}

// получаем информацию о том есть ли онлайн подключение пользователя и в фокусе ли окно
func (ucStore *UserConnectionStore) GetUserOnlineState(userId int64) (isOnline bool, isFocused bool) {

	// получаем объект с подключениями пользователя
	ucStore.mx.RLock()
	userConnectionList, exist := ucStore.store[userId]
	ucStore.mx.RUnlock()
	if !exist {
		return false, false
	}

	// проходимся по каждому подключению и смотрим focus статус
	isFocused = false
	userConnectionList.mx.RLock()
	defer userConnectionList.mx.RUnlock()

	for _, v := range userConnectionList.connList {

		if v.isFocused {

			isFocused = true
			break
		}
	}

	return true, isFocused
}

// получаем информацию об онлайн подключениях пользователя
func (ucStore *UserConnectionStore) GetConnectionList(userId int64) []*ConnectionStruct {

	ucStore.mx.RLock()
	userConnectionList, exist := ucStore.store[userId]
	ucStore.mx.RUnlock()
	if !exist {
		return []*ConnectionStruct{}
	}

	// инициализируем список соединиений
	connectionListTemp := make([]*ConnectionStruct, 0, len(userConnectionList.connList))
	userConnectionList.mx.RLock()
	defer userConnectionList.mx.RUnlock()

	for _, v := range userConnectionList.connList {
		connectionListTemp = append(connectionListTemp, v)
	}

	return connectionListTemp
}

// получаем список device_id устройств пользователя, которые сейчас online
func (ucStore *UserConnectionStore) GetOnlineDeviceList(userId int64) []string {

	ucStore.mx.RLock()
	userConnectionList, exist := ucStore.store[userId]
	ucStore.mx.RUnlock()
	if !exist {
		return []string{}
	}

	// инцииализируем массив для ответа
	var output []string
	userConnectionList.mx.RLock()
	defer userConnectionList.mx.RUnlock()

	for _, v := range userConnectionList.connList {
		output = append(output, v.deviceId)
	}

	return output
}

// получаем список всех id пользователей
func (ucStore *UserConnectionStore) GetAllUserIdList() []int64 {

	ucStore.mx.RLock()
	defer ucStore.mx.RUnlock()

	idList := make([]int64, 0, len(ucStore.store))
	for k := range ucStore.store {
		idList = append(idList, k)
	}

	return idList
}

// закрываем соединение с подключением и удаляем его из хранилища
// !!! эта функкция закрывает коннект но не удаляет пользователя из сторов, для удаления из сторонов нужна функция CloseConnectionsByDeviceID
func closeConnection(connection *ConnectionStruct) {

	// временная метка, когда было закрыто соединение
	closedAt := functions.GetCurrentTimeStamp()

	// если подключение было авторизованным
	if connection.UserId > 0 {

		// сохраняем запись с аналитическими данными по соединению
		connection.analyticStore.storeAnalyticsData(connection.analyticsData, closedAt, connection.closeConnectionError)
	}

	// удаляем коннект из хранилищ тайпингов тредов
	if connection.ThreadKStore != nil {
		connection.ThreadKStore.DeleteConnectionFromThread(connection.ThreadUserConnectionStore, connection.UserId, connection.ConnId)
	}

	// Закрываем соединение
	err := connection.connection.Close()
	if err != nil {
		log.Errorf("Не смогли закрыть соединение. Error: %v", err)
	}
}
