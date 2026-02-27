package observer

import (
	"context"
	"go_event/api/conf"
	CompanyEnvironment "go_event/api/includes/type/company_config"
	"os"
	"sync/atomic"
	"time"

	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/server"
)

var (
	isSecondWorker  atomic.Value
	isMinute1Worker atomic.Value

	isNeedWorkMinute1Worker atomic.Value
)

// метод для выполнения работы через время
func Work(ctx context.Context) {

	go doWorkInfinite(ctx)
	go doWork1MinuteInfinite(ctx)
}

// каждую секунду
func doWorkInfinite(ctx context.Context) {

	if isSecondWorker.Load() != nil && isSecondWorker.Load().(bool) == true {
		return
	}

	isSecondWorker.Store(true)

	for {

		if ctx.Err() != nil {
			break
		}

		// нон-стоп обновляем конф
		CompanyEnvironment.UpdateWorldConfig()
		time.Sleep(100 * time.Millisecond)
	}
}

// каждую минуту
// @long
func doWork1MinuteInfinite(ctx context.Context) {

	if isMinute1Worker.Load() != nil && isMinute1Worker.Load().(bool) == true {
		return
	}

	isMinute1Worker.Store(true)

	for {

		if ctx.Err() != nil {
			break
		}

		// если это резервный сервер
		if isReserveServer() {

			// отмечаем, что необходимо выполнить работу
			if isNeedWorkMinute1Worker.Load() == nil || !isNeedWorkMinute1Worker.Load().(bool) {
				isNeedWorkMinute1Worker.Store(true)
			}
			continue
		}

		if isNeedWorkMinute1Worker.Load() == nil {

			isNeedWorkMinute1Worker.Store(false)
			continue
		}

		// если нет необходимости выполнить работу
		if !isNeedWorkMinute1Worker.Load().(bool) {
			continue
		}

		CompanyEnvironment.ReloadIsolation()
		isNeedWorkMinute1Worker.Store(false)

		time.Sleep(time.Minute)
	}
}

// проверяем, является ли сервер резервным
// @long
func isReserveServer() bool {

	if !server.IsOnPremise() {
		return false
	}

	if conf.GetConfig().ServiceLabel == "" {
		return false
	}

	if conf.GetConfig().CompaniesRelationshipFile == "" {
		return false
	}

	// открываем файл
	file, err := os.Open(conf.GetConfig().DominoConfigPath + "/" + conf.GetConfig().CompaniesRelationshipFile)
	if err != nil {

		log.Errorf("unable open file %s, error: %v", conf.GetConfig().CompaniesRelationshipFile, err)
		return false
	}

	// считываем информацию из файла
	decoder := go_base_frame.Json.NewDecoder(file)
	var decodedInfo map[string]map[string]interface{}

	err = decoder.Decode(&decodedInfo)
	if err != nil {

		log.Errorf("unable decode file %s, error: %v", conf.GetConfig().CompaniesRelationshipFile, err)
		return false
	}

	// закрываем файл
	_ = file.Close()

	serviceData := decodedInfo[conf.GetConfig().ServiceLabel]

	if _, exists := serviceData["master"]; !exists {
		return true
	}

	if !serviceData["master"].(bool) {
		return true
	}

	return false
}
