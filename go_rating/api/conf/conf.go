package conf

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"os"
	"path"
	"runtime"
	"time"
)

// структура конфига
type ConfigStruct struct {
	LoggingLevel int    `json:"logging_level"`
	ServerType   string `json:"server_type"`

	SocketKeyMe string `json:"socket_key_me"`
	TcpPort     int64  `json:"tcp_port"`
	GrpcPort    int64  `json:"grpc_port"`

	RabbitQueue    string `json:"rabbit_queue"`
	RabbitExchange string `json:"rabbit_exchange"`

	CapacityLimit   int    `json:"capacity_limit"`
	WorldConfigPath string `json:"world_config_path"`

	ServerTagList                       []string      `json:"server_tag_list"`
	ForceCompanyConfigUpdateIntervalSec time.Duration `json:"force_company_config_update_interval_sec"`
}

// переменная содержащая конфигурацию
var configuration *ConfigStruct

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// обновляем конфигурацию
func UpdateConfig() error {

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b)) // nosemgrep
	}

	// сохраняем конфигурацию
	decodedInfo, err := getConfigFromFile(tempPath + "/conf.json")
	if err != nil {
		return err
	}

	// записываем конфигурацию в хранилище
	configuration = decodedInfo

	return nil
}

// получаем конфиг из файла
func getConfigFromFile(path string) (*ConfigStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return &ConfigStruct{}, fmt.Errorf("unable read file conf.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := go_base_frame.Json.NewDecoder(file)
	decodedInfo := &ConfigStruct{}

	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return &ConfigStruct{}, fmt.Errorf("unable decode file conf.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}

// получаем конфигурацию custom
func GetConfig() *ConfigStruct {

	// если конфига еще нет
	if configuration == nil {

		// обновляем конфиг
		err := UpdateConfig()
		if err != nil {
			panic(err)
		}

	}

	return configuration
}
