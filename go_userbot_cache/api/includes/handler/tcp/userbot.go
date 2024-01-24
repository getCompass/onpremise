package handlerTcp

import (
	"encoding/json"
	"go_userbot_cache/api/includes/controller/userbot"
)

// -------------------------------------------------------
// контроллер предназначенный для вызова функций для ботов
// -------------------------------------------------------

type userbotController struct{}

// поддерживаемые методы
var userbotMethods = methodMap{
	"getone": userbotController{}.GetOne,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// формат запроса
type userbotGetOneRequestStruct struct {
	Token string `json:"token"`
}

// получаем информацию по боту
func (userbotController) GetOne(requestBytes []byte) ResponseStruct {

	request := userbotGetOneRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	userbotItem, _ := userbot.GetOne(request.Token)

	return Ok(struct {
		Token            string `json:"token"`
		UserbotId        string `json:"userbot_id"`
		Status           int64  `json:"status"`
		CompanyId        int64  `json:"company_id"`
		DominoEntrypoint string `json:"domino_entrypoint"`
		SecretKey        string `json:"secret_key"`
	}{
		Token:            userbotItem.Token,
		UserbotId:        userbotItem.UserbotId,
		Status:           userbotItem.Status,
		CompanyId:        userbotItem.CompanyId,
		DominoEntrypoint: userbotItem.DominoEntrypoint,
		SecretKey:        userbotItem.SecretKey,
	})
}
