package controller

import (
	"encoding/json"
	"go_sender_balancer/api/includes/methods/system"
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
	"reloadConfig":   systemController{}.reloadConfig,
	"reloadSharding": systemController{}.reloadSharding,
	"checkSharding":  systemController{}.checkSharding,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// собираем информацию о состоянии системы
func (systemController) status(_ []byte) ResponseStruct {

	statusInfoItem, err := system.Status()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(statusInfoItem)
}

// получаем информацию о goroutines микросервиса
func (systemController) traceGoroutine(_ []byte) ResponseStruct {

	err := system.TraceGoroutine()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// получаем информацию о выделенной памяти
func (systemController) traceMemory(_ []byte) ResponseStruct {

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
func (systemController) cpuProfile(requestBytes []byte) ResponseStruct {

	// переводим json в структуру
	request := systemCpuProfileRequestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = system.CpuProfile(request.Time)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// обновляем конфигурацию
func (systemController) reloadConfig(_ []byte) ResponseStruct {

	configItem, err := system.ReloadConfig()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(configItem)

}

// обновляем sharding конфигурацию
func (systemController) reloadSharding(_ []byte) ResponseStruct {

	err := system.ReloadSharding()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// проверяем sharding конфигурацию
func (systemController) checkSharding(_ []byte) ResponseStruct {

	system.CheckSharding()

	return Ok()
}
