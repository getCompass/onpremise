package conf

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"os"
	"path"
	"runtime"
	"sync/atomic"
)

// структура конфига
type TestDefineStruct struct {
	ServerType       string `json:"server_type"`
	ApiUrl           string `json:"api_url"`
	GoMockHost       string `json:"go_mock_host"`
	IsPushMockEnable bool   `json:"is_push_mock_enable"`
	Tls              bool   `json:"tls"`
	CrtFilePath      string `json:"crt_file_path"`
}

// переменная содержащая конфигурацию
var testConfiguration atomic.Value

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// обновляем конфигурацию
func UpdateTestConfig() error {

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b))
	}

	// сохраняем конфигурацию
	decodedInfo, err := getTestConfigFromFile(tempPath + "/test_define.json")
	if err != nil {
		return err
	}

	// записываем конфигурацию в хранилище
	testConfiguration.Store(decodedInfo)

	return nil
}

// получаем конфиг из файла
func getTestConfigFromFile(path string) (TestDefineStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return TestDefineStruct{}, fmt.Errorf("unable read file conf.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := json.NewDecoder(file)
	var decodedInfo TestDefineStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return TestDefineStruct{}, fmt.Errorf("unable decode file conf.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}

// получаем конфигурацию custom
func GetTestConfig() TestDefineStruct {

	// получаем конфиг
	config := testConfiguration.Load()

	// если конфига еще нет
	if config == nil {

		// обновляем конфиг
		err := UpdateTestConfig()
		if err != nil {
			panic(err)
		}

		// подгружаем новый
		config = testConfiguration.Load()
	}

	return config.(TestDefineStruct)
}

// подменяем ссылку до firebase
func ChangeIsMockPushEnabled(isPushMockEnable int) {

	config := GetTestConfig()

	// перезаписываем url
	config.IsPushMockEnable = isEnable(isPushMockEnable)

	// записываем обновленный конфиг в хранилище
	testConfiguration.Store(config)
}

// чекаем на bool
func isEnable(isEnable int) bool {

	if isEnable == 1 {
		return true
	}

	return false
}
