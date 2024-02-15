package ws

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	analyticsWs "go_sender/api/includes/type/analytics/ws"
	wsEventHandler "go_sender/api/includes/type/ws/event/handler"
	"go_sender/api/includes/type/ws/event/method_config"
	"time"
)

// отправляет событие по user_id пользователя на все открытые соединения
func (ucStore *UserConnectionStore) SendEventViaUserID(userId int64, eventDataByVersion map[int]map[int][]byte, wsUniqueID string, eventName string) {

	// достаем объект пользователя
	ucStore.mx.RLock()
	userItem, isExist := ucStore.store[userId]
	ucStore.mx.RUnlock()

	if !isExist {
		return
	}

	// блокируем объект с подключениями пользователя на запись
	userItem.mx.RLock()
	defer userItem.mx.RUnlock()
	for _, connection := range userItem.connList {

		// проверяем, что ws соединение обрабатывает этот handler события
		eventVersionList, exist := eventDataByVersion[connection.HandlerVersion]
		if !exist {
			continue
		}

		// пробегаемся по каждой версии событий
		for eventVersion, eventDataJson := range eventVersionList {

			// проверяем, что ws соединение поддерживает эту версию
			if !isConnectionSupportEventByVersion(connection, eventName, eventVersion) {
				continue
			}

			// отправляем ивент в броадкаст
			sendExternalEventToBroadcast(connection, eventName, wsUniqueID, eventDataJson)
		}
	}
}

// отправляем событие пользователю на конкретное подключение через connection_id
func (ucStore *UserConnectionStore) SendEventViaConnectionID(userID int64, connectionID int64, eventName string, EventVersionList map[int]interface{}, wsUniqueID string) {

	// получаем конкретное подключение пользователя по connectionID
	connectionObj, exist := ucStore.getByConnectionID(userID, connectionID)
	if !exist {
		return
	}

	// пробегаемся по каждой версии ws-событий
	for eventVersion, eventDataJson := range EventVersionList {

		// проверяем, что ws соединение поддерживает эту версию
		if !isConnectionSupportEventByVersion(connectionObj, eventName, eventVersion) {
			continue
		}

		// получаем структуру ws-события
		var wsUsers interface{}
		translatedEventStructure, exist := wsEventHandler.Translate(eventName, int64(eventVersion), eventDataJson, wsUsers, connectionObj.HandlerVersion, wsUniqueID)
		if !exist {
			return
		}

		// упаковываем структуру в json
		eventData, err := json.Marshal(translatedEventStructure)
		if err != nil {
			return
		}

		// оптравляем ивент на broadcast
		sendExternalEventToBroadcast(connectionObj, eventName, wsUniqueID, eventData)
	}
}

// поддерживает ли ws соединение событие этой версии
func isConnectionSupportEventByVersion(connection *ConnectionStruct, eventName string, eventVersion int) bool {

	// проверяем, что ws соединение поддерживает эту версию
	isSupported, isEventExist := method_config.IsEventVersionSupported(connection.MethodConfigHash, eventName, eventVersion)

	// если соединение поддерживает версионность событий и событие НЕ найдено, то пропускает его
	if connection.isHaveSupportVersionedEvent && !isEventExist {
		return false
	}

	// если соединение поддерживает версионность событий и событие НЕ поддерживается, то пропускает его
	if connection.isHaveSupportVersionedEvent && !isSupported {
		return false
	}

	return true
}

