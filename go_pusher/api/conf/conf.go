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
type ConfigStruct struct {
	LoggingLevel int    `json:"logging_level"`
	ServerType   string `json:"server_type"`
	TcpPort      int64  `json:"tcp_port"`
	GrpcPort     int64  `json:"grpc_port"`
	HttpPort     int64  `json:"http_port"`

	RabbitQueue    string `json:"rabbit_queue"`
	RabbitExchange string `json:"rabbit_exchange"`

	SocketKeyMe string `json:"socket_key_me"`

	// расположение решения saas/premise
	ServerAccommodation string `json:"server_accommodation"`

	PushMonitoringCompanyId int `json:"push_monitoring_company_id"`
}

// переменная содержащая конфигурацию
var configuration atomic.Value

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// обновляем конфигурацию
func UpdateConfig() error {

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b))
	}

	// сохраняем конфигурацию
	decodedInfo, err := getConfigFromFile(tempPath + "/conf.json")
	if err != nil {
		return err
	}

	// записываем конфигурацию в хранилище
	configuration.Store(decodedInfo)

	return nil
}

// получаем конфиг из файла
func getConfigFromFile(path string) (ConfigStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return ConfigStruct{}, fmt.Errorf("unable read file conf.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := json.NewDecoder(file)
	var decodedInfo ConfigStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return ConfigStruct{}, fmt.Errorf("unable decode file conf.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}

// получаем конфигурацию custom
func GetConfig() ConfigStruct {

	// получаем конфиг
	config := configuration.Load()

	// если конфига еще нет
	if config == nil {

		// обновляем конфиг
		err := UpdateConfig()
		if err != nil {
			panic(err)
		}

		// подгружаем новый
		config = configuration.Load()
	}

	return config.(ConfigStruct)
}
