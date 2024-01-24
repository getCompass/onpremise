package controller

import (
	"fmt"
	CompanyEnvironment "go_event/api/includes/type/company_config"
	"go_event/api/includes/type/event"
	"go_event/api/includes/type/event_broker"
	"go_event/api/includes/type/request"
)

type eventController struct{}

// поддерживаемые методы
var eventMethods = methodMap{
	"dispatch": eventController{}.dispatch,
	"handle":   eventController{}.handle,
}

// точка входа для обработки любого события в системе
// отсюда будет вестись вещание
func (eventController) dispatch(data request.Data) ResponseStruct {

	// декодим наше событие
	appEvent, err := Event.CreateEventFromRequest(data.RequestData)

	if err != nil {
		return Error(105, "bad json in request")
	}

	isolation := CompanyEnvironment.GetEnv(data.CompanyId)

	if isolation == nil {
		return Error(400, "company is not served by service")
	}

	// добавляем задачу в хранилище
	if err := EventBroker.Handle(isolation, &appEvent); err != nil {
		return Error(400, fmt.Sprintf("bad event in request, error: %s", err.Error()))
	}

	return Ok()
}

// выполняет обработку события из запроса
func (eventController) handle(data request.Data) ResponseStruct {

	// получаем событие из запроса
	appEvent, err := Event.CreateEventFromRequest(data.RequestData)

	if err != nil {
		return Error(105, "bad json in request")
	}

	// обрабатываем событие
	Event.Handle(&appEvent)

	return Ok()
}
