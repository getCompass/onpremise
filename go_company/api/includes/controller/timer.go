package controller

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame"
	"go_company/api/includes/methods/timer_methods"
	"go_company/api/includes/type/request"
	timer_worker "go_company/api/includes/type/timer/worker"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// контроллер, предназначенный для взаимодействия с go_timer
// -------------------------------------------------------

type timerController struct{}

// поддерживаемые методы
var timerMethods = methodMap{
	"setTimeout":              timerController{}.SetTimeout,
	"setTimeoutForUserIdList": timerController{}.SetTimeoutForUserIdList,
	"doForceWork":             timerController{}.DoForceWork,
	"deleteTaskCache":         timerController{}.DeleteTaskCache,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// SetTimeoutRequestStruct структура запроса на отправку события для go_sender
type SetTimeoutRequestStruct struct {
	RequestName string          `json:"request_name"`
	RequestKey  string          `json:"request_key"`
	RequestData json.RawMessage `json:"request_data,omitempty"`
	TaskList    []string        `json:"task_list,omitempty"`
	IsAdd       int             `json:"is_add,omitempty"`
	UserId      int64           `json:"user_id,omitempty"`
	Timeout     int             `json:"timeout,omitempty"`
}

// SetTimeout добавляем задачу с отложенным выполнением
func (timerController) SetTimeout(data *request.Data) ResponseStruct {

	// пробуем получить параметры запроса
	setTimeoutRequest := SetTimeoutRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &setTimeoutRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// если не переданы параметры
	if len(setTimeoutRequest.RequestName) < 1 || len(setTimeoutRequest.RequestKey) < 1 {
		return Error(409, "bad params in request")
	}

	// добавляем задачу с отложенным выполнением
	err = timer_methods.SetTimeout(data.CompanyIsolation, setTimeoutRequest.RequestName, setTimeoutRequest.RequestKey, setTimeoutRequest.RequestData, setTimeoutRequest.TaskList, setTimeoutRequest.IsAdd, setTimeoutRequest.UserId, setTimeoutRequest.Timeout)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// SetTimeoutForUserIdListRequestStruct структура запроса на отправку события в go_sender для списка пользователей
type SetTimeoutForUserIdListRequestStruct struct {
	RequestName string          `json:"request_name"`
	RequestKey  string          `json:"request_key"`
	RequestData json.RawMessage `json:"request_data,omitempty"`
	TaskList    []string        `json:"task_list,omitempty"`
	IsAdd       int             `json:"is_add,omitempty"`
	UserIdList  []int64         `json:"user_id_list,omitempty"`
	Timeout     int             `json:"timeout,omitempty"`
}

// SetTimeoutForUserIdList добавляем задачу с отложенным выполнением для списка пользователей
func (timerController) SetTimeoutForUserIdList(data *request.Data) ResponseStruct {

	// пробуем получить параметры запроса
	setTimeoutForUserRequest := SetTimeoutForUserIdListRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &setTimeoutForUserRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// если не переданы параметры
	if len(setTimeoutForUserRequest.RequestName) < 1 || len(setTimeoutForUserRequest.RequestKey) < 1 {
		return Error(409, "bad params in request")
	}

	// добавляем задачу  с отложенным выполнением для каждого пользователя
	for _, UserId := range setTimeoutForUserRequest.UserIdList {

		err = timer_methods.SetTimeout(
			data.CompanyIsolation,
			setTimeoutForUserRequest.RequestName, setTimeoutForUserRequest.RequestKey,
			setTimeoutForUserRequest.RequestData, setTimeoutForUserRequest.TaskList,
			setTimeoutForUserRequest.IsAdd, UserId, setTimeoutForUserRequest.Timeout,
		)
	}

	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// ForceWorkRequestStruct структура запроса на форсированную отправку задачи
type ForceWorkRequestStruct struct {
	RequestKey string `json:"request_key"`
}

// незамедлительно выполнить таск
func (timerController) DoForceWork(data *request.Data) ResponseStruct {

	// пробуем получить параметры запроса
	forceWorkRequest := ForceWorkRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &forceWorkRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	task := data.CompanyIsolation.TimerStore.GetTask(forceWorkRequest.RequestKey)

	// если таска не нашли то он выполнился
	if task == nil {
		return Ok()
	}
	timer_worker.DoWorkTask(data.CompanyIsolation, task)

	return Ok()
}

// очищаем кэш данных для выполнения
func (timerController) DeleteTaskCache(data *request.Data) ResponseStruct {

	data.CompanyIsolation.TimerStore.ClearStore()
	return Ok()
}
