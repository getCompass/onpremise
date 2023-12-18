package controller

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	methodSystem "go_company/api/includes/methods/system"
	"go_company/api/includes/type/request"
	"google.golang.org/grpc/status"
	"os"
	"runtime"
	"runtime/pprof"
	"time"
)

// -------------------------------------------------------
// контроллер предназанченный для вызова системных функций
// -------------------------------------------------------

type system struct{}

// поддерживаемые методы
var systemMethods = methodMap{
	"status":          system{}.status,
	"traceGoroutines": system{}.traceGoroutines,
	"traceMemory":     system{}.traceMemory,
	"cpuProfile":      system{}.cpuProfile,
	"doClearCache":    system{}.doClearCache,
}

// -------------------------------------------------------
// METHODS
// -------------------------------------------------------

// структура ответа
type systemStatusResponseStruct struct {
	Name             string `json:"name"`
	Goroutines       int64  `json:"goroutines"`
	Memory           int64  `json:"memory"`
	MemoryKB         string `json:"memory_kb"`
	MemoryMB         string `json:"memory_mb"`
	UpTime           int64  `json:"uptime"`
	CachedUsersCount int    `json:"cached_user_count"`
}

// собираем информацию о состоянии системы
func (system) status(_ *request.Data) ResponseStruct {

	// читаем память
	memStats := runtime.MemStats{}
	runtime.ReadMemStats(&memStats)

	// собираем ответ
	responseItem := systemStatusResponseStruct{
		Name:       "go_company",
		Goroutines: int64(runtime.NumGoroutine()),
		Memory:     int64(memStats.Alloc),
		MemoryKB:   fmt.Sprintf("%0.3f", float64(memStats.Alloc)*0.001),
		MemoryMB:   fmt.Sprintf("%0.3f", float64(memStats.Alloc)*0.001*0.001),
		UpTime:     functions.GetUpTime(),
	}

	return Ok(responseItem)
}

// получаем информацию о goroutines микросервиса
func (system) traceGoroutines(_ *request.Data) ResponseStruct {

	// создаем файл для записи трейса
	traceFilePath := fmt.Sprintf("%s/logs/trace_%d.log", functions.GetExecutableDir(flags.ExecutableDir), functions.GetCurrentTimeStamp())
	file, err := os.Create(traceFilePath)
	if err != nil {
		return Error(500, "unable to create file")
	}

	// записываем информацию в файл
	err = pprof.Lookup("goroutine").WriteTo(file, 2)
	if err != nil {

		log.Errorf("unable to get goroutines, error: %v", err)
		return Error(500, "unable to get goroutines")
	}

	return Ok()
}

// получаем информацию о выделенной памяти
func (system) traceMemory(_ *request.Data) ResponseStruct {

	// создаем файл для записи информации
	traceFilePath := fmt.Sprintf("%s/logs/memory_heap_%d.log", functions.GetExecutableDir(flags.ExecutableDir), functions.GetCurrentTimeStamp())
	file, err := os.Create(traceFilePath)
	if err != nil {
		return Error(500, "unable to create file")
	}

	// записываем информацию в файл
	err = pprof.Lookup("heap").WriteTo(file, 2)
	if err != nil {

		log.Errorf("unable to get heap, error: %v", err)
		return Error(500, "unable to get heap")
	}

	return Ok()
}

// структура запроса
type systemCpuProfileRequestStruct struct {
	Time int `json:"time"` // время профилирования (сек.)
}

// собираем информацию о нагрузке процессора за указанное время
func (system) cpuProfile(data *request.Data) ResponseStruct {

	// переводим json в структуру
	cpuProfileRequest := systemCpuProfileRequestStruct{}
	err := go_base_frame.Json.Unmarshal(data.RequestData, &cpuProfileRequest)
	if err != nil {
		return Error(105, "bad json in request")
	}

	// проверяем, что время профилирования не больше минуты
	if cpuProfileRequest.Time < 1 || cpuProfileRequest.Time > 60 {
		return Error(400, "incorrect time")
	}

	// создаем файл для записи трейса профилирвоания
	fileName := fmt.Sprintf("%s/logs/profile_%d", functions.GetExecutableDir(flags.ExecutableDir), functions.GetCurrentTimeStamp())
	file, err := os.Create(fileName + ".temp")
	if err != nil {
		return Error(500, "unable to create file")
	}

	// профилируем в файл ассинхронно
	go writeCpuProfileToFile(file, fileName, cpuProfileRequest.Time)

	return Ok()
}

// пишем профилирование в файл
func writeCpuProfileToFile(file *os.File, fileName string, profileTime int) {

	// запускаем профилирование
	err := pprof.StartCPUProfile(file)
	if err != nil {

		log.Errorf("unable start cpu profile, error: %v", err)
		return
	}

	// ждем указанное время
	select {
	case <-time.After(time.Duration(profileTime) * time.Second):

		// останавливаем профилирование
		pprof.StopCPUProfile()

		// переименовываем файл, для ясности, когда профилирование закончилось
		err := os.Rename(fileName+".temp", fileName+".prof")
		if err != nil {
			log.Errorf("unable rename cpu profile file, error: %v", err)
		}
	}
}

// чистим кэш рейтинга
func (system) doClearCache(data *request.Data) ResponseStruct {

	err := methodSystem.DoClearCache(data.CompanyIsolation)
	if err != nil {
		return Error(int(status.Code(err)), status.Convert(err).Message())
	}

	return Ok()
}
