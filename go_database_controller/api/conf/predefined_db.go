package conf

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"os"
	"path"
	"runtime"
)

// структура предефайнд базы
type PredefinedDbStruct struct {
	Host         string `json:"host"`
	Port         int    `json:"port"`
	RootPassword string `json:"root_password"`
}

// -------------------------------------------------------
// пакет получения predefined конфигурации
// -------------------------------------------------------

// переменная содержащая конфигурацию
var predefinedDbConfig map[string]*PredefinedDbStruct

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// обновляем конфигурацию
func UpdatePredefinedDbConfig() error {

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b)) // nosemgrep
	}

	// сохраняем конфигурацию
	decodedInfo, err := getPredefinedDbConfigFromFile(tempPath + "/predefined_db.json")
	if err != nil {
		return err
	}

	predefinedDbConfig = decodedInfo

	return nil
}

// получаем конфиг из файла
func getPredefinedDbConfigFromFile(path string) (map[string]*PredefinedDbStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return nil, fmt.Errorf("unable read file predefined_db.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := json.NewDecoder(file)
	var decodedInfo map[string]*PredefinedDbStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return nil, fmt.Errorf("unable decode file predefined_db.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}

// получаем конфигурацию custom
func GetPredefinedDbConfig() map[string]*PredefinedDbStruct {

	if predefinedDbConfig == nil {

		err := UpdatePredefinedDbConfig()
		if err != nil {
			panic(err)
		}
	}

	return predefinedDbConfig
}