// SendInternalEventViaConnection отправляем внутреннее событие пользователю на конкретное подключение
func (connection *ConnectionStruct) SendInternalEventViaConnection(userID int64, event string, eventData interface{}, wsUniqueID string) {

	// всегда 1 версия события, поскольку здесь нет версионности событий
	// через этот метод отправляются события в тот момент, когда мы ничего не знаем о поддерживемых
	// версиях ws событий подключения
	eventVersion := int64(1)

	// получаем структуру ws-события
	var wsUsers interface{}
	translatedEventStructure, exist := wsEventHandler.Translate(event, eventVersion, eventData, wsUsers, connection.HandlerVersion, wsUniqueID)
	if !exist {

		log.Errorf("%d нет версии", userID)
		return
	}

	// упаковываем структуру в json
	eventDataJson, err := json.Marshal(translatedEventStructure)
	if err != nil {
		log.Errorf("Не удалось упаковать структуру в JSON")
		return
	}

	// оптравляем ивент на broadcast
	sendEventToBroadcast(connection, event, wsUniqueID, eventDataJson)
}

// получаем соединение пользователя по connectionID
func (ucStore *UserConnectionStore) getByConnectionID(userID int64, connectionID int64) (*ConnectionStruct, bool) {

	// если нет активных подключений у пользователя
	ucStore.mx.RLock()
	userConnectionList, exist := ucStore.store[userID]
	ucStore.mx.RUnlock()
	if !exist {
		return &ConnectionStruct{}, false
	}

	// получаем userConnection
	userConnectionList.mx.RLock()
	userConnectionInterface, exist := userConnectionList.connList[connectionID]
	userConnectionList.mx.RUnlock()

	// если подключение не найдено
	if !exist {
		return &ConnectionStruct{}, false
	}

	return userConnectionInterface, true
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// отправляем внешний ивент (который пришел в go_sender) на броадкаст
func sendExternalEventToBroadcast(connection *ConnectionStruct, event string, wsUniqueID string, eventJsonStructure []byte) {

	// если коннкт закрыт для получения новых ws
	// флаг ставится в момент когда пользователь разлогинился или был удален но ему должны доприйти ws чтобы он мог правильно жить
	if connection.isBlock {
		return
	}

	sendEventToBroadcast(connection, event, wsUniqueID, eventJsonStructure)
}

// отправляем ивент в броадкаст
func sendEventToBroadcast(connection *ConnectionStruct, event string, wsUniqueID string, eventJsonStructure []byte) {

	// создадим и запишем обьект аналитики
	analyticItem := createAnalyticItemByConnection(connection, event)

	connection = connectIncAck(connection, event, wsUniqueID)

	trySendEventMessage(connection, event, eventJsonStructure, analyticItem)
}

// создадим обьект аналитики из подключения ws
func createAnalyticItemByConnection(connection *ConnectionStruct, event string) *analyticsWs.WsStruct {

	// создадим и запишем обьект аналитики
	analyticItem := &analyticsWs.WsStruct{
		Uuid:      functions.GenerateUuid(),
		UserId:    connection.UserId,
		CreatedAt: functions.GetCurrentTimeStamp(),
		EventName: event,
		Platform:  connection.platform,
		CompanyId: connection.CompanyId,
	}

	return analyticItem
}

// инкрементим в подключении количество запросов
func connectIncAck(connection *ConnectionStruct, event string, wsUniqueID string) *ConnectionStruct {

	// добавляем для соединения уникальный идентификатор
	connection.addAck(wsUniqueID, time.Now(), event)

	// инкрементим количество отправленных websocket событий
	connection.incAckRequested()

	return connection
}

// попробуем отправить ивент сообщение
func trySendEventMessage(connection *ConnectionStruct, event string, eventJsonStructure []byte, analyticItem *analyticsWs.WsStruct) {

	select {
	case connection.broadcast <- eventJsonStructure:
	default:
		select {
		case connection.broadcast <- eventJsonStructure:
		default:

			log.Errorf("could not send message connection_id: %d user_id: %d event: %s", connection.ConnId, connection.UserId, event)
			analyticItem.OnEventIsNotSend()
			connection.analyticStoreWs.Add(analyticItem, functions.GenerateUuid())
			return
		}
	}

	analyticItem.OnEventSend()
	connection.analyticStoreWs.Add(analyticItem, functions.GenerateUuid())
}
