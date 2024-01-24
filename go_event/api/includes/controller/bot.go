package controller

// -----------------------------------------------
// контроллер, предназначенный для работы с ботами
// -----------------------------------------------

import (
	CompanyEnvironment "go_event/api/includes/type/company_config"
	"go_event/api/includes/type/event"
	"go_event/api/includes/type/request"
	"go_event/api/includes/type/system_bot"
)

type bot struct{}

// поддерживаемые методы
var botMethods = methodMap{
	"handle":        bot{}.handle,
	"handleTrigger": bot{}.handleTrigger,
}

// вызвать функцию системного бота,
// единственная входная точка для обычных событий, которые хотят получить системные боты
func (bot) handle(data request.Data) ResponseStruct {

	// декодим наше событие
	appEvent, err := Event.CreateEventFromRequest(data.RequestData)
	if err != nil {
		return Error(105, "bad json in request")
	}

	isolation := CompanyEnvironment.GetEnv(data.CompanyId)

	if isolation == nil {
		return Error(400, "company is not served by service")
	}

	// передаем обработку события системным ботам
	if err = SystemBot.HandleEvent(isolation, &appEvent); err != nil {
		return Error(400, err.Error())
	}

	return Ok()
}

// вызвать функцию системного бота,
// точка для событий, которые должны только стриггерить бота, но не оказывать влияния на систему
func (bot) handleTrigger(data request.Data) ResponseStruct {

	// декодим наше событие
	appEvent, err := Event.CreateEventFromRequest(data.RequestData)
	if err != nil {
		return Error(105, "bad json in request")
	}

	isolation := CompanyEnvironment.GetEnv(data.CompanyId)

	if isolation == nil {
		return Error(400, "company is not served by service")
	}

	// передаем обработку события системным ботам
	if err = SystemBot.HandleTrigger(isolation, &appEvent); err != nil {
		return Error(400, err.Error())
	}

	return Ok()
}
