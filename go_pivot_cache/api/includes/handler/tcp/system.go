package handlerTcp

// tcp handler для системных функций
import (
	"encoding/json"
	"go_pivot_cache/api/includes/controller/system"
	"google.golang.org/grpc/status"
)

type systemHandler struct{}

// поддерживаемые методы
var systemMethods = methodMap{
	"status":         systemHandler{}.status,
	"traceGoroutine": systemHandler{}.traceGoroutine,
	"traceMemory":    systemHandler{}.traceMemory,
	"cpuProfile":     systemHandler{}.cpuProfile,
	"reloadConfig":   systemHandler{}.reloadConfig,
	"reloadSharding": systemHandler{}.reloadSharding,
	"checkSharding":  systemHandler{}.checkSharding,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// собираем информацию о состоянии системы
func (systemHandler) status(_ []byte) ResponseStruct {

	statusInfoItem, err := system.Status()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(statusInfoItem)
}

// получаем информацию о goroutines микросервиса
func (systemHandler) traceGoroutine(_ []byte) ResponseStruct {

	err := system.TraceGoroutine()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// получаем информацию о выделенной памяти
func (systemHandler) traceMemory(_ []byte) ResponseStruct {

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
func (systemHandler) cpuProfile(requestBytes []byte) ResponseStruct {

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
func (systemHandler) reloadConfig(_ []byte) ResponseStruct {

	configItem, err := system.ReloadConfig()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(configItem)

}

// обновляем sharding конфигурацию
func (systemHandler) reloadSharding(_ []byte) ResponseStruct {

	err := system.ReloadSharding()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// проверяем sharding конфигурацию
func (systemHandler) checkSharding(_ []byte) ResponseStruct {

	system.CheckSharding()

	return Ok()
}
