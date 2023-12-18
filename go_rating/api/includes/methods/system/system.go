package system

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"google.golang.org/grpc/status"
	"os"
	"runtime"
	"runtime/pprof"
	"time"
)

// пакет, в который вынесена вся бизнес-логика группы методов system

// структура для ответа в методе system.status
type StatusInfoStruct struct {
	Name       string `json:"name"`
	Goroutines int64  `json:"goroutines"`
	Memory     int64  `json:"memory"`
	MemoryKB   string `json:"memory_kb"`
	MemoryMB   string `json:"memory_mb"`
	UpTime     int64  `json:"uptime"`
}

// собираем информацию о состоянии системы
func Status() (StatusInfoStruct, error) {

	// читаем память
	memStats := runtime.MemStats{}
	runtime.ReadMemStats(&memStats)

	// собираем ответ
	return StatusInfoStruct{
		Name:       "go_rating",
		Goroutines: int64(runtime.NumGoroutine()),
		Memory:     int64(memStats.Alloc),
		MemoryKB:   fmt.Sprintf("%0.3f", float64(memStats.Alloc)*0.001),
		MemoryMB:   fmt.Sprintf("%0.3f", float64(memStats.Alloc)*0.001*0.001),
		UpTime:     functions.GetUpTime(),
	}, nil
}

// получаем информацию о goroutines микросервиса
func TraceGoroutine() error {

	// создаем файл для записи трейса
	traceFilePath := fmt.Sprintf("%s/logs/trace_%d.log", functions.GetExecutableDir(""), functions.GetCurrentTimeStamp())
	file, err := os.Create(traceFilePath)
	if err != nil {
		return status.Error(501, "unable to create file")
	}

	// записываем информацию в файл
	err = pprof.Lookup("goroutine").WriteTo(file, 2)
	if err != nil {

		log.Errorf("unable to get goroutines, error: %v", err)
		return status.Error(502, "unable to get goroutines")
	}

	return nil
}

// получаем информацию о выделенной памяти
func TraceMemory() error {

	// создаем файл для записи информации
	traceFilePath := fmt.Sprintf("%s/logs/memory_heap_%d.log", functions.GetExecutableDir(""), functions.GetCurrentTimeStamp())
	file, err := os.Create(traceFilePath)
	if err != nil {
		return status.Error(501, "unable to create file")
	}

	// записываем информацию в файл
	err = pprof.Lookup("heap").WriteTo(file, 2)
	if err != nil {

		log.Errorf("unable to get heap, error: %v", err)
		return status.Error(503, "unable to get heap")
	}

	return nil
}

// собираем информацию о нагрузке процессора за указанное время
func CpuProfile(time int) error {

	// проверяем, что время профилирования не больше минуты
	if time < 1 || time > 60 {
		return status.Error(414, "incorrect time")
	}

	// создаем файл для записи трейса профилирвоания
	fileName := fmt.Sprintf("%s/logs/profile_%d", functions.GetExecutableDir(""), functions.GetCurrentTimeStamp())
	file, err := os.Create(fileName + ".temp")
	if err != nil {
		return status.Error(501, "unable to create file")
	}

	// профилируем в файл ассинхронно
	go writeCpuProfileToFile(file, fileName, time)

	return nil
}

// пишем профилирование в файл
func writeCpuProfileToFile(file *os.File, fileName string, profileTime int) {

	// запускаем профилирование
	err := pprof.StartCPUProfile(file)
	if err != nil {

		log.Errorf("unable start cpu profile, error: %v", err)
		return
	}

	time.Sleep(time.Duration(profileTime) * time.Second)

	// останавливаем профилирование
	pprof.StopCPUProfile()

	// переименовываем файл, для ясности, когда профилирование закончилось
	err = os.Rename(fileName+".temp", fileName+".prof")
	if err != nil {
		log.Errorf("unable rename cpu profile file, error: %v", err)
	}
}
