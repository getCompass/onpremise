package controller

import (
	"encoding/json"
	"go_sender/api/includes/methods/sender"
	"go_sender/api/includes/type/push"
	"go_sender/api/includes/type/request"
	"go_sender/api/includes/type/sender"
	"go_sender/api/includes/type/structures"
	"go_sender/api/includes/type/ws"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// контроллер предназанченный для взаимодействия с go_sender, а так же отправкой задач в go_pusher
// -------------------------------------------------------

type senderController struct{}

// поддерживаемые методы
var senderMethods = methodMap{
	"setToken":                        senderController{}.SetToken,
	"sendEvent":                       senderController{}.SendEvent,
	"sendEventToAll":                  senderController{}.SendEventToAll,
	"getOnlineConnectionsByUserId":    senderController{}.GetOnlineConnectionsByUserId,
	"closeConnectionsByUserId":        senderController{}.CloseConnectionsByUserId,
	"addUsersToThread":                senderController{}.AddUsersToThread,
	"getOnlineUsers":                  senderController{}.GetOnlineUsers,
	"getOnlineUserList":               senderController{}.GetOnlineUserList,
	"sendVoIP":                        senderController{}.SendVoIP,
	"addTaskPushNotification":         senderController{}.AddTaskPushNotification,
	"sendTypingEvent":                 senderController{}.SendTypingEvent,
	"sendThreadTypingEvent":           senderController{}.SendThreadTypingEvent,
	"sendIncomingCall":                senderController{}.SendIncomingCall,
	"sendJitsiConferenceCreatedEvent": senderController{}.SendJitsiConferenceCreatedEvent,
	"sendJitsiVoipPush":               senderController{}.SendJitsiVoipPush,
	"clearUserNotificationCache":      senderController{}.ClearUserNotificationCache,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// метод для установки пользовательского токена на go_sender для авторизации подключения
// принимает параметры: user_id int64, token string, expire	int64
// обращается к ноде go_sender, чтобы тот сохранил токен к себе
func (senderController) SetToken(data *request.Data) ResponseStruct {

	senderRequest := struct {
		Token    string `json:"token"`
		Platform string `json:"platform"`
		DeviceId string `json:"device_id"`
		UserId   int64  `json:"user_id"`
		Expire   int64  `json:"expire"`
	}{}

	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = talking.SetToken(data.Isolation, senderRequest.Token, senderRequest.Platform, senderRequest.DeviceId, senderRequest.UserId, senderRequest.Expire)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(struct {
		Node int64 `json:"node"`
	}{Node: 1})
}

// метод для отправки WS событий на активные подключения пользователей
// в случае, если у пользователя нет активных подключений
// отправляет push уведомление, если это необходимо
func (senderController) SendEvent(data *request.Data) ResponseStruct {

	senderRequest := structures.SendEventRequestStruct{}
	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	if len(senderRequest.Event) < 1 {
		return Error(409, "bad event in request")
	}

	// конвертируем полученные версии события в более удобный формат
	eventVersionList := sender.ConvertEventVersionList(senderRequest.EventVersionList)

	talking.SendEvent(data.Isolation, senderRequest.UserList, senderRequest.Event, eventVersionList, senderRequest.PushData, senderRequest.WSUsers,
		senderRequest.Uuid, senderRequest.RoutineKey, senderRequest.Channel)

	return Ok()
}

// метод для отправки WS событий для всех пользователей на активные подключения
// в случае, если у пользователя нет активных подключений
// отправляет push уведомление, если это необходимо
func (senderController) SendEventToAll(data *request.Data) ResponseStruct {

	senderRequest := structures.SendEventToAllRequestStruct{}
	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	if len(senderRequest.Event) < 1 {
		return Error(409, "bad event in request")
	}

	// конвертируем полученные версии события в более удобный формат
	eventVersionList := sender.ConvertEventVersionList(senderRequest.EventVersionList)

	talking.SendEventToAll(data.Isolation, senderRequest.Event, eventVersionList, senderRequest.PushData, senderRequest.WSUsers,
		senderRequest.Uuid, senderRequest.RoutineKey, senderRequest.IsNeedPush, senderRequest.Channel)

	return Ok()
}

// метод для получения списка ws соединений по идентификатору пользователя (userId)
func (senderController) GetOnlineConnectionsByUserId(data *request.Data) ResponseStruct {

	senderRequest := struct {
		UserId int64 `json:"user_id"`
	}{}

	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	localUserConnectionList, err := talking.GetOnlineConnectionsByUserId(data.Isolation, senderRequest.UserId)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(struct {
		OnlineConnectionList []sender.ConnectionInfoStruct `json:"online_connection_list"`
	}{OnlineConnectionList: localUserConnectionList})
}

// метод для закрытия подключений по идентификатору пользователя (userId)
func (senderController) CloseConnectionsByUserId(data *request.Data) ResponseStruct {

	senderRequest := struct {
		UserId int64 `json:"user_id"`
	}{}

	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = talking.CloseConnectionsByUserId(data.Isolation, senderRequest.UserId)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// метод для закрытия подключений по идентификатору пользователя (userId)
func (senderController) CloseConnectionsByUserIdWithWait(data *request.Data) ResponseStruct {

	senderRequest := struct {
		UserId     int64  `json:"user_id"`
		RoutineKey string `json:"routine_key"`
	}{}

	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = talking.CloseConnectionsByUserIdWithWait(data.Isolation, senderRequest.UserId, senderRequest.RoutineKey)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// метод для добавления задачи на отправку пушей пользователям в RabbitMq очередь микросервиса go_pusher
func (senderController) AddTaskPushNotification(data *request.Data) ResponseStruct {

	senderRequest := struct {
		UserList []int64             `json:"user_list"`
		PushData push.PushDataStruct `json:"push_data"`
		Uuid     string              `json:"uuid"`
	}{}
	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.AddTaskPushNotification(data.Isolation, senderRequest.UserList, senderRequest.PushData, senderRequest.Uuid)

	return Ok()
}

// метод для запроса онлайна ряда пользователей
func (senderController) GetOnlineUsers(data *request.Data) ResponseStruct {

	senderRequest := struct {
		UserList []int64 `json:"user_list"`
	}{}

	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	onlineUserList, offlineUserList := talking.GetOnlineUsers(data.Isolation, senderRequest.UserList)

	return Ok(struct {
		OnlineUserList  []int64 `json:"online_user_list"`
		OfflineUserList []int64 `json:"offline_user_list"`
	}{onlineUserList, offlineUserList})
}

// request sender.getOnlineUserList
type senderGetOnlineUserListRequest struct {
	UUID   string `json:"uuid"`
	Limit  int    `json:"limit"`
	Offset int    `json:"offset"`
}

// получаем лист с онлайн устройствами пользователей
func (senderController) GetOnlineUserList(data *request.Data) ResponseStruct {

	// парсим запрос
	senderRequest := senderGetOnlineUserListRequest{}
	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// инициализируем снапшот (если уже имеется, то инициализация не произойдет)
	ws.InitializeSnapshot(data.Isolation.UserConnectionStore, senderRequest.UUID)

	// получаем часть онлайн списка
	userOnlineDeviceList, hasNext := ws.GetOnlineUserList(senderRequest.UUID, senderRequest.Limit, senderRequest.Offset)

	return Ok(struct {
		UserList []ws.UserOnlineDevice `json:"user_list"`
		HasNext  bool                  `json:"has_next"`
	}{
		UserList: userOnlineDeviceList,
		HasNext:  hasNext,
	})
}

// метод для добавления пользователя к треду
func (senderController) AddUsersToThread(data *request.Data) ResponseStruct {

	senderRequest := struct {
		ThreadKey  string  `json:"thread_key"`
		UserList   []int64 `json:"user_list"`
		RoutineKey string  `json:"routine_key"`
		CompanyId  int64   `json:"company_id"`
		Channel    string  `json:"channel"`
	}{}

	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.AddUsersToThread(data.Isolation, senderRequest.ThreadKey, senderRequest.UserList, senderRequest.RoutineKey, senderRequest.Channel)

	return Ok()
}

// структура typing события, получаемое микросервисом
type sendTypingEvent struct {
	UserList         []int64                                 `json:"user_list,omitempty"`
	Event            string                                  `json:"event"`
	EventVersionList []structures.SendEventVersionItemStruct `json:"event_version_list"`
	RoutineKey       string                                  `json:"routine_key"`
	Channel          string                                  `json:"channel"`
}

// метод для отправки typing события на ноды go_sender_*
func (senderController) SendTypingEvent(data *request.Data) ResponseStruct {

	senderRequest := sendTypingEvent{}

	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// конвертируем полученные версии события в более удобный формат
	eventVersionList := sender.ConvertEventVersionList(senderRequest.EventVersionList)

	talking.SendTypingEvent(data.Isolation, senderRequest.UserList, senderRequest.Event, eventVersionList, senderRequest.RoutineKey, senderRequest.Channel)

	return Ok()
}

// структура typing события, получаемое микросервисом
type sendThreadTypingEvent struct {
	UserList         []int64                                 `json:"user_list,omitempty"`
	Event            string                                  `json:"event"`
	ThreadKey        string                                  `json:"thread_key"`
	EventVersionList []structures.SendEventVersionItemStruct `json:"event_version_list"`
	RoutineKey       string                                  `json:"routine_key"`
	Channel          string                                  `json:"channel"`
}

// метод для отправки thread_typing события
func (senderController) SendThreadTypingEvent(data *request.Data) ResponseStruct {

	senderRequest := sendThreadTypingEvent{}

	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// конвертируем полученные версии события в более удобный формат
	eventVersionList := sender.ConvertEventVersionList(senderRequest.EventVersionList)

	talking.SendThreadTypingEvent(data.Isolation.UserConnectionStore, data.Isolation.ThreadUcStore, senderRequest.Event, senderRequest.ThreadKey, eventVersionList)

	return Ok()
}

// запрос для отправки voip пуша со звонком
func (senderController) SendVoIP(data *request.Data) ResponseStruct {

	senderRequest := struct {
		UserId     int64       `json:"user_id"`
		PushData   interface{} `json:"push_data"`
		Uuid       string      `json:"uuid"`
		TimeToLive int64       `json:"time_to_live,omitempty"`
		RoutineKey string      `json:"routine_key"`
	}{}

	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.SendVoIP(data.Isolation, senderRequest.UserId, senderRequest.PushData, senderRequest.Uuid, senderRequest.TimeToLive, senderRequest.RoutineKey)

	return Ok()
}

// sender.SendIncomingCall request
type sendIncomingCallRequestStruct struct {
	UserId           int64                                   `json:"user_id"`
	EventVersionList []structures.SendEventVersionItemStruct `json:"event_version_list"`
	PushData         interface{}                             `json:"push_data"`
	WSUsers          interface{}                             `json:"ws_users"`
	Uuid             string                                  `json:"uuid"`
	TimeToLive       int64                                   `json:"time_to_live"`
	RoutineKey       string                                  `json:"routine_key"`
	Channel          string                                  `json:"channel"`
}

// запрос для отправки ws-события и voip-пуша о входящем звонке
func (senderController) SendIncomingCall(data *request.Data) ResponseStruct {

	senderRequest := sendIncomingCallRequestStruct{}
	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	if senderRequest.UserId < 1 {
		return Error(401, "passed bad user_id")
	}

	// конвертируем полученные версии события в более удобный формат
	eventVersionList := sender.ConvertEventVersionList(senderRequest.EventVersionList)

	talking.SendIncomingCall(data.Isolation, senderRequest.UserId, eventVersionList, senderRequest.PushData, senderRequest.WSUsers,
		senderRequest.Uuid, senderRequest.TimeToLive, senderRequest.RoutineKey, senderRequest.Channel)

	return Ok()
}

// sender.ClearUserNotificationCache request
type clearUserNotificationCacheRequestStruct struct {
	UserId int64 `json:"user_id"`
}

// запрос для очистки кэша с настройками уведомления пользователя
func (senderController) ClearUserNotificationCache(data *request.Data) ResponseStruct {

	senderRequest := clearUserNotificationCacheRequestStruct{}
	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	if senderRequest.UserId < 1 {
		return Error(401, "passed bad user_id")
	}

	err = talking.ClearUserNotificationCache(data.Isolation, senderRequest.UserId)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// запрос для отправки ws-события и voip-пуша о создании конференции Jitsi
func (senderController) SendJitsiConferenceCreatedEvent(data *request.Data) ResponseStruct {

	senderRequest := structures.SendJitsiConferenceCreatedEventRequestStruct{}
	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	if len(senderRequest.Event) < 1 {
		return Error(409, "bad event in request")
	}

	// конвертируем полученные версии события в более удобный формат
	eventVersionList := sender.ConvertEventVersionList(senderRequest.EventVersionList)

	talking.SendJitsiConferenceCreated(data.Isolation, senderRequest.UserId, senderRequest.Event, eventVersionList, senderRequest.PushData, senderRequest.WSUsers,
		senderRequest.Uuid, senderRequest.TimeToLive, senderRequest.RoutineKey, senderRequest.Channel)

	return Ok()
}

// запрос для отправки voip-пуша Jitsi
func (senderController) SendJitsiVoipPush(data *request.Data) ResponseStruct {

	senderRequest := structures.SendJitsiVoipPushRequestStruct{}
	err := json.Unmarshal(data.RequestData, &senderRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.SendJitsiVoipPush(data.Isolation, senderRequest.UserId, senderRequest.PushData, senderRequest.Uuid, senderRequest.RoutineKey)

	return Ok()
}
