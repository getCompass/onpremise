package handlerTcp

import (
	"github.com/getCompassUtils/go_base_frame"
	"go_company_cache/api/includes/controller/session"
	"go_company_cache/api/includes/type/request"
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
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// формат запроса
type sessionGetInfoRequestStruct struct {
	SessionUniq string `json:"session_uniq"` // Идентификатор сессии
	IPAddress   string `json:"ip_address"`
	CompanyId   int64  `json:"company_id"`
}

// GetInfo получаем информацию по сессии
func (sessionController) GetInfo(data *request.Data) ResponseStruct {

	sessionRequest := sessionGetInfoRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &sessionRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	sessionInfo, err := session.GetInfo(data.CompanyIsolation, sessionRequest.SessionUniq)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(sessionInfo)
}

// формат запроса
type sessionDeleteByUserIdRequestStruct struct {
	UserID    int64 `json:"user_id"`
	CompanyId int64 `json:"company_id"`
}

// DeleteByUserId удалить все сессии пользователя по его userID
func (sessionController) DeleteByUserId(data *request.Data) ResponseStruct {

	sessionRequest := sessionDeleteByUserIdRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &sessionRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	session.DeleteByUserId(data.CompanyIsolation, sessionRequest.UserID)

	return Ok()
}

// формат запроса
type sessionDeleteBySessionUniqRequestStruct struct {
	SessionUniq string `json:"session_uniq"`
	CompanyId   int64  `json:"company_id"`
}

// DeleteBySessionUniq удалить конкретную сессию из кэша
func (sessionController) DeleteBySessionUniq(data *request.Data) ResponseStruct {

	sessionRequest := sessionDeleteBySessionUniqRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &sessionRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	session.DeleteBySessionUniq(data.CompanyIsolation, sessionRequest.SessionUniq)

	return Ok()
}
