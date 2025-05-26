package conf

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"os"
	"path"
	"runtime"
)

// ShardingStruct структура файла sharding.json
type ShardingStruct struct {
	Mysql  MysqlShardingStruct             `json:"mysql,omitempty"`
	Rabbit map[string]RabbitShardingStruct `json:"rabbit,omitempty"`
	Go     map[string]GoShardingStruct     `json:"go,omitempty"`
}

// MysqlShardingStruct структура mysql шардом
type MysqlShardingStruct struct {
	Ssl            bool     `json:"ssl"`
	MaxConnections int      `json:"max_connections"`
	DbList         []string `json:"db_list"`
}

// RabbitShardingStruct структура rabbit шардов
type RabbitShardingStruct struct {
	Host string `json:"host"`
	Port string `json:"port"`
	User string `json:"user"`
	Pass string `json:"pass"`
}

// структура go шардов
type GoShardingStruct struct {
	Host     string `json:"host,omitempty"`
	TcpPort  string `json:"tcp_port,omitempty"`
	GrpcPort string `json:"grpc_port,omitempty"`
}

// структура go ноды
type GoNodeShardingStruct struct {
	Id   int64  `json:"id"`
	Host string `json:"host"`
	Port string `json:"port"`
}

// -------------------------------------------------------
// пакет получения sharding конфигурации
// -------------------------------------------------------

// GetShardingConfig обновляем конфигурацию
func GetShardingConfig() (*ShardingStruct, error) {

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b))
	}

	// сохраняем конфигурацию
	decodedInfo, err := getShardingConfigFromFile(tempPath + "/sharding.json")
	if err != nil {
		return nil, err
	}

	return decodedInfo, nil
}

// получаем конфиг из файла
func getShardingConfigFromFile(path string) (*ShardingStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return nil, fmt.Errorf("unable read file sharding.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := json.NewDecoder(file)
	var decodedInfo ShardingStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return nil, fmt.Errorf("unable decode file sharding.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return &decodedInfo, nil
}
