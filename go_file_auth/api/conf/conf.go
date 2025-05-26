package conf

import (
	"fmt"
	"github.com/service/go_base_frame"
	"github.com/service/go_base_frame/api/system/flags"
	"os"
	"path"
	"runtime"
)

// ConfigStruct структура конфига
type ConfigStruct struct {
	LoggingLevel int    `json:"logging_level"`
	ServerType   string `json:"server_type"`

	TcpPort  int64 `json:"tcp_port"`
	GrpcPort int64 `json:"grpc_port"`

	SocketKeyMe     string `json:"socket_key_me"`
	TokenEncryptKey string `json:"token_encrypt_key"`

	SessionCacheTTLSec    int64  `json:"session_cache_ttl_sec"`
	TrustedEntrypointList string `json:"trusted_entrypoint_list"`
}

// переменная содержащая конфигурацию
var configuration *ConfigStruct

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// UpdateConfig обновляем конфигурацию
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
		return nil, fmt.Errorf("unable read file conf.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := go_base_frame.Json.NewDecoder(file)
	decodedInfo := &ConfigStruct{}

	err = decoder.Decode(decodedInfo)
	if err != nil {
		return nil, fmt.Errorf("unable decode file conf.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}

// GetConfig получаем конфигурацию custom
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
