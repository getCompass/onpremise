package tcp

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender_balancer/api/includes/type/balancer"
	"go_sender_balancer/api/includes/type/structures"
	"strings"
	"time"
)

// информация о соедиении
type ConnectionInfoStruct struct {
	SenderNodeId int64 `json:"sender_node_id"`

	ConnId      int64  `json:"connection_id"`
	UserId      int64  `json:"user_id"`
	IpAddress   string `json:"ip_address"`
	ConnectedAt int64  `json:"connected_at"`
	UserAgent   string `json:"user_agent"`
	Platform    string `json:"platform"`
	IsFocused   int    `json:"is_focused"`
}

// отправить один ивент всем локальным сендерам
func SendEvent(userConnectionList []structures.UserConnectionStruct, event string, eventVersionList interface{}, wsUsers interface{}, uuid string, routineKey string, channel string) []structures.UserConnectionStruct {

	// получаем id всех нод go_sender
	senderNodeIdList := getAllSenderNodeList(userConnectionList)

	// инициализируем список подключений, которые успешно получили эвент
	var sentConnectionList []structures.UserConnectionStruct

	// отправляем запросы на каждую ноду
	for _, item := range senderNodeIdList {

		// отправляем запрос с отправкой сообщения на локальный go_sender
		userList := structures.ConvertUserConnectionListToUserList(userConnectionList)
		userList = doCallPublish(item, userList, event, eventVersionList, wsUsers, uuid, routineKey, channel)

		// формируем список соединений на которые был отправлен запрос
		successSendEventConnection := prepareSuccessSendEventConnectionList(userList, item)

		// добавляем к списку подключений, которые успешно получили эвент
		sentConnectionList = append(sentConnectionList, successSendEventConnection...)
	}

	// возвращаем список подключений, на который успешно отправили эвент
	return sentConnectionList
}

// получаем массив нод на которые отправим эвенты
func getAllSenderNodeList(userConnectionList []structures.UserConnectionStruct) []int64 {

	var senderNodeIdList []int64
	allSenderNodeList := balancer.GetSenderIdList()

	// пробегаемся по массиву пользовательских соединений
	for _, item := range userConnectionList {

		senderNodeIdList = append(senderNodeIdList, item.SenderNodeId)
	}

	// убираем одинаковые
	senderNodeIdList = functions.UniqueInt64(senderNodeIdList)

	// если их нет, кладем в массив все текущие ноды на дпс
	if len(senderNodeIdList) == 0 {
		senderNodeIdList = allSenderNodeList
	}

	return senderNodeIdList
}

// формируем массив со списком соединений на которые успешно отправили события
func prepareSuccessSendEventConnectionList(userList []int64, nodeId int64) []structures.UserConnectionStruct {

	// инициализируем запрос с соединениями на которые бул успешно отправлен запрос
	var successSendEventConnectionList []structures.UserConnectionStruct

	// проходимся по каждому пользователю которому удалось отправить
	for _, item := range userList {

		// добваляем соединение в ответ
		successSendEventConnection := structures.UserConnectionStruct{
			UserId:          item,
			SenderNodeId:    nodeId,
			LastConnectedAt: functions.GetCurrentTimeStamp(),
		}
		successSendEventConnectionList = append(successSendEventConnectionList, successSendEventConnection)
	}

	return successSendEventConnectionList
}

// GetAllUserConnectionsInfo метод, для получения соединений с всех нод go_sender для текущего ЦОДа
func GetAllUserConnectionsInfo(userId int64) []ConnectionInfoStruct {

	// получаем идентификаторы ноды для этого пользователя
	senderNodeList := balancer.GetSenderNodeIdListForUser(userId)

	// инициализцируем переменную, собирающую коннекты со всех go_sender нод
	var output []ConnectionInfoStruct

	// проходим по каждой ноде
	for _, item := range senderNodeList {

		// собираем конекты со всех go_sender нод
		connectionInfoList := doCallSenderGetOnlineConnectionsByUserId(item, userId)
		output = append(output, connectionInfoList...)
	}

	return output
}

