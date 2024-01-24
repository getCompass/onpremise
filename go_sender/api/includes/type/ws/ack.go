package ws

import (
	"fmt"
	"time"
)

/*
 * модель для работы с подтверждением получения websocket событий клиентами
 */

const ackItemExpire = 60 * time.Second

// стуркута ack ивента
type ackStruct struct {
	time  time.Time
	event string
}

// структура для подсчета добавляеных и подтвержденных ws событий
type ackEventStruct struct {
	AckRequested int64 `json:"ack_requested"`
	AckConfirmed int64 `json:"ack_confirmed"`
}

// подтверждаем получение ранее отправленного websocket события
func ConfirmAck(connection *ConnectionStruct, uniqueID string) (delay time.Duration, err error) {

	connection.ackList.mx.Lock()
	ack, exist := connection.ackList.store[uniqueID]
	defer connection.ackList.mx.Unlock()
	if !exist {
		return 0, fmt.Errorf("unique_id for confirm not found or may be confirmed before")
	}

	// получаем обьект с аналитикой по ивенту
	connection.analyticsData.AckEventListStore.mx.Lock()
	defer connection.analyticsData.AckEventListStore.mx.Unlock()

	ackEvent, exist := connection.analyticsData.AckEventListStore.store[ack.event]
	if !exist {
		return 0, fmt.Errorf("event for confirm not found or may be confirmed before")
	}

	// инкрементим
	ackEvent.AckConfirmed++

	// записываем обратно
	connection.analyticsData.AckEventListStore.store[ack.event] = ackEvent

	delete(connection.ackList.store, uniqueID)
	return time.Since(ack.time), nil
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// добавить ack для дальнейшего подтверждения
func (connection *ConnectionStruct) addAck(uniqueID string, createdAt time.Time, event string) {

	if connection.analyticsData.AckEventListStore == nil {
		return
	}

	connection.analyticsData.AckEventListStore.mx.Lock()

	// получаем обьект с аналитикой по ивенту
	ackEvent, isExist := connection.analyticsData.AckEventListStore.store[event]
	if !isExist {

		// если не существует создаем новый
		ackEvent = &ackEventStruct{
			AckRequested: 0,
			AckConfirmed: 0,
		}
	}

	// инкрементим
	ackEvent.AckRequested++

	// записываем обратно
	connection.analyticsData.AckEventListStore.store[event] = ackEvent
	connection.analyticsData.AckEventListStore.mx.Unlock()

	ackObj := &ackStruct{
		time:  createdAt,
		event: event,
	}
	connection.ackList.mx.Lock()
	connection.ackList.store[uniqueID] = ackObj
	connection.ackList.mx.Unlock()
}

// пробегается по хранилищу событий и удаляем протухшие элементы
func (connection *ConnectionStruct) cleanStorage() {

	connection.ackList.mx.Lock()
	defer connection.ackList.mx.Unlock()
	for uniqueID, ackEvent := range connection.ackList.store {

		if time.Since(ackEvent.time) > ackItemExpire {

			delete(connection.ackList.store, uniqueID)
		}
	}
}
