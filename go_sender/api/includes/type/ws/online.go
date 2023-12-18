package ws

/*
 * файл реализует логику - получение онлайна текущей ноды go_sender
 */

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"sync"
)

// структура снимка
type onlineSnapshotStruct struct {
	expireAt int64              // временная метка, когда снимок будет считаться протухшим
	userList []UserOnlineDevice // снимок с онлайном пользовательских устройств
}

var onlineSnapshotStore sync.Map

const expireTime = 180

// функция инициализирует процесс получения онлайна ноды совершается снимок онлайна,
// на момент вызова функции который в последствии частями возвращается на go_sender
func InitializeSnapshot(userConnectionStore *UserConnectionStore, uuid string) {

	// если снимок уже существует
	_, isExist := onlineSnapshotStore.Load(uuid)
	if isExist {
		return
	}

	// создаем снимок
	snapshotObj := onlineSnapshotStruct{
		expireAt: functions.GetCurrentTimeStamp() + expireTime,
		userList: userConnectionStore.getOnlineUserList(),
	}

	// сохраняем в хранилище
	onlineSnapshotStore.Store(uuid, snapshotObj)
}

// field user_list in response sender.getOnlineUserList
type UserOnlineDevice struct {
	UserId   int64  `json:"user_id"`
	Platform string `json:"platform"`
	DeviceId string `json:"device_id"`
}

// возвращает пачку из N идентификаторов онлайна
func GetOnlineUserList(uuid string, count int, offset int) ([]UserOnlineDevice, bool) {

	// получаем объект снапшота
	temp, isExist := onlineSnapshotStore.Load(uuid)
	if !isExist {
		return []UserOnlineDevice{}, false
	}

	onlineSnapshotObj := temp.(onlineSnapshotStruct)

	startCursor := offset
	endOffset := offset + count

	if len(onlineSnapshotObj.userList) < endOffset {
		return onlineSnapshotObj.userList[offset:], false
	}

	// пишем в хранилище
	return onlineSnapshotObj.userList[startCursor:endOffset], true
}

// получаем срез с идентификаторами онлайн пользователями
func (ucStore *UserConnectionStore) GetOnlineUserCount() int {

	ucStore.mx.Lock()
	defer ucStore.mx.Unlock()

	return len(ucStore.store)
}

// закрываем соедиения
func (ucStore *UserConnectionStore) CloseAllConnections() {

	ucStore.mx.Lock()
	defer ucStore.mx.Unlock()

	for k1, userItem := range ucStore.store {

		for k2, conn := range userItem.connList {
			closeConnection(conn)
			delete(userItem.connList, k2)
		}
		delete(ucStore.store, k1)
		ucStore.balancerConn.DeleteConnectionFromBalancer(k1, true)
	}
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// получаем срез с идентификаторами онлайн пользователями
func (ucStore *UserConnectionStore) getOnlineUserList() []UserOnlineDevice {

	// проходимся по онлайн пользователям и собираем ответ
	var output []UserOnlineDevice

	ucStore.mx.RLock()
	for _, connList := range ucStore.store {

		connList.mx.RLock()
		for _, v := range connList.connList {

			output = append(output, UserOnlineDevice{
				UserId:   v.UserId,
				Platform: v.platform,
				DeviceId: v.deviceId,
			})
		}
		connList.mx.RUnlock()
	}
	ucStore.mx.RUnlock()

	return output
}
