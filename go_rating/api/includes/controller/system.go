package controller

import (
	"github.com/getCompassUtils/go_base_frame"
	"go_rating/api/includes/methods/system"
	"go_rating/api/includes/type/request"
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
func (systemController) status(_ request.Data) ResponseStruct {

	statusInfoItem, err := system.Status()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(statusInfoItem)
}

// получаем информацию о goroutines микросервиса
func (systemController) traceGoroutine(_ request.Data) ResponseStruct {

	err := system.TraceGoroutine()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// получаем информацию о выделенной памяти
func (systemController) traceMemory(_ request.Data) ResponseStruct {

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
func (systemController) cpuProfile(data request.Data) ResponseStruct {

	// переводим json в структуру
	cpuProfileRequest := systemCpuProfileRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &cpuProfileRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = system.CpuProfile(cpuProfileRequest.Time)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}
