package socket

import (
	"encoding/json"
	"go_pusher/api/conf"
)

// GetServerInfoResponseStruct структура ответа premise.GetServerInfoResponseStruct
type GetServerInfoResponseStruct struct {
	Status   string `json:"status"`
	Response struct {
		Domain    string `json:"domain"`
		ServerUid string `json:"server_uid"`
		SecretKey string `json:"secret_key"`
	} `json:"response"`
}

// GetServerInfo получить информацию о пользователе
func GetServerInfo() (*GetServerInfoResponseStruct, error) {

	// кодируем сообщение
	data := json.RawMessage{}
	moduleUrl, signature := preparePremiseParams(data)

	// делаем запрос в php модуль
	responseBytes, err := DoCall(moduleUrl, "premise.getServerInfo", data, signature, 0)
	if err != nil {
		return nil, err
	}

	response := GetServerInfoResponseStruct{}
	if err = json.Unmarshal(responseBytes, &response); err != nil {
		return nil, err
	}

	return &response, nil
}

// подготовить параметры для запроса
func preparePremiseParams(data json.RawMessage) (string, string) {

	// получаем урл модуля в который уйдет событие
	config := conf.GetSocketConfig()
	moduleUrl := config.SocketUrl["php_premise"] + config.SocketModule["php_premise"]

	// получаем подпись модуля
	signature := GetPivotSignature(conf.GetConfig().SocketKeyMe, data)
	return moduleUrl, signature
}
