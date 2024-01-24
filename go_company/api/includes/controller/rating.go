package controller

import (
	"github.com/getCompassUtils/go_base_frame"
	"go_company/api/includes/methods/methods_rating"
	structGeneral "go_company/api/includes/struct/general"
	structUser "go_company/api/includes/struct/user"
	"go_company/api/includes/type/request"
	"google.golang.org/grpc/status"
)

// даже когда контроллер ничего не возвращает - назначаем p.response на пустой map[string]string

// -------------------------------------------------------
// контроллер предназначенный для работы с рейтингом
// -------------------------------------------------------

type ratingController struct{}

// поддерживаемые методы
var ratingMethods = methodMap{
	"inc":                            ratingController{}.Inc,
	"dec":                            ratingController{}.Dec,
	"get":                            ratingController{}.Get,
	"getByUserId":                    ratingController{}.GetByUserId,
	"getEventCountByInterval":        ratingController{}.GetEventCountByInterval,
	"getGeneralEventCountByInterval": ratingController{}.GetGeneralEventCountByInterval,
	"forceSaveCache":                 ratingController{}.forceSaveCache,
	"setUserBlockInSystemStatus":     ratingController{}.setUserBlockInSystemStatus,
	"getListByDay":                   ratingController{}.getListByDay,
}

// структура для запроса rating.inc
type ratingIncRequestStruct struct {
	Event     string `json:"event"`
	UserId    int64  `json:"user_id"`
	Inc       int    `json:"inc"`
	CompanyId int64  `json:"company_id"`
}

