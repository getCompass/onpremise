package conf

/* Пакет конфигурация */
/* В этом файле описан конфиг вещания событий и подписок */

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"os"
	"path"
	"runtime"
	"sync/atomic"
)

// тип — параметры сервиса событий
type EventServiceStruct struct {
	RabbitKey string `json:"rabbit_key"`
	Method    string `json:"method"`
	Queue     string `json:"queue"`
	Exchange  string `json:"exchange"`
}

// тип - параметры хранилища подписчиков
type SubscriberStorageStruct struct {
	Db    string `json:"db"`
	Table string `json:"table"`
}

// тип — параметры для работы событий
type EventStruct struct {
	SourceIdentifier          string                  `json:"source_identifier"`
	SourceType                string                  `json:"source_type"`
	EventQueue                string                  `json:"event_queue"`
	EventExchange             string                  `json:"event_exchange"`
	EventService              EventServiceStruct      `json:"event_service"`
	SubscriberStorage         SubscriberStorageStruct `json:"subscriber_storage"`
	SubscriptionList          json.RawMessage         `json:"subscription_list"`
	EventDiscreteCourierCount int                     `json:"event_discrete_courier_count"`
	EventDiscreteCourierDelay int                     `json:"event_discrete_courier_delay"`
	TaskDiscreteCourierCount  int                     `json:"task_discrete_courier_count"`
	TaskDiscreteCourierDelay  int                     `json:"task_discrete_courier_delay"`
	PerDeliveryLimit          int                     `json:"per_delivery_limit"`
}

// переменная содержащая конфигурацию
var eventConfig atomic.Value

func GetEventConf() EventStruct {

	config := eventConfig.Load()
	if config == nil {

		err := UpdateEventConfig()
		if err != nil {
			panic(err)
		}

		config = eventConfig.Load()
	}

	return config.(EventStruct)
}

// обновляем конфигурацию
func UpdateEventConfig() error {

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b))
	}

	// сохраняем конфигурацию
	decodedInfo, err := getEventConfigFromFile(tempPath + "/event.json")
	if err != nil {
		return err
	}

	// записываем конфигурацию в хранилище
	eventConfig.Store(decodedInfo)

	return nil
}

// получаем конфиг из файла
func getEventConfigFromFile(path string) (EventStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return EventStruct{}, fmt.Errorf("unable read file event.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := go_base_frame.Json.NewDecoder(file)
	var decodedInfo EventStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return EventStruct{}, fmt.Errorf("unable decode file event.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}
