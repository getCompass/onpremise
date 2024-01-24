package gatewayPhpPivot

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/push"
	"go_sender/api/includes/type/socket"
	socketAuthKey "go_sender/api/includes/type/socket/auth"
	"go_sender/api/includes/type/structures"
	"go_sender/api/includes/type/user_notification"
)

// SendPush отправляем пуш
func SendPush(userNotificationStructList []user_notification.UserNotificationStruct, pushData push.PushDataStruct, uuid string, companyId int64, pivotSocketKey string, NeedForcePush int) {

	// создаем объект для отправки Push Notification пользователю
	pushNotificationObject := structures.SendPushRequestStruct{
		UserNotificationList: userNotificationStructList,
		PushData:             pushData,
		Uuid:                 uuid,
		NeedForcePush:        NeedForcePush,
	}

	jsonParams, err := json.Marshal(pushNotificationObject)

	signature, err := socketAuthKey.GetPivotSignature(pivotSocketKey, jsonParams)
	if err != nil {

		log.Errorf("Не смогли получить сигнатуру for company %v для запроса %v", companyId, err)
		return
	}

	response, err := socket.DoCall("go_pusher", "pusher.sendPush", signature, string(jsonParams), 0, companyId)
	if err != nil || response.Status != "ok" {
		log.Errorf("Не смогли выполнить запрос %v", err)
	}
}

// SendVoipPush отправляем voip пуш
func SendVoipPush(userNotificationStruct user_notification.UserNotificationStruct, pushData interface{}, uuid string, timeToLive int64, sentDeviceList []string, companyId int64, pivotSocketKey string) {

	// создаем объект для отправки VoIP уведомления пользователю
	sendVoIPPushObj := structures.SendVoIPRequestStruct{
		UserNotification: userNotificationStruct,
		Uuid:             uuid,
		PushData:         pushData,
		TimeToLive:       timeToLive,
		SentDeviceList:   sentDeviceList,
	}

	jsonParams, err := json.Marshal(sendVoIPPushObj)
	if err != nil {

		log.Error("Не смогли сформировать JSON для запроса на отправку пуша")
		return
	}

	signature, err := socketAuthKey.GetPivotSignature(pivotSocketKey, jsonParams)
	if err != nil {

		log.Errorf("Не смогли получить сигнатуру for company %v для запроса %v", companyId, err)
		return
	}

	response, err := socket.DoCall("go_pusher", "pusher.sendVoipPush", signature, string(jsonParams), 0, companyId)
	if err != nil || response.Status != "ok" {
		log.Errorf("Не смогли выполнить запрос %v", err)
	}
}
