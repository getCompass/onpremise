package socket

import (
	"crypto/md5"
	"encoding/hex"
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pusher/api/conf"
)

// структура ответа user.getInfo
type GetUserInfoResponseStruct struct {
	Status   string `json:"status"`
	Response struct {
		FullName      string `json:"full_name"`
		AvatarFileKey string `json:"avatar_file_key"`
		AvatarColor   string `json:"avatar_color"`
	} `json:"response"`
}

// получить информацию о пользователе
func GetUserInfo(userId int64) (GetUserInfoResponseStruct, error) {

	// готовим сообщение
	request := getInfoParams{
		Method: "user.getInfoForVoip",
		UserId: userId,
	}

	// кодируем сообщение
	data, err := json.Marshal(request)
	if err != nil {
		return GetUserInfoResponseStruct{}, err
	}

	moduleUrl, signature := preparePivotParams(data)

	// делаем запрос в php модуль
	responseBytes, err := DoCall(moduleUrl, request.Method, data, signature, 0)
	if err != nil {
		return GetUserInfoResponseStruct{}, err
	}

	response := GetUserInfoResponseStruct{}
	err = json.Unmarshal(responseBytes, &response)
	if err != nil {
		return GetUserInfoResponseStruct{}, err
	}
	return response, nil
}

// внутренний тип — запакованные данные события
type GetGetCompanySocketKeyParams struct {
	Method    string `json:"method"`
	CompanyId int    `json:"company_id"`
}

// структура ответа user.getInfo
type GetGetCompanySocketKeyResponseStruct struct {
	Status   string `json:"status"`
	Response struct {
		SocketKey string `json:"socket_key"`
	} `json:"response"`
}

// получить информацию о компании
func GetGetCompanySocketKey(companyId int) (GetGetCompanySocketKeyResponseStruct, error) {

	// готовим сообщение
	request := GetGetCompanySocketKeyParams{
		Method:    "system.getCompanySocketKey",
		CompanyId: companyId,
	}

	// кодируем сообщение
	data, err := json.Marshal(request)
	if err != nil {
		log.Errorf("error socket request: %s", err)
	}

	moduleUrl, signature := preparePivotParams(data)

	// делаем запрос в php модуль
	responseBytes, err := DoCall(moduleUrl, request.Method, data, signature, 0)
	if err != nil {
		return GetGetCompanySocketKeyResponseStruct{}, err
	}
	response := GetGetCompanySocketKeyResponseStruct{}
	err = json.Unmarshal(responseBytes, &response)
	if err != nil {
		return GetGetCompanySocketKeyResponseStruct{}, err
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
