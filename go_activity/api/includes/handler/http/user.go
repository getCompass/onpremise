package handlerHttp

import (
	"encoding/json"
	"go_activity/api/includes/controller/user"
	"go_activity/api/includes/type/request"
	"go_activity/api/includes/type/usercache"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// контроллер предназначенный для вызова функций для кеша активности пользователей
// -------------------------------------------------------

type userController struct{}

// поддерживаемые методы
var userMethods = methodMap{
	"addActivityList": userController{}.AddActivityList,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// userAddActivityListRequestStruct структура запроса для addActivityList
type userAddActivityListRequestStruct struct {
    Users []struct {
        UserId       int64  `json:"user_id"`
        SessionUniq  string `json:"session_uniq"`
        LastPingWsAt int64  `json:"last_ping_ws_at"`
    } `json:"users"`
}

// AddActivityList обработка списка активностей пользователей
func (userController) AddActivityList(requestBytes request.Data) []byte {

	// извлекаем []byte из request.Data
	rawBytes := requestBytes.RequestData

	// декодируем JSON в структуру
	var activityRequest userAddActivityListRequestStruct
	err := json.Unmarshal(rawBytes, &activityRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// преобразуем входные данные в структуру
	users := make([]usercache.UserActivityStruct, 0, len(activityRequest.Users))
	for _, u := range activityRequest.Users {
		users = append(users, usercache.UserActivityStruct{
			UserId:       u.UserId,
			SessionUniq:  u.SessionUniq,
			LastPingWsAt: u.LastPingWsAt,
		})
	}

	// вызываем бизнес-логику
	err = user.AddActivityList(users)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}
