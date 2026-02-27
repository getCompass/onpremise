package sender

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/balancer"
	"go_sender/api/includes/type/event"
	Isolation "go_sender/api/includes/type/isolation"
	"go_sender/api/includes/type/structures"
	"go_sender/api/includes/type/thread"
	"go_sender/api/includes/type/ws"
	"go_sender/api/includes/type/ws/event/handler"
)

// информация о соедиении
type ConnectionInfoStruct struct {
	ConnId      int64  `json:"connection_id"`
	UserId      int64  `json:"user_id"`
	IpAddress   string `json:"ip_address"`
	ConnectedAt int64  `json:"connected_at"`
	UserAgent   string `json:"user_agent"`
	Platform    string `json:"platform"`
	IsFocused   int    `json:"is_focused"`
}

// field user_list in response sender.getOnlineUserList
type UserOnlineDevicesStruct struct {
	UserId   int64  `json:"user_id"`
	Platform string `json:"platform"`
	DeviceId string `json:"device_id"`
}

// метод для отправки ивента
func SendEvent(isolation *Isolation.Isolation, userList []int64, event string, eventListByVersion map[int]interface{}, wsUsers interface{}, channel string) []structures.UserConnectionStruct {

	// отправляем запрос с отправкой сообщения
	userList = doPublish(isolation, userList, event, eventListByVersion, wsUsers, channel)

	// формируем и возвращаем список соединений на которые был отправлен запрос
	return prepareSuccessSendEventConnectionList(userList)
}

// отправляем ивент
func doPublish(isolation *Isolation.Isolation, userList []int64, event string, eventListByVersion map[int]interface{}, wsUsers interface{}, channel string) []int64 {

	// разделяем пользователей на онлайн и на тех кто в фокусе
	onlineUserList, focusedUserList := splitUserByFocused(isolation, userList)

	// генерируем ws_unique_id для всех пользователей - один и тот же
	wsUniqueID := functions.GenerateUuid()

	// начинаем отправку сообщений в отдельной рутине
	go func() {

		// проходимся по каждому онлайн пользователю
		for _, userId := range onlineUserList {
			sentEventToUserId(isolation, userId, event, eventListByVersion, wsUsers, wsUniqueID, channel)
		}
	}()

	return focusedUserList
}

// разделяем пользователей на тех кто онлайн и кто у кого в фокусе
func splitUserByFocused(isolation *Isolation.Isolation, userList []int64) ([]int64, []int64) {

	focusedUserList := make([]int64, 0)
	onlineToUserList := make([]int64, 0)
	for _, userID := range userList {

		// получаем информацию об онлайн пользователя, а также focusStatus его подключений
		isOnline, isFocused := isolation.UserConnectionStore.GetUserOnlineState(userID)

		// если пользователь не имеет активное подключение
		if !isOnline {
			continue
		}

		// добавляем пользователя в срез для рассылки WS события
		onlineToUserList = append(onlineToUserList, userID)

		// если пользователь имеет focused подключение
		if isFocused {
			focusedUserList = append(focusedUserList, userID)
		}
	}
	return onlineToUserList, focusedUserList
}

// формируем массив со списком соединений на которые успешно отправили события
func prepareSuccessSendEventConnectionList(userList []int64) []structures.UserConnectionStruct {

	// инициализируем запрос с соединениями на которые был успешно отправлен запрос
	var successSendEventConnectionList []structures.UserConnectionStruct

	// проходимся по каждому пользователю которому удалось отправить
	for _, item := range userList {

		// добваляем соединение в ответ
		successSendEventConnection := structures.UserConnectionStruct{
			UserId:          item,
			LastConnectedAt: functions.GetCurrentTimeStamp(),
		}
		successSendEventConnectionList = append(successSendEventConnectionList, successSendEventConnection)
	}

	return successSendEventConnectionList
}

// отправляем ивент пользователю
func sentEventToUserId(isolation *Isolation.Isolation, userId int64, event string, eventListByVersion map[int]interface{}, wsUsers interface{}, wsUniqueID string, channel string) {

	// подготавливаем каждую версию события к handler-версии WS соединения
	var eventDataListByHandlerVersion = prepareEventVersionListToHandlerVersions(isolation.UserConnectionStore, userId, event, eventListByVersion, wsUsers, wsUniqueID)

	// отправляем событие на все соединения пользователя
	isolation.UserConnectionStore.SendEventViaUserID(userId, eventDataListByHandlerVersion, wsUniqueID, event, channel)
}

