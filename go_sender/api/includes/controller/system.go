package controller

import (
	"encoding/json"
	"go_sender/api/includes/methods/system"
	"go_sender/api/includes/type/request"
	"google.golang.org/grpc/status"
)

// -------------------------------------------------------
// контроллер предназанченный для вызова системных функций
// -------------------------------------------------------

type systemController struct{}

// поддерживаемые методы
var systemMethods = methodMap{
	"status":         systemController{}.status,
	"traceGoroutine": systemController{}.traceGoroutine,
	"traceMemory":    systemController{}.traceMemory,
	"cpuProfile":     systemController{}.cpuProfile,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// собираем информацию о состоянии системы
func (systemController) status(data *request.Data) ResponseStruct {

	statusInfoItem, err := system.Status(data.CompanyEnvList)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(statusInfoItem)
}

// получаем информацию о goroutines микросервиса
func (systemController) traceGoroutine(_ *request.Data) ResponseStruct {

	err := system.TraceGoroutine()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// получаем информацию о выделенной памяти
func (systemController) traceMemory(_ *request.Data) ResponseStruct {

	err := system.TraceMemory()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// структура запроса
type systemCpuProfileRequestStruct struct {
	Time int `json:"time"` // время профилирования (сек.)
}

// собираем информацию о нагрузке процессора за указанное время
func (systemController) cpuProfile(data *request.Data) ResponseStruct {

	// переводим json в структуру
	systemRequest := systemCpuProfileRequestStruct{}
	err := json.Unmarshal(data.RequestData, &systemRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = system.CpuProfile(systemRequest.Time)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}