// CloseConnectionsByUserId закрывает соедиения на go_sender нодах текущего ЦОДа
func CloseConnectionsByUserId(userId int64) {

	// получаем идентификаторы go_sender ноды для этого пользователя
	senderNodeList := balancer.GetSenderNodeIdListForUser(userId)

	// отправляем запросы на go_sender ноды для закрытия ws подключений
	for _, item := range senderNodeList {

		err := doCallCloseConnectionsByUserId(item, userId)
		if err != nil {

			log.Errorf("%v", err)
			return
		}

		// удаляем из хранилища информацию о соединениях пользователя
		balancer.RemoveUserConnection(userId, item)
	}
}

// GetOnlineOfflineUserList отправляем запросы на go_sender ноды для получения онлайна
func GetOnlineOfflineUserList(userList []int64) ([]int64, []int64) {

	var OnlineUserList []int64
	var OfflineUserList []int64

	// пробегаемся по всем пользователям
	userListGroupByNodeId := map[int64][]int64{}
	for _, item := range userList {

		senderNodeList := balancer.GetSenderNodeIdListForUser(item)
		for _, nodeId := range senderNodeList {
			userListGroupByNodeId[nodeId] = append(userListGroupByNodeId[nodeId], item)
		}
	}

	for nodeId, userList := range userListGroupByNodeId {

		onlineUserList, offlineUserList := doCallGetOnlineUsers(nodeId, userList)
		OnlineUserList = append(OnlineUserList, onlineUserList...)
		OfflineUserList = append(OfflineUserList, offlineUserList...)
	}

	return OnlineUserList, OfflineUserList
}

// SetToken метод для установки токена пользователю
func SetToken(nodeId int64, token string, userId int64, expire int64, platform string, deviceId string) error {

	response, err := doCallSetToken(nodeId, token, userId, expire, platform, deviceId)
	if err != nil {
		return err
	}

	if response.Status != "ok" {
		return err
	}

	return nil
}

// GetOnlineUserList функция собирает онлайн с ноды go_sender
func GetOnlineUserList(nodeId int64, uuid string, limit int) []UserOnlineDevicesStruct {

	var output []UserOnlineDevicesStruct
	var offset = 0
	var err error
	var response SendGetOnlineUserListRequestResponseStruct

	for {

		response, err = doCallGetOnlineUserList(nodeId, uuid, limit, offset)
		if err != nil {
			break
		}

		output = append(output, response.Response.UserList...)
		if !response.Response.HasNext {
			break
		}
		offset += limit
	}

	if err != nil {

		log.Errorf("%v, ждем 10 сек и пробуем снова", err)
		time.Sleep(10 * time.Second)
		return GetOnlineUserList(nodeId, uuid, limit)
	}
	return output
}

// отправить событие и voip-пуш при создании конференции Jitsi
func SendJitsiConferenceCreatedEvent(userId int64, userConnectionList []structures.UserConnectionStruct, event string, eventVersionList interface{}, pushData interface{}, wsUsers interface{}, uuid string, timeToLive int64, routineKey string, channel string) {

	// получаем id всех нод go_sender
	senderNodeIdList := getAllSenderNodeList(userConnectionList)

	// отправляем запросы на каждую ноду
	for _, item := range senderNodeIdList {

		doCallJitsiConferenceCreated(item, userId, event, eventVersionList, pushData, wsUsers, uuid, timeToLive, routineKey, channel)
	}
}

// отправить voip-пуш Jitsi
func SendJitsiVoIPPush(userId int64, userConnectionList []structures.UserConnectionStruct, pushData interface{}, uuid string, routineKey string) {

	// получаем id всех нод go_sender
	senderNodeIdList := getAllSenderNodeList(userConnectionList)

	// отправляем запросы на каждую ноду
	for _, item := range senderNodeIdList {

		doCallSendJitsiVoIPPush(item, userId, pushData, uuid, routineKey)
	}
}

// структура ответа со списком соедиениний пользователя
type doCallGetOnlineConnectionsByUserIdResponseStruct struct {
	Status   string `json:"status"`
	Response struct {
		OnlineConnectionList []ConnectionInfoStruct `json:"online_connection_list"`
	} `json:"response"`
}

