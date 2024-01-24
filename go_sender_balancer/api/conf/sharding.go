package conf

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"os"
	"sync/atomic"
)

// структура файла sharding.json
type ShardingStruct struct {
	Mysql  map[string]MysqlShardingStruct  `json:"mysql,omitempty"`
	Rabbit map[string]RabbitShardingStruct `json:"rabbit,omitempty"`
	Go     map[string]GoShardingStruct     `json:"go,omitempty"`
}

// структура mysql шардом
type MysqlShardingStruct struct {
	Db    string `json:"db"`
	Mysql struct {
		Host           string `json:"host"`
		User           string `json:"user"`
		Pass           string `json:"pass"`
		Ssl            bool   `json:"ssl"`
		MaxConnections int    `json:"max_connections"`
	} `json:"mysql"`
	Schemas map[string]string `json:"schemas"`
}

// структура rabbit шардов
type RabbitShardingStruct struct {
	Host string `json:"host"`
	Port string `json:"port"`
	User string `json:"user"`
	Pass string `json:"pass"`
}

// структура go шардов
type GoShardingStruct struct {
	Host  string                 `json:"host,omitempty"`
	Port  string                 `json:"port,omitempty"`
	Nodes []GoNodeShardingStruct `json:"nodes,omitempty"`
}

// структура go ноды
type GoNodeShardingStruct struct {
	Id    int64  `json:"id"`
	Host  string `json:"host"`
	Port  string `json:"port"`
	Limit int64  `json:"limit"`
}

// -------------------------------------------------------
// пакет получения sharding конфигурации
// -------------------------------------------------------

// переменная содержащая конфигурацию
var shardingConfig atomic.Value

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// обновляем конфигурацию
func UpdateShardingConfig() error {

	// сохраняем конфигурацию
	decodedInfo, err := getShardingConfigFromFile(flags.ConfDir + "/sharding.json")
	if err != nil {
		return err
	}

	shardingConfig.Store(decodedInfo)

	return nil
}

// добавляем ноду go_sender
func AddSenderNode(id int64, port int64, limit int64) {

	// сохраняем конфигурацию
	decodedInfo, err := getShardingConfigFromFile(flags.ConfDir + "/sharding.json")
	if err != nil {
		return
	}

	nodes := decodedInfo.Go["sender"]
	nodes.Nodes = append(nodes.Nodes, GoNodeShardingStruct{
		Id:    id,
		Host:  "0.0.0.0",
		Port:  fmt.Sprintf("%d", port),
		Limit: limit,
	})
	decodedInfo.Go["sender"] = nodes
	shardingConfig.Store(decodedInfo)
}

// получаем конфиг из файла
func getShardingConfigFromFile(path string) (ShardingStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return ShardingStruct{}, fmt.Errorf("unable read file sharding.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := json.NewDecoder(file)
	var decodedInfo ShardingStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return ShardingStruct{}, fmt.Errorf("unable decode file sharding.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}

// получаем конфигурацию custom
func GetShardingConfig() ShardingStruct {

	config := shardingConfig.Load()
	if config == nil {

		err := UpdateShardingConfig()
		if err != nil {
			panic(err)
		}

		config = shardingConfig.Load()
	}

	return config.(ShardingStruct)
}