// подготавливаем каждую версию события к handler-версии WS соединения
// что это такое смотрим здесь – api/includes/type/ws/event/handler/v1.go
// PS вообще это огромной роли не играет, но в будущем может помочь
func prepareEventVersionListToHandlerVersions(userConnectionStore *ws.UserConnectionStore, userId int64, event string, eventListByVersion map[int]interface{}, wsUsers interface{}, wsUniqueID string) map[int]map[int][]byte {

	// получаем версии подключений пользователя
	handlerVersions := userConnectionStore.GetConnectionVersions(userId)

	// здесь лежит массив массивов:
	// [
	// 	handler_version => [
	// 		event_version => event_data
	// 		...
	// 	]
	// ]
	var eventDataListByHandlerVersion = map[int]map[int][]byte{}

	// формируем структуру WS события под версию подключения пользователя
	for _, handlerVersion := range handlerVersions {

		// если ранее не проходились по этой версии
		if _, exist := eventDataListByHandlerVersion[handlerVersion]; !exist {
			eventDataListByHandlerVersion[handlerVersion] = make(map[int][]byte)
		}

		// пробегаемся по каждой версии события
		for eventVersion, eventData := range eventListByVersion {

			// форматируем событие под версию хендлера
			formattedEventData, ok := makeEventData(handlerVersion, event, int64(eventVersion), eventData, wsUsers, wsUniqueID)

			// если не удалось, то пропускаем
			if !ok {
				continue
			}

			eventDataListByHandlerVersion[handlerVersion][eventVersion] = formattedEventData
		}
	}

	return eventDataListByHandlerVersion
}

// формируем ивент дату в зависимости от версии подключения
func makeEventData(handlerVersion int, event string, eventVersion int64, eventData interface{}, wsUsers interface{}, wsUniqueID string) ([]byte, bool) {

	// иначе получаем структуру
	handlerVersionStructure, exist := wsEventHandler.Translate(event, eventVersion, eventData, wsUsers, handlerVersion, wsUniqueID)

	// если структура для данной версии не найдена, то переходим к след иттерации
	if !exist {

		log.Debug(fmt.Sprintf("Не формировали структуру для версии: %d", handlerVersion))
		return []byte{}, false
	}

	// упаковываем структуру в json
	jsonStructure, err := json.Marshal(handlerVersionStructure)
	if err != nil {

		log.Errorf("Не удалось упаковать структуру в JSON")
		return []byte{}, false
	}
	return jsonStructure, true
}

// метод, для получения соединений пользователя
func GetAllUserConnectionsInfo(isolation *Isolation.Isolation, userId int64) []ConnectionInfoStruct {

	// получаем список соединений пользователя
	connectionList := isolation.UserConnectionStore.GetConnectionList(userId)
	onlineConnectionList := make([]ConnectionInfoStruct, 0)
	for _, v := range connectionList {

		onlineConnectionList = append(onlineConnectionList, ConnectionInfoStruct{
			UserId: v.UserId,
			ConnId: v.ConnId,
		})
	}

	return onlineConnectionList
}

// закрывает соединения пользователя
func CloseConnectionsByUserId(isolation *Isolation.Isolation, userId int64) {

	// закрываем соединения
	isolation.UserConnectionStore.CloseConnectionsByUserID(userId)

	if isolation.GetCompanyId() == 0 {

		// удаляем из хранилища информацию о соединениях пользователя
		balancer.RemoveUserConnection(userId)
	}
}

// закрывает соединения пользователя по конкретному устройству
func CloseConnectionsByDeviceId(isolation *Isolation.Isolation, userId int64, deviceId string) {

	// закрываем соединения
	isolation.UserConnectionStore.CloseConnectionsByDeviceID(userId, deviceId)
}

// отправляем запросы для получения онлайна
func GetOnlineOfflineUserList(isolation *Isolation.Isolation, userList []int64) ([]int64, []int64) {

	onlineUserList := make([]int64, 0)
	offlineUserList := make([]int64, 0)

	// проходимся по каждому пользователю
	for _, userID := range userList {

		isOnline := isolation.UserConnectionStore.IsUserOnline(userID)
		if isOnline {
			onlineUserList = append(onlineUserList, userID)
			continue
		}

		offlineUserList = append(offlineUserList, userID)
	}

	return onlineUserList, offlineUserList
}