// метод для отправки запроса на получения соединений пользователя на go_sender ноды
func doCallSenderGetOnlineConnectionsByUserId(nodeId int64, userId int64) []ConnectionInfoStruct {

	request := struct {
		Method string `json:"method"`
		UserId int64  `json:"user_id"`
	}{
		Method: "sender.getOnlineConnectionsByUserId",
		UserId: userId,
	}
	request.Method = strings.ToLower(request.Method)

	senderResponse := doCallGetOnlineConnectionsByUserIdResponseStruct{}
	err := doCallSender(nodeId, request, &senderResponse)
	if err != nil {

		log.Errorf("%v", err)
		return []ConnectionInfoStruct{}
	}

	// добавляем senderNodeId в ответ
	for item := range senderResponse.Response.OnlineConnectionList {
		senderResponse.Response.OnlineConnectionList[item].SenderNodeId = nodeId
	}

	return senderResponse.Response.OnlineConnectionList
}

// отправляем запрос
func doCallCloseConnectionsByUserId(nodeId int64, userId int64) error {

	request := struct {
		Method string `json:"method"`
		UserId int64  `json:"user_id"`
	}{
		Method: "sender.closeConnectionsByUserId",
		UserId: userId,
	}
	request.Method = strings.ToLower(request.Method)

	err := doCallSender(nodeId, request, nil)

	return err
}

// получаем список онлайн юзеров
func doCallGetOnlineUsers(nodeId int64, userList []int64) ([]int64, []int64) {

	request := struct {
		Method   string  `json:"method"`
		UserList []int64 `json:"user_list"`
	}{
		Method:   "sender.getOnlineUsers",
		UserList: userList,
	}
	request.Method = strings.ToLower(request.Method)

	response := struct {
		Status   string `json:"status"`
		Response struct {
			OnlineUserList  []int64 `json:"online_user_list"`
			OfflineUserList []int64 `json:"offline_user_list"`
		} `json:"response"`
	}{}

	err := doCallSender(nodeId, request, &response)
	if err != nil {
		return []int64{}, []int64{}
	}

	return response.Response.OnlineUserList, response.Response.OfflineUserList
}

type doCallSetTokenResponseStruct struct {
	Status   string      `json:"status"`
	Response interface{} `json:"response"`
}

// метод для установки токена пользователю
func doCallSetToken(nodeId int64, token string, userId int64, expire int64, platform string, deviceId string) (response doCallSetTokenResponseStruct, err error) {

	request := struct {
		Method   string `json:"method"`
		Token    string `json:"token"`
		UserId   int64  `json:"user_id"`
		Expire   int64  `json:"expire"`
		Platform string `json:"platform"`
		DeviceId string `json:"device_id"`
	}{
		Method:   "sender.setToken",
		Token:    token,
		UserId:   userId,
		Expire:   expire,
		Platform: platform,
		DeviceId: deviceId,
	}
	request.Method = strings.ToLower(request.Method)

	err = doCallSender(nodeId, request, &response)

	return response, err
}

// SendGetOnlineUserListRequestResponseStruct структура ответа запроса на ноду для получения списка онлайн пользователей
type SendGetOnlineUserListRequestResponseStruct struct {
	Status   string `json:"status"`
	Response struct {
		UserList []UserOnlineDevicesStruct `json:"user_list"`
		HasNext  bool                      `json:"has_next"`
	} `json:"response"`
}

// UserOnlineDevicesStruct field user_list in response sender.getOnlineUserList
type UserOnlineDevicesStruct struct {
	UserId   int64  `json:"user_id"`
	Platform string `json:"platform"`
	DeviceId string `json:"device_id"`
}

// метод для получения списка онлайн пользователей ноды
func doCallGetOnlineUserList(nodeId int64, uuid string, limit int, offset int) (response SendGetOnlineUserListRequestResponseStruct, err error) {

	request := struct {
		Method string `json:"method"`
		Uuid   string `json:"uuid"`
		Limit  int    `json:"limit"`
		Offset int    `json:"offset"`
	}{
		Method: "sender.getOnlineUserList",
		Uuid:   uuid,
		Limit:  limit,
		Offset: offset,
	}
	request.Method = strings.ToLower(request.Method)

	err = doCallSender(nodeId, request, &response)
	if err != nil {
		return SendGetOnlineUserListRequestResponseStruct{}, err
	}

	return response, nil
}

