package socket

import (
	"encoding/json"
	"go_pusher/api/conf"
	"go_pusher/api/includes/type/push"
)

type InvalidTokensRequest struct {
	ServerUid string `json:"server_uid"`
}

type InvalidTokensResponse struct {
	InvalidTokens []invalidToken `json:"invalid_tokens"`
}

type invalidToken struct {
	Token  string `json:"token"`
	Device string `json:"device"`
}

// отправить пуш
func SendPush(pushTask push.PushTaskStruct) error {

	method := "push.send"

	// кодируем сообщение
	data, err := json.Marshal(pushTask)
	if err != nil {
		return err
	}

	moduleUrl := preparePushParams()

	// делаем запрос в php модуль
	_, err = DoCallWithValidationKey(moduleUrl, method, data, pushTask.ServerUid, "")
	if err != nil {
		return err
	}

	return nil
}

// получить информацию о инвалидных токенах
func GetInvalidTokens(serverUid string) (InvalidTokensResponse, error) {

	method := "push.getInvalidTokens"

	invalidTokenResponse := InvalidTokensResponse{}

	// кодируем сообщение
	data := json.RawMessage{}

	moduleUrl := preparePushParams()

	// делаем запрос в php модуль
	response, err := DoCallWithValidationKey(moduleUrl, method, data, serverUid, "")
	if err != nil {
		return invalidTokenResponse, err
	}

	err = json.Unmarshal(response, &invalidTokenResponse)

	if err != nil {
		return invalidTokenResponse, err
	}

	return invalidTokenResponse, nil
}

// подготовить параметры для запроса
func preparePushParams() string {

	// получаем урл модуля в который уйдет событие
	config := conf.GetSocketConfig()
	moduleUrl := config.SocketUrl["go_push_sender"] + config.SocketModule["go_push_sender"]

	return moduleUrl

}
