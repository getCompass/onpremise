package handlerTcp

// tcp handler для системных функций
import (
	"github.com/getCompassUtils/go_base_frame"
	"go_company_cache/api/includes/controller/system"
	"go_company_cache/api/includes/type/request"
	"google.golang.org/grpc/status"
)

type systemHandler struct{}

// поддерживаемые методы
var systemMethods = methodMap{
	"status":         systemHandler{}.status,
	"traceGoroutine": systemHandler{}.traceGoroutine,
	"traceMemory":    systemHandler{}.traceMemory,
	"cpuProfile":     systemHandler{}.cpuProfile,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// собираем информацию о состоянии системы
func (systemHandler) status(_ *request.Data) ResponseStruct {

	statusInfoItem, err := system.Status()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok(statusInfoItem)
}

// получаем информацию о goroutines микросервиса
func (systemHandler) traceGoroutine(_ *request.Data) ResponseStruct {

	err := system.TraceGoroutine()
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}

// получаем информацию о выделенной памяти
func (systemHandler) traceMemory(_ *request.Data) ResponseStruct {

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
func (systemHandler) cpuProfile(data *request.Data) ResponseStruct {

	// переводим json в структуру
	systemRequest := systemCpuProfileRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &systemRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	err = system.CpuProfile(systemRequest.Time)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}
