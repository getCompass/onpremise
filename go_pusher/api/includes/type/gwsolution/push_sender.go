package gwsolution

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pusher/api/includes/type/devicetoken"
	"go_pusher/api/includes/type/premise"
	"go_pusher/api/includes/type/push"
)

// SendPush передает пуш-уведомление в сервис рассылки
func SendPush(pushTask push.PushTaskStruct) error {

	method := "push.send"

	// кодируем сообщение
	data, err := json.Marshal(pushTask)
	if err != nil {
		return err
	}

	requestUrl := resolveUrl("go_push_sender")
	if len(requestUrl) == 0 {
		return nil
	}

	// делаем запрос в php модуль
	_, err = call(requestUrl, method, data)
	return err
}

// GetInvalidTokens получить инвалидные токены
func GetInvalidTokens() []devicetoken.InvalidTokenStruct {

	serverInfo, err := premise.GetCurrent()

	if err != nil {
		log.Error("cant get server info for get invalid tokens")
		return []devicetoken.InvalidTokenStruct{}
	}

	method := "push.getInvalidTokens"
	data, _ := json.Marshal(getInvalidTokensRequestStruct{ServerUid: serverInfo.ServerUid})

	requestUrl := resolveUrl("go_push_sender")
	if len(requestUrl) == 0 {
		return []devicetoken.InvalidTokenStruct{}
	}

	// делаем запрос в микросервис
	response, err := call(requestUrl, method, data)

	if err != nil {

		log.Error("bad response from push.getInvalidTokens")
		return []devicetoken.InvalidTokenStruct{}
	}

	responseStruct := getInvalidTokensResponseStruct{}
	err = json.Unmarshal(response, &responseStruct)
	if err != nil {

		log.Error("cant parse json in response from push.getInvalidTokens")
		return []devicetoken.InvalidTokenStruct{}
	}

	return responseStruct.InvalidTokens
}

// структура запроса
type getInvalidTokensRequestStruct struct {
	ServerUid string `json:server_uid`
}

// структура ответа
type getInvalidTokensResponseStruct struct {
	InvalidTokens []devicetoken.InvalidTokenStruct `json:"invalid_tokens"`
}