// инкрементим количество ивентов
func (ratingController) Inc(data *request.Data) ResponseStruct {

	ratingRequest := ratingIncRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &ratingRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = methods_rating.Inc(data.CompanyIsolation.RatingStore, ratingRequest.Event, ratingRequest.UserId, ratingRequest.Inc)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// структура для запроса rating.dec
type ratingDecRequestStruct struct {
	Event     string `json:"event"`
	UserId    int64  `json:"user_id"`
	CreatedAt int64  `json:"created_at"`
	Dec       int    `json:"dec"`
}

// декрементим количество ивентов
func (ratingController) Dec(data *request.Data) ResponseStruct {

	ratingRequest := ratingDecRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &ratingRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// декрементим значение ивента в рейтинге
	err = methods_rating.Dec(data.CompanyIsolation.Context, data.CompanyIsolation.MainStorage, data.CompanyIsolation.RatingStore, data.CompanyIsolation.CompanyDataConn,
		ratingRequest.Event, ratingRequest.UserId, ratingRequest.CreatedAt, ratingRequest.Dec)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// структура для запроса rating.get
type ratingGetRequestStruct struct {
	Event         string `json:"event"`
	FromDateAt    int    `json:"from_date_at"`
	ToDateAt      int    `json:"to_date_at"`
	TopListOffset int    `json:"top_list_offset"`
	TopListCount  int    `json:"top_list_count"`
}

// метод для получения общего рейтинга
func (ratingController) Get(data *request.Data) ResponseStruct {

	ratingRequest := ratingGetRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &ratingRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	response, _, err := methods_rating.Get(data.CompanyIsolation.Context, data.CompanyIsolation.MainStorage, data.CompanyIsolation.CompanyDataConn, ratingRequest.Event,
		ratingRequest.FromDateAt, ratingRequest.ToDateAt, ratingRequest.TopListOffset, ratingRequest.TopListCount)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(response)
}

// структура для запроса rating.getByUserId
type ratingGetByUserIdRequestStruct struct {
	UserId          int64   `json:"user_id"`
	Year            int     `json:"year"`
	FromDateAtList  []int64 `json:"from_date_at_list"`
	ToDateAtList    []int64 `json:"to_date_at_list"`
	IsFromCacheList []int64 `json:"is_from_cache_list"`
}

// структура списка для ответа rating.getByUserId
type ratingGetByUserIdListResponseStruct struct {
	UserRatingList []structUser.Rating `json:"user_rating_list"`
}

// метод для рейтинга по userId
func (ratingController) GetByUserId(data *request.Data) ResponseStruct {

	ratingRequest := ratingGetByUserIdRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &ratingRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	userRating, _, err := methods_rating.GetByUserId(data.CompanyIsolation.Context, data.CompanyIsolation.CompanyDataConn, data.CompanyIsolation.UserRatingByDays,
		ratingRequest.UserId, ratingRequest.Year, ratingRequest.FromDateAtList, ratingRequest.ToDateAtList, ratingRequest.IsFromCacheList)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(ratingGetByUserIdListResponseStruct{UserRatingList: userRating})
}

// структура для запроса methods_rating.GetEventCountByInterval
type ratingGetEventCountByIntervalRequestStruct struct {
	Event      string `json:"event"`
	Year       int    `json:"year"`
	FromDateAt int    `json:"from_date_at"`
	ToDateAt   int    `json:"to_date_at"`
}

// структура для ответа methods_rating.GetEventCountByInterval
type ratingGetEventCountByIntervalResponseStruct struct {
	EventCountList []structGeneral.EventCount `json:"event_count_list"`
}

// получаем ивенты за период времени
func (ratingController) GetEventCountByInterval(data *request.Data) ResponseStruct {

	// получаем реквест
	ratingRequest := ratingGetEventCountByIntervalRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &ratingRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	eventCountList, _, err := methods_rating.GetEventCountByInterval(data.CompanyIsolation.Context, data.CompanyIsolation.CompanyDataConn, ratingRequest.Event,
		ratingRequest.Year, ratingRequest.FromDateAt, ratingRequest.ToDateAt)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(ratingGetEventCountByIntervalResponseStruct{EventCountList: eventCountList})
}

// структура для запроса rating.getGeneralEventCountByInterval
type ratingGetGeneraEventCountByIntervalRequestStruct struct {
	Year       int `json:"year"`
	FromDateAt int `json:"from_date_at"`
	ToDateAt   int `json:"to_date_at"`
}

// структура для ответа rating.getGeneralEventCountByInterval
type ratingGetGeneraEventCountByIntervalResponseStruct struct {
	EventCountList []structGeneral.EventCount `json:"event_count_list"`
}

// получаем ивенты за период времени
func (ratingController) GetGeneralEventCountByInterval(data *request.Data) ResponseStruct {

	ratingRequest := ratingGetGeneraEventCountByIntervalRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &ratingRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	eventCountList, _, err := methods_rating.GetGeneralEventCountByInterval(data.CompanyIsolation.Context, data.CompanyIsolation.CompanyDataConn, ratingRequest.Year,
		ratingRequest.FromDateAt, ratingRequest.ToDateAt)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(ratingGetGeneraEventCountByIntervalResponseStruct{EventCountList: eventCountList})
}

// сохраняем данные из кеша
func (ratingController) forceSaveCache(data *request.Data) ResponseStruct {

	// запускаем в отдельной рутине и сразу отдаем ответ, чтобы не поймать timeout exception
	// потому что dump на паблике занимает относительно много времени, и нет смысла ждать ответа от него синхронно
	methods_rating.ForceSaveCache(data.CompanyIsolation.Context, data.CompanyIsolation.MainStorage, data.CompanyIsolation.RatingStore,
		data.CompanyIsolation.CompanyDataConn, data.CompanyIsolation.GetGlobalIsolation())

	return Ok()
}

// структура для запроса rating.setUserBlockInSystemStatus
type ratingSetUserBlockInSystemStatusRequestStruct struct {
	UserId int64 `json:"user_id"`
	Status int   `json:"status"`
}

// структура для запроса rating.getUserStatus
type ratingGetUserStatusRequestStruct struct {
	UserId int64 `json:"user_id"`
}

// структура для запроса rating.getUserStatus
type ratingGetUserStatusResponseStruct struct {
	Status int `json:"status"`
}

// помечаем пользователя забаненным (разбаненным в рейтинге)
func (ratingController) setUserBlockInSystemStatus(data *request.Data) ResponseStruct {

	ratingRequest := ratingSetUserBlockInSystemStatusRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &ratingRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = methods_rating.SetUserBlockInSystemStatus(data.CompanyIsolation.Context, data.CompanyIsolation.CompanyDataConn, ratingRequest.UserId, ratingRequest.Status)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// получаем статус пользователя в рейтинге
func (ratingController) GetUserStatus(data *request.Data) ResponseStruct {

	ratingRequest := ratingGetUserStatusRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &ratingRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	userStatus, err := methods_rating.GetUserStatus(data.CompanyIsolation.Context, data.CompanyIsolation.CompanyDataConn, ratingRequest.UserId)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(ratingGetUserStatusResponseStruct{Status: userStatus})
}

// структура для запроса rating.getListByDay
type ratingGetListByDayRequestStruct struct {
	Year int `json:"year"`
	Day  int `json:"day"`
}

// структура для ответа rating.getGeneralEventCountByInterval
type ratingGetListByDayResponseStruct struct {
	UserDayStatsList []structGeneral.UserDayStats `json:"user_day_stats_list"`
}

// получаем всю статистику по пользователям за день
func (ratingController) getListByDay(data *request.Data) ResponseStruct {

	ratingRequest := ratingGetListByDayRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &ratingRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	list, _, err := methods_rating.GetListByDay(data.CompanyIsolation.Context, data.CompanyIsolation.CompanyDataConn, ratingRequest.Year, ratingRequest.Day)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(ratingGetListByDayResponseStruct{UserDayStatsList: list})
}
