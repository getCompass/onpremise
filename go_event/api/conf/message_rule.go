package conf

/* Пакет конфигурация */
/* В этом файле описан конфиг правил по отправке сообщений по типам */

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
type MessageRuleStruct struct {
	EventMessageRuleList map[string][]json.RawMessage `json:"event_message_rule_list"`
}

// переменная содержащая конфигурацию
var messageRuleConfig atomic.Value

// возвращает конфиг настроек сообщений
func GetMessageRuleConf() MessageRuleStruct {

	config := messageRuleConfig.Load()
	if config == nil {

		err := UpdateMessageRuleConfig()
		if err != nil {
			panic(err)
		}

		config = messageRuleConfig.Load()
	}

	return config.(MessageRuleStruct)
}

// обновляем конфигурацию
func UpdateMessageRuleConfig() error {

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b))
	}

	// сохраняем конфигурацию
	decodedInfo, err := getMessageRuleConfigFromFile(tempPath + "/message_rule.json")
	if err != nil {
		return err
	}

	// записываем конфигурацию в хранилище
	messageRuleConfig.Store(decodedInfo)

	return nil
}

// получаем конфиг из файла
func getMessageRuleConfigFromFile(path string) (MessageRuleStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return MessageRuleStruct{}, fmt.Errorf("unable read file message_rule.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := go_base_frame.Json.NewDecoder(file)
	var decodedInfo MessageRuleStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return MessageRuleStruct{}, fmt.Errorf("unable decode file message_rule.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}
