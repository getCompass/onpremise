package handlerHttp

import (
	"encoding/json"
	"go_userbot_cache/api/includes/controller/userbot"
)

// -------------------------------------------------------
// контроллер предназанченный для вызова функций бота
// -------------------------------------------------------

type userbotHandler struct{}

// поддерживаемые методы
var userbotMethods = methodMap{
	"clear": userbotHandler{}.clear,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// формат запроса
type userbotClearRequestStruct struct {
	Token string `json:"token"`
}

// очистить бота из кэша
func (userbotHandler) clear(requestBytes []byte) ResponseStruct {

	request := userbotClearRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	userbot.ClearFromCacheByToken(request.Token)

	return Ok()
}
