package handlerHttp

import (
	"encoding/json"
	"go_pusher/api/includes/type/push/textpush/queue"
	"go_pusher/api/includes/type/push/voippush/queue"
)

// -------------------------------------------------------
// контроллер предназанченный для вызова системных функций
// -------------------------------------------------------

type pusherHandler struct{}

// поддерживаемые методы
var pusherMethods = methodMap{
	"sendPush":     pusherHandler{}.sendPush,
	"sendVoipPush": pusherHandler{}.SendVoipPush,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// отправить пуш
func (pusherHandler) sendPush(requestBytes []byte, userId int64, companyId int) []byte {

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
func (pusherHandler) SendVoipPush(requestBytes []byte, userId int64, companyId int) []byte {

	request := voipPushQueue.VoipPushStruct{}

	err := json.Unmarshal(requestBytes, &request)

	if err != nil {
		return Error(105, "bad json in request")
	}

	voipPushQueue.AddTask(request)

	return Ok()
}
