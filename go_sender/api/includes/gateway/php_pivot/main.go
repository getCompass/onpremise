package gatewayPhpPivot

import (
	"crypto/md5"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/conf"
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

// SendJitsiVoipPush отправляем voip пуш для Jitsi
func SendJitsiVoipPush(userId int64, pushData interface{}, uuid string, timeToLive int64, sentDeviceList []string) {

	// создаем объект для отправки VoIP уведомления пользователю
	sendVoIPPushObj := structures.SendJitsiVoIPRequestStruct{
		UserId:         userId,
		Uuid:           uuid,
		PushData:       pushData,
		TimeToLive:     timeToLive,
		SentDeviceList: sentDeviceList,
	}

	jsonParams, err := json.Marshal(sendVoIPPushObj)
	if err != nil {

		log.Error("Не смогли сформировать JSON для запроса на отправку пуша")
		return
	}

	response, err := socket.DoCall("go_pusher", "pusher.SendJitsiVoipPush", "", string(jsonParams), 0, 0)
	if err != nil || response.Status != "ok" {
		log.Errorf("Не смогли выполнить запрос %v", err)
	}
}

// ValidateUserSession проверяет пользовательскую сессию через PHP API.
func ValidateUserSession(userID int64, pivotSession string) (string, error) {

	// подготовка параметров для запроса
	request := map[string]interface{}{
		"method":        "user.validateSession",
		"pivot_session": pivotSession,
	}

	// кодируем сообщение в JSON
	data, err := json.Marshal(request)
	if err != nil {

		log.Errorf("Ошибка кодирования JSON: %v", err)
		return "", err
	}

	moduleUrl, signature := preparePivotParams(data)

	// выполняем HTTP-запрос
	responseBytes, err := socket.DoCallPivot(moduleUrl, request["method"].(string), data, signature, userID)
	if err != nil {

		log.Errorf("Ошибка вызова PHP API: %v", err)
		return "", err
	}

	// декодируем ответ
	var response struct {
		Status   string `json:"status"`
		Response struct {
			SessionUniq string `json:"session_uniq"`
		} `json:"response"`
		ErrorMessage string `json:"error_message,omitempty"`
	}
	err = json.Unmarshal(responseBytes, &response)
	if err != nil {

		log.Errorf("Ошибка декодирования ответа JSON: %v", err)
		return "", err
	}

	// проверка статуса ответа
	if response.Status != "ok" {

		log.Errorf("Сервер вернул ошибку: %s", response.ErrorMessage)
		return "", fmt.Errorf("сервер вернул ошибку: %s", response.ErrorMessage)
	}

	return response.Response.SessionUniq, nil
}

// подготовить параметры для запроса
func preparePivotParams(data json.RawMessage) (string, string) {

	// получаем урл модуля в который уйдет событие
	configSocket := conf.GetSocketConfig()
	moduleUrl := configSocket.SocketUrl["php_pivot"] + configSocket.SocketModule["php_pivot"]

	config, _ := conf.GetConfig()
	socketKeyMe := config.SocketKeyMe

	// получаем подпись модуля
	signature := GetPivotSignature(socketKeyMe, data)

	return moduleUrl, signature
}

// сгенерить сигнатуру для запроса
func GetPivotSignature(privateKey string, jsonParams json.RawMessage) string {

	data := []byte(privateKey + string(jsonParams))
	hash := md5.Sum(data) // nosemgrep

	return hex.EncodeToString(hash[:])
}
