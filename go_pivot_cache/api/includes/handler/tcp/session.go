package handlerTcp

import (
	"context"
	"encoding/json"
	"go_pivot_cache/api/includes/controller/session"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// контроллер предназанченный для вызова функций для сессий
// -------------------------------------------------------

type sessionController struct{}

// поддерживаемые методы
var sessionMethods = methodMap{
	"getinfo":             sessionController{}.GetInfo,
	"deletebyuserid":      sessionController{}.DeleteByUserId,
	"deletebysessionuniq": sessionController{}.DeleteBySessionUniq,
	"deleteuserinfo":      sessionController{}.DeleteUserInfo,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// формат запроса
type sessionGetInfoRequestStruct struct {
	ShardID     string `json:"shard_id"`
	TableID     string `json:"table_id"`
	SessionUniq string `json:"session_uniq"` // Идентификатор сессии
	IPAddress   string `json:"ip_address"`
}

// формат запроса
type sessionGetInfoResponseStruct struct {
	UserId      int64 `json:"user_id"`
	RefreshedAt int32 `json:"refreshed_at"`
}

// получаем информацию по сессии
func (sessionController) GetInfo(requestBytes []byte) ResponseStruct {

	request := sessionGetInfoRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	userId, refreshedAt, err := session.GetInfo(ctx, request.SessionUniq, request.ShardID, request.TableID)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(sessionGetInfoResponseStruct{
		UserId:      userId,
		RefreshedAt: refreshedAt,
	})
}

// формат запроса
type sessionDeleteByUserIdRequestStruct struct {
	UserID int64 `json:"user_id"`
}

// удалить все сессии пользователя по его userID
func (sessionController) DeleteByUserId(requestBytes []byte) ResponseStruct {

	request := sessionDeleteByUserIdRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// удаляем пользовательские сессии из кэша
	session.DeleteByUserId(request.UserID)

	return Ok()
}

// формат запроса
type sessionDeleteBySessionUniqRequestStruct struct {
	SessionUniq string `json:"session_uniq"`
}

// удалить конкретную сессию из кэша
func (sessionController) DeleteBySessionUniq(requestBytes []byte) ResponseStruct {

	request := sessionDeleteBySessionUniqRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// удаляем сессию из кеша по sessionUniq
	session.DeleteBySessionUniq(request.SessionUniq)

	return Ok()
}

// формат запроса
type sessionDeleteUserInfoRequestStruct struct {
	UserID int64 `json:"user_id"`
}

// удаляет информацио о пользователе, не трогает сессию
func (sessionController) DeleteUserInfo(requestBytes []byte) ResponseStruct {

	request := sessionDeleteUserInfoRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// удаляем объект пользователя из кэша
	session.DeleteUserInfo(request.UserID)

	return Ok()
}
