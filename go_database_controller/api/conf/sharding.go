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

// структура файла sharding.json
type ShardingStruct struct {
	Mysql     map[string]MysqlShardingStruct `json:"mysql,omitempty"`
	Manticore ManticoreShardingStruct        `json:"manticore,omitempty"`
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

// структура manticore
type ManticoreShardingStruct struct {
	Host string `json:"host"`
	Port string `json:"port"`
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

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b)) // nosemgrep
	}

	// сохраняем конфигурацию
	decodedInfo, err := getShardingConfigFromFile(tempPath + "/sharding.json")
	if err != nil {
		return err
	}

	shardingConfig.Store(decodedInfo)

	return nil
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
