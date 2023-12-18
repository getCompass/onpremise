package conf

/* Пакет конфигурация */
/* В этом файле описан конфиг вещания событий и подписок */

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"os"
	"path"
	"runtime"
	"sync/atomic"
)

// тип — параметры сервиса событий
type LocaleStruct struct {
	StringStore map[string]map[string]string `json:"string_store"`
}

// переменная содержащая конфигурацию
var localeConfig atomic.Value

// возвращает конфиг локалей
func GetLocaleConf() LocaleStruct {

	config := localeConfig.Load()
	if config == nil {

		err := UpdateLocaleConfig()
		if err != nil {
			panic(err)
		}

		config = localeConfig.Load()
	}

	return config.(LocaleStruct)
}

// обновляем конфигурацию
func UpdateLocaleConfig() error {

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b))
	}

	// сохраняем конфигурацию
	decodedInfo, err := getLocaleConfigFromFile(tempPath + "/locale.json")
	if err != nil {
		return err
	}

	// записываем конфигурацию в хранилище
	localeConfig.Store(decodedInfo)

	return nil
}

// получаем конфиг из файла
func getLocaleConfigFromFile(path string) (LocaleStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return LocaleStruct{}, fmt.Errorf("unable read file locale.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := go_base_frame.Json.NewDecoder(file)
	var decodedInfo LocaleStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return LocaleStruct{}, fmt.Errorf("unable decode file locale.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}
