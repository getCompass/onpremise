package handlerHttp

import (
	"encoding/json"
	jitsiVoipPushQueue "go_pusher/api/includes/type/push/jitsivoippush/queue"
	textPushQueue "go_pusher/api/includes/type/push/textpush/queue"
	voipPushQueue "go_pusher/api/includes/type/push/voippush/queue"
)

// -------------------------------------------------------
// контроллер предназанченный для вызова системных функций
// -------------------------------------------------------

type pusherHandler struct{}

// поддерживаемые методы
var pusherMethods = methodMap{
	"sendPush":          pusherHandler{}.sendPush,
	"sendVoipPush":      pusherHandler{}.SendVoipPush,
	"sendJitsiVoipPush": pusherHandler{}.SendJitsiVoipPush,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// отправить пуш
func (pusherHandler) sendPush(requestBytes []byte, userId int64, companyId int) ResponseStruct {

	// получаем объект запроса из пула и парсим запрос
	request := textPushQueue.TextPushStruct{}

	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	textPushQueue.AddTask(request)

	return Ok()
}

// отправляем voip analyticspush на firebase & apns
func (pusherHandler) SendVoipPush(requestBytes []byte, userId int64, companyId int) ResponseStruct {

	request := voipPushQueue.VoipPushStruct{}

	err := json.Unmarshal(requestBytes, &request)

	if err != nil {
		return Error(105, "bad json in request")
	}

	voipPushQueue.AddTask(request)

	return Ok()
}

// отправляем пользователю voip-пуш для jitsi из pivot
func (pusherHandler) SendJitsiVoipPush(requestBytes []byte, userId int64, companyId int) ResponseStruct {

	request := jitsiVoipPushQueue.VoIPJitsiStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	jitsiVoipPushQueue.AddTask(request)

	return Ok()
}
