package system

// пакет, в который вынесена вся бизнес-логика группы методов system

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pusher/api/conf"
	"go_pusher/api/system/sharding"
	"google.golang.org/grpc/status"
	"os"
	"runtime"
	"runtime/pprof"
	"time"
)

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
		Name:       "go_pusher",
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
	traceFilePath := fmt.Sprintf("%s/logs/trace_%d.log", functions.GetExecutableDir(flags.ExecutableDir), functions.GetCurrentTimeStamp())
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
	traceFilePath := fmt.Sprintf("%s/logs/memory_heap_%d.log", functions.GetExecutableDir(flags.ExecutableDir), functions.GetCurrentTimeStamp())
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
	fileName := fmt.Sprintf("%s/logs/profile_%d", functions.GetExecutableDir(flags.ExecutableDir), functions.GetCurrentTimeStamp())
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

// обновляем конфигурацию
func ReloadConfig() (conf.ConfigStruct, error) {

	// обновляем конфигурацию
	err := conf.UpdateConfig()
	if err != nil {
		return conf.ConfigStruct{}, status.Error(504, "unable to update config")
	}

	// логируем обновление
	log.Systemf("configuration update")

	return conf.GetConfig(), nil
}

// обновляем sharding конфигурацию
func ReloadSharding() error {

	// обновляем конфигурацию
	err := conf.UpdateShardingConfig()
	if err != nil {
		return status.Error(505, "unable to update sharding config")
	}

	// логируем обновление
	log.Systemf("sharding configuration update")

	return nil
}

func CheckSharding() {

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// получаем конфигурацию sharding
	config := conf.GetShardingConfig()

	// проходимся по всем базам
	for _, v := range config.Mysql {

		err := sharding.Mysql(ctx, v.Db)
		if err != nil {
			log.Systemf("[FAIL] %s", v.Db)
		}

		log.Systemf("[OK] %s", v.Db)
	}
}
