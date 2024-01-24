package socket

import (
	"crypto/md5"
	"encoding/hex"
	"encoding/json"
	"go_userbot_cache/api/conf"
)

// структура ответа userbot.getInfo
type GetUserbotInfoResponseStruct struct {
	Status   string `json:"status"`
	Response struct {
		UserbotId        string `json:"userbot_id"`
		Status           int    `json:"status"`
		CompanyId        int64  `json:"company_id"`
		DominoEntrypoint string `json:"domino_entrypoint"`
		CompanyUrl       string `json:"company_url"`
		SecretKey        string `json:"secret_key"`
		IsReactCommand   int    `json:"is_react_command"`
		UserbotUserId    int    `json:"userbot_user_id"`
		Extra            string `json:"extra"`
	} `json:"response"`
}

// получить информацию о боте
func GetUserbotInfo(token string) (GetUserbotInfoResponseStruct, error) {

	// готовим сообщение
	request := getInfoParams{
		Method: "userbot.getInfo",
		Token:  token,
	}

	// кодируем сообщение
	data, err := json.Marshal(request)
	if err != nil {
		return GetUserbotInfoResponseStruct{}, err
	}

	moduleUrl, signature := preparePivotParams(data)

	// делаем запрос в php модуль
	responseBytes, err := DoCall(moduleUrl, request.Method, data, signature, 0)
	if err != nil {
		return GetUserbotInfoResponseStruct{}, err
	}

	response := GetUserbotInfoResponseStruct{}
	err = json.Unmarshal(responseBytes, &response)
	if err != nil {
		return GetUserbotInfoResponseStruct{}, err
	}
	return response, nil
}

// подготовить параметры для запроса
func preparePivotParams(data json.RawMessage) (string, string) {

	// получаем урл модуля в который уйдет событие
	config := conf.GetSocketConfig()
	moduleUrl := config.SocketUrl["php_pivot"] + config.SocketModule["php_pivot"]

	// получаем подпись модуля
	signature := GetPivotSignature(conf.GetConfig().SocketKeyMe, data)

	return moduleUrl, signature

}

// сгенерить сигнатуру для запроса
func GetPivotSignature(privateKey string, jsonParams json.RawMessage) string {

	data := []byte(privateKey + string(jsonParams))
	hash := md5.Sum(data)

	return hex.EncodeToString(hash[:])
}
