package handlerTcp

import (
	"encoding/json"
	"go_pivot_cache/api/includes/controller/user"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// контроллер предназанченный для вызова функций для кеша пользователей
// -------------------------------------------------------

type userController struct{}

// поддерживаемые методы
var userMethods = methodMap{
	"getinfo": userController{}.GetInfo,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// формат запроса
type userGetInfoRequestStruct struct {
	UserId int64 `json:"user_id"`
}

// получаем информацию по пользователю
func (userController) GetInfo(requestBytes []byte) ResponseStruct {

	request := userGetInfoRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	userInfo, err := user.GetInfo(request.UserId)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(userInfo)
}
