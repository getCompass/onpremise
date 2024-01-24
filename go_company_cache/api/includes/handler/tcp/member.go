package handlerTcp

import (
	"github.com/getCompassUtils/go_base_frame"
	"go_company_cache/api/includes/controller/member"
	"go_company_cache/api/includes/type/request"
)

// -------------------------------------------------------
// контроллер предназанченный для вызова функций для сессий
// -------------------------------------------------------

type memberController struct{}

// поддерживаемые методы
var memberMethods = methodMap{
	"getlist":        memberController{}.GetList,
	"deletebyuserid": memberController{}.DeleteByUserId,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// формат запроса
type memberGetListRequestStruct struct {
	UserIdList []int64 `json:"user_id_list"`
	CompanyId  int64   `json:"company_id"`
}

// GetList получаем информацию по нескольким сущностям
func (memberController) GetList(data *request.Data) ResponseStruct {

	memberRequest := memberGetListRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &memberRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	memberList, notFoundList := member.GetList(data.CompanyIsolation, memberRequest.UserIdList)

	return Ok(struct {
		MemberList   interface{} `json:"member_list"`
		NotFoundList []int64     `json:"not_found_list"`
	}{
		MemberList:   memberList,
		NotFoundList: notFoundList,
	})
}

// формат запроса
type memberDeleteByUserIdRequestStruct struct {
	UserID    int64 `json:"user_id"`
	CompanyId int64 `json:"company_id"`
}

// DeleteByUserId удалить пользователя из кэша по его userID
func (memberController) DeleteByUserId(data *request.Data) ResponseStruct {

	memberRequest := memberDeleteByUserIdRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &memberRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// удаляем
	member.DeleteFromCacheByUserId(data.CompanyIsolation, memberRequest.UserID)

	return Ok()
}