// добавляем пользователей к треду
func AddUsersToThread(isolation *Isolation.Isolation, tucStore *thread.UserConnectionStore, taStore *thread.AuthStore, userList []int64, threadKey string, channel string) {

	var wsUsers interface{}

	// формируем все версии события event.need_verify_thread_opened
	EventVersionList := event.MakeNeedVerifyThreadOpened(threadKey)

	// генерируем ws_unique_id для всех пользователей - один и тот же
	wsUniqueID := functions.GenerateUuid()

	for _, userId := range userList {

		// добавляем пользователя в список читателей треда
		tucStore.AddUserToThread(taStore, threadKey, userId)

		sentEventToUserId(isolation, userId, event.NeedVerifyThreadOpenedEventName, EventVersionList, wsUsers, wsUniqueID, channel)
	}
}

// SendTypingEvent отправляем тайпинг
func SendTypingEvent(isolation *Isolation.Isolation, localUserConnectionList []int64, event string, eventVersionList map[int]interface{}, channel string) []structures.UserConnectionStruct {

	var userList []int64

	// проходим по списку соединений, собирая список userId
	for _, userId := range localUserConnectionList {
		userList = append(userList, userId)
	}

	sentUserList := doPublish(isolation, userList, event, eventVersionList, nil, channel)
	return makeSentUserConnectionList(sentUserList)
}

// формируем список соединений, на которые были отправлены события
func makeSentUserConnectionList(sentUserList []int64) []structures.UserConnectionStruct {

	var userConnectionList []structures.UserConnectionStruct

	for _, userId := range sentUserList {

		userConnection := structures.UserConnectionStruct{
			UserId: userId,
		}
		userConnectionList = append(userConnectionList, userConnection)
	}

	return userConnectionList
}

// отправляем тайпинг в тред
func SendThreadTypingEvent(userConnectionStore *ws.UserConnectionStore, threadUserConnectionStore *thread.UserConnectionStore, event string, threadKey string, EventVersionList map[int]interface{}) {

	// генерируем ws_unique_id для всех пользователей - один и тот же
	wsUniqueID := functions.GenerateUuid()

	// отправляем тайпинг слушателям треда
	connectionIdListGroupByUserId := threadUserConnectionStore.GetThreadListeners(threadKey)

	// пробегаемся по всем подключениям
	threadUserConnectionStore.Lock()
	for userId, connectionList := range connectionIdListGroupByUserId {

		for _, connectionId := range connectionList {

			go userConnectionStore.SendEventViaConnectionID(userId, connectionId, event, EventVersionList, wsUniqueID)
		}
	}
	threadUserConnectionStore.UnLock()
}

// отправить ивент о входящем звонке конкретному пользователю
// в ответе получаем срез device_id на которые был отправлен event
func SendIncomingCall(isolation *Isolation.Isolation, userId int64, eventVersionList map[int]interface{}, wsUsers interface{}, channel string) []string {

	// получаем список device_id устройств пользователя, которые сейчас online, на них и будут отправлены события
	sentDeviceList := isolation.UserConnectionStore.GetOnlineDeviceList(userId)

	// отправляем событие
	go func() {

		sentEventToUserId(isolation, userId, "action.call_incoming", eventVersionList, wsUsers, functions.GenerateUuid(), channel)
	}()

	return sentDeviceList
}

// отправить ивент о создании конференции Jitsi конкретному пользователю
// в ответе получаем срез device_id на которые был отправлен event
func SendJitsiConferenceCreated(isolation *Isolation.Isolation, userId int64, event string, eventVersionList map[int]interface{}, wsUsers interface{}, channel string) []string {

	// получаем список device_id устройств пользователя, которые сейчас online, на них и будут отправлены события
	sentDeviceList := isolation.UserConnectionStore.GetOnlineDeviceList(userId)

	// отправляем событие
	go func() {
		sentEventToUserId(isolation, userId, event, eventVersionList, wsUsers, functions.GenerateUuid(), channel)
	}()

	return sentDeviceList
}
