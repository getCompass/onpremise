package handlerTcp

// даже когда контроллер ничего не возвращает - назначаем p.response на пустой map[string]string

import (
	"context"
	"encoding/json"
	"go_pusher/api/includes/controller/pusher"
	"go_pusher/api/includes/type/device"
	"go_pusher/api/includes/type/push"
	"go_pusher/api/includes/type/usernotification"
	"time"
)

// -------------------------------------------------------
// контроллер предназанченный для работы с пушами
// -------------------------------------------------------

type pusherController struct{}

// поддерживаемые методы
var pusherMethods = methodMap{
	"sendPush":      pusherController{}.SendPush,
	"sendPivotPush": pusherController{}.SendPivotPush,
	"sendVoipPush":  pusherController{}.SendVoipPush,
	"updateBadge":   pusherController{}.UpdateBadge,
}

// таймаут реквеста
const requestTimeout = 5 * time.Second

// на FIREBASE отправляем Push-уведомления сразу пачкой по тысяче токенов за запрос
// на APNS отправляем по одному запросу на каждый токен
// отправляем пуш уведомление ряду пользователей
// * badge значение при этом не обновляется
func (pusherController) SendPush(requestBytes []byte) ResponseStruct {

	// получаем объект запроса из пула и парсим запрос
	request := struct {
		Uuid                 string                                    `json:"uuid"`
		UserNotificationList []usernotification.UserNotificationStruct `json:"user_notification_list"`
		PushData             push.PushDataStruct                       `json:"push_data"`
	}{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	ctx, cancel := context.WithTimeout(context.Background(), requestTimeout)
	defer cancel()

	pusher.SendPush(ctx, request.Uuid, request.UserNotificationList, request.PushData)

	return Ok()
}

// на FIREBASE отправляем Push-уведомления сразу пачкой по тысяче токенов за запрос
// на APNS отправляем по одному запросу на каждый токен
// отправляем пуш уведомление ряду пользователей по userId из pivot
// * badge значение при этом не обновляется
func (pusherController) SendPivotPush(requestBytes []byte) ResponseStruct {

	// получаем объект запроса из пула и парсим запрос
	request := struct {
		Uuid       string              `json:"uuid"`
		UserIdList []int64             `json:"user_id_list"`
		PushData   push.PushDataStruct `json:"push_data"`
	}{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	ctx, cancel := context.WithTimeout(context.Background(), requestTimeout)
	defer cancel()

	pusher.SendPivotPush(ctx, request.Uuid, request.UserIdList, request.PushData)

	return Ok()
}

// отправляем voip analyticspush на firebase & apns
func (pusherController) SendVoipPush(requestBytes []byte) ResponseStruct {

	request := struct {
		UserNotification usernotification.UserNotificationStruct `json:"user_notification"`
		Uuid             string                                  `json:"uuid"`
		PushData         push.PushDataStruct                     `json:"push_data"`
		SentDeviceList   []string                                `json:"sent_device_list,omitempty"`
	}{}
	err := json.Unmarshal(requestBytes, &request)

	if err != nil {
		return Error(105, "bad json in request")
	}

	ctx, cancel := context.WithTimeout(context.Background(), requestTimeout)
	defer cancel()

	pusher.SendVoipPush(ctx, request.UserNotification, request.Uuid, request.PushData, request.SentDeviceList)

	return Ok()
}

// обновляет badge
func (pusherController) UpdateBadge(requestBytes []byte) ResponseStruct {

	request := struct {
		Device              device.DeviceStruct `json:"device"`
		BadgeCount          int64               `json:"badge_count"`
		ConversationKeyList []string            `json:"conversation_key_list"`
		ThreadKeyList       []string            `json:"thread_key_list"`
	}{}
	err := json.Unmarshal(requestBytes, &request)

	if err != nil {
		return Error(105, "bad json in request")
	}

	// продолжаем выполнение запроса асинхронно
	go func() {

		pusher.UpdateBadge(request.Device, request.BadgeCount, request.ConversationKeyList, request.ThreadKeyList)
	}()

	return Ok()
}
