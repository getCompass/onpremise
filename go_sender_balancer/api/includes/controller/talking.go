package controller

import (
	"encoding/json"
	"go_sender_balancer/api/includes/gateway/tcp"
	"go_sender_balancer/api/includes/methods/talking"
	"go_sender_balancer/api/includes/type/structures"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// контроллер предназанченный для взаимодействия с
// нодами go_sender, а так же отправкой задач в go_pusher
// -------------------------------------------------------

type talkingController struct{}

// поддерживаемые методы
var talkingMethods = methodMap{
	"setToken":                             talkingController{}.SetToken,
	"sendEvent":                            talkingController{}.SendEvent,
	"sendEventBatching":                    talkingController{}.SendEventBatching,
	"broadcastEvent":                       talkingController{}.BroadcastEvent,
	"getOnlineConnectionsByUserId":         talkingController{}.GetOnlineConnectionsByUserId,
	"closeConnectionsByUserId":             talkingController{}.CloseConnectionsByUserId,
	"getOnlineUsers":                       talkingController{}.GetOnlineUsers,
	"addConnectionToBalancer":              talkingController{}.AddConnectionToBalancer,
	"deleteConnectionFromBalancer":         talkingController{}.DeleteConnectionFromBalancer,
	"deleteAllNodeConnectionsFromBalancer": talkingController{}.DeleteAllNodeConnectionsFromBalancer,
	"jitsiConferenceCreated":               talkingController{}.JitsiConferenceCreated,
	"sendJitsiVoIPPush":                    talkingController{}.SendJitsiVoIPPush,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// метод для установки пользовательского токена на go_sender для авторизации подключения
// принимает параметры: user_id int64, token string, expire	int64
// обращается к ноде go_sender, чтобы тот сохранил токен к себе
func (talkingController) SetToken(requestBytes []byte) ResponseStruct {

	request := struct {
		Token    string `json:"token"`
		Platform string `json:"platform"`
		DeviceId string `json:"device_id"`
		UserId   int64  `json:"user_id"`
		Expire   int64  `json:"expire"`
	}{}

	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	senderNodeId, err := talking.SetToken(request.Token, request.Platform, request.DeviceId, request.UserId, request.Expire)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(struct {
		Node int64 `json:"node"`
	}{Node: senderNodeId})
}

// метод для отправки WS событий на активные подключения пользователей
func (talkingController) SendEvent(requestBytes []byte) ResponseStruct {

	request := structures.SendEventRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.SendEvent(request.UserList, request.Event, request.EventVersionList, request.PushData, request.WSUsers, request.Uuid, request.RoutineKey, request.IsNeedPush)

	return Ok()
}

// метод для отправки WS событий batching-методом на активные подключения пользователей
func (talkingController) SendEventBatching(requestBytes []byte) ResponseStruct {

	request := structures.SendEventBatchingRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	for _, data := range request.BatchingData {
		talking.SendEvent(data.UserList, data.Event, data.EventVersionList, data.PushData, data.WSUsers, data.Uuid, data.RoutineKey, data.IsNeedPush)
	}

	return Ok()
}

// метод для отправки события всем подключенным клиентам
func (talkingController) BroadcastEvent(requestBytes []byte) ResponseStruct {

	request := structures.BroadcastEventRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.BroadcastEvent(request.Event, request.EventVersionList, request.WSUsers, request.Uuid, request.RoutineKey)

	return Ok()
}

// метод для получения списка ws соединений по идентификатору пользователя (userId)
func (talkingController) GetOnlineConnectionsByUserId(requestBytes []byte) ResponseStruct {

	request := struct {
		UserId int64 `json:"user_id"`
	}{}

	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	userConnectionList, err := talking.GetOnlineConnectionsByUserId(request.UserId)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(struct {
		OnlineConnectionList []tcp.ConnectionInfoStruct `json:"online_connection_list"`
	}{OnlineConnectionList: userConnectionList})
}

// метод для закрытия подключений по идентификатору пользователя (userId)
func (talkingController) CloseConnectionsByUserId(requestBytes []byte) ResponseStruct {

	request := struct {
		UserId int64 `json:"user_id"`
	}{}

	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = talking.CloseConnectionsByUserId(request.UserId)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// метод для запроса онлайна ряда пользователей на ноды go_sender_*
func (talkingController) GetOnlineUsers(requestBytes []byte) ResponseStruct {

	request := struct {
		UserList []int64 `json:"user_list"`
	}{}

	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	onlineUserList, offlineUserList := talking.GetOnlineUsers(request.UserList)

	return Ok(struct {
		OnlineUserList  []int64 `json:"online_user_list"`
		OfflineUserList []int64 `json:"offline_user_list"`
	}{onlineUserList, offlineUserList})
}

// метод для записи соединения пользователя
func (talkingController) AddConnectionToBalancer(requestBytes []byte) ResponseStruct {

	request := struct {
		NodeId int64 `json:"node_id"`
		UserId int64 `json:"user_id"`
	}{}

	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.AddConnectionToBalancer(request.NodeId, request.UserId)

	return Ok()
}

// метод для декремента счетчика и удаления соединений пользователя, если необходимо
func (talkingController) DeleteConnectionFromBalancer(requestBytes []byte) ResponseStruct {

	request := struct {
		NodeId                   int64 `json:"node_id"`
		UserId                   int64 `json:"user_id"`
		ClosedAllUserConnections bool  `json:"closed_all_user_connections"`
	}{}

	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.DeleteConnectionFromBalancer(request.NodeId, request.UserId, request.ClosedAllUserConnections)

	return Ok()
}

// метод для декремента счетчика и удаления соединений пользователя, если необходимо
func (talkingController) DeleteAllNodeConnectionsFromBalancer(requestBytes []byte) ResponseStruct {

	request := struct {
		NodeId int64 `json:"node_id"`
	}{}

	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.DeleteAllNodeConnectionsFromBalancer(request.NodeId)

	return Ok()
}

// метод для отправки событий и voip-пуша при создании конференции jitsi
func (talkingController) JitsiConferenceCreated(requestBytes []byte) ResponseStruct {

	request := structures.JitsiConferenceCreatedRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.JitsiConferenceCreated(request.UserId, request.Event, request.EventVersionList, request.PushData, request.WSUsers, request.Uuid, request.TimeToLive, request.RoutineKey)

	return Ok()
}

// метод для отправки voip-пуша jitsi
func (talkingController) SendJitsiVoIPPush(requestBytes []byte) ResponseStruct {

	request := structures.SendJitsiVoIPPushRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	talking.SendJitsiVoIPPush(request.UserId, request.PushData, request.Uuid, request.RoutineKey)

	return Ok()
}
