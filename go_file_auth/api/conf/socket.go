package conf

import (
	"fmt"
	"github.com/service/go_base_frame"
	"github.com/service/go_base_frame/api/system/flags"
	"os"
	"path"
	"runtime"
	"sync/atomic"
)

// SocketConfigStruct структура конфига
type SocketConfigStruct struct{}

// переменная содержащая конфигурацию
var socketConfig atomic.Value

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// UpdateSocketConfig обновляем конфигурацию
func UpdateSocketConfig() error {

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b)) // nosemgrep - предлагает заменить path на filepath
	}

	// сохраняем конфигурацию
	decodedInfo, err := getSocketConfigFromFile(tempPath + "/socket.json")
	if err != nil {
		return err
	}

	// записываем конфигурацию в хранилище
	socketConfig.Store(decodedInfo)

	return nil
}

// получаем конфиг из файла
func getSocketConfigFromFile(path string) (SocketConfigStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return SocketConfigStruct{}, fmt.Errorf("unable read file conf.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := go_base_frame.Json.NewDecoder(file)
	var decodedInfo SocketConfigStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return SocketConfigStruct{}, fmt.Errorf("unable decode file conf.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}

// GetSocketConfig получаем конфигурацию custom
func GetSocketConfig() SocketConfigStruct {

	// получаем конфиг
	config := socketConfig.Load()

	// если конфига еще нет
	if config == nil {

		// обновляем конфиг
		err := UpdateSocketConfig()
		if err != nil {
			panic(err)
		}

		// подгружаем новый
		config = socketConfig.Load()
	}

	return config.(SocketConfigStruct)
}