// структура запроса на отправку события
type publishRequestStruct struct {
	Method           string                  `json:"method"`
	UserList         []structures.UserStruct `json:"user_list"`
	Event            string                  `json:"event"`
	EventVersionList interface{}             `json:"event_version_list"`
	PushData         interface{}             `json:"push_data,omitempty"`
	WSUsers          interface{}             `json:"ws_users,omitempty"`
	Uuid             string                  `json:"uuid"`
	RoutineKey       string                  `json:"routine_key"`
	Channel          string                  `json:"channel"`
}

// метод для отправки события на ноду go_sender
// @long
func doCallPublish(nodeId int64, userList []int64, event string, eventVersionList interface{}, wsUsers interface{}, uuid string, routineKey string, channel string) []int64 {

	var userEventList []structures.UserStruct

	for _, userId := range userList {
		userEventList = append(userEventList, structures.UserStruct{UserId: userId})
	}

	request := publishRequestStruct{
		Method:           "sender.sendEvent",
		UserList:         userEventList,
		Event:            event,
		EventVersionList: eventVersionList,
		WSUsers:          wsUsers,
		Uuid:             uuid,
		RoutineKey:       routineKey,
		Channel:          channel,
	}

	response := struct {
		Status   string `json:"status"`
		Response struct {
			SentUserList []int64 `json:"sent_user_list"`
		} `json:"response"`
	}{}
	err := doCallSender(nodeId, request, &response)
	if err != nil {

		log.Errorf("%v", err)
		return []int64{}
	}

	return response.Response.SentUserList
}

// структура запроса на отправку события создания конференции Jitsi
type JitsiConferenceCreatedRequestStruct struct {
	Method           string      `json:"method"`
	UserId           int64       `json:"user_id"`
	Event            string      `json:"event"`
	EventVersionList interface{} `json:"event_version_list"`
	PushData         interface{} `json:"push_data,omitempty"`
	WSUsers          interface{} `json:"ws_users,omitempty"`
	Uuid             string      `json:"uuid"`
	TimeToLive       int64       `json:"time_to_live"`
	RoutineKey       string      `json:"routine_key"`
	Channel          string      `json:"channel"`
}

// структура запроса на отправку voip-пуша Jitsi
type SendJitsiVoIPPushRequestStruct struct {
	Method     string      `json:"method"`
	UserId     int64       `json:"user_id"`
	PushData   interface{} `json:"push_data,omitempty"`
	Uuid       string      `json:"uuid"`
	RoutineKey string      `json:"routine_key"`
}

// метод для отправки события создания конференции Jitsi на ноду go_sender
// @long
func doCallJitsiConferenceCreated(nodeId int64, userId int64, event string, eventVersionList interface{}, pushData interface{}, wsUsers interface{}, uuid string, timeToLive int64, routineKey string, channel string) {

	request := JitsiConferenceCreatedRequestStruct{
		Method:           "sender.sendJitsiConferenceCreatedEvent",
		UserId:           userId,
		Event:            event,
		EventVersionList: eventVersionList,
		PushData:         pushData,
		WSUsers:          wsUsers,
		Uuid:             uuid,
		TimeToLive:       timeToLive,
		RoutineKey:       routineKey,
		Channel:          channel,
	}

	log.Errorf("отправили запрос в go_sender %v. pushData %v", request, pushData)

	err := doCallSender(nodeId, request, struct{}{})
	if err != nil {
		log.Errorf("ошибка при отправке ивента создания конференции Jitsi в go_sender. Error: %v", err)
	}
}

// метод для отправки события создания конференции Jitsi на ноду go_sender
// @long
func doCallSendJitsiVoIPPush(nodeId int64, userId int64, pushData interface{}, uuid string, routineKey string) {

	request := SendJitsiVoIPPushRequestStruct{
		Method:     "sender.sendJitsiVoipPush",
		UserId:     userId,
		PushData:   pushData,
		Uuid:       uuid,
		RoutineKey: routineKey,
	}

	log.Errorf("отправили запрос в go_sender %v. pushData %v", request, pushData)

	err := doCallSender(nodeId, request, struct{}{})
	if err != nil {
		log.Errorf("ошибка при отправке ивента для voip-пуша Jitsi в go_sender. Error: %v", err)
	}
}
