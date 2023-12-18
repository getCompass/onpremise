package controller

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"go_event/api/includes/type/async_task"
	CompanyEnvironment "go_event/api/includes/type/company_config"
	"go_event/api/includes/type/request"
)

type taskController struct{}

// поддерживаемые методы
var taskMethods = methodMap{
	"push": taskController{}.push,
}

// запрос на добавление задачи
type pushRequest struct {
	UniqueKey  string          `json:"unique_key"`          // уникальный ключ задачи
	Type       string          `json:"type"`                // тип задачи
	Data       json.RawMessage `json:"data"`                // данные задачи
	Module     string          `json:"module"`              // исполнитель задачи
	Group      string          `json:"group"`               // группа задачи
	NeedWork   int64           `json:"need_work,omitempty"` // когда задача должна взяться в работу
	ErrorLimit int             `json:"error_limit"`         // сколько ошибок допустимо для этой задачи
}

// добавляет новую задачу
func (taskController) push(data request.Data) ResponseStruct {

	req := pushRequest{}

	// декодим данные запроса
	if err := go_base_frame.Json.Unmarshal(data.RequestData, &req); err != nil {
		return Error(105, "bad json in request")
	}

	isolation := CompanyEnvironment.GetEnv(data.CompanyId)

	if isolation == nil {
		return Error(400, "company is not served by service")
	}

	// добавляем задачу в хранилище
	err := AsyncTask.CreateAndPushToDiscrete(isolation, req.Type, AsyncTask.TaskTypeSingle, req.NeedWork, req.Data, req.Module, req.Group)
	if err != nil {
		return Error(400, fmt.Sprintf("bad task in request, error: %s", err.Error()))
	}

	return Ok()
}
