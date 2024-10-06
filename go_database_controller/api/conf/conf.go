package conf

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"os"
	"sync/atomic"
)

// структура конфига
type ConfigStruct struct {
	ServerType                         string `json:"server_type"` // тип сервера
	LoggingLevel                       int    `json:"logging_level"`
	ProfilerPort                       int    `json:"profiler_port"` // порт профайлера
	GrpcPort                           int64  `json:"grpc_port"`
	EncryptKey                         string `json:"encrypt_key"`
	EncryptIv                          string `json:"encrypt_iv"`
	MysqlConfPath                      string `json:"mysql_conf_path"`
	ComposeFilePath                    string `json:"compose_file_path"`
	RegistryServicePath                string `json:"registry_service_path"`
	CompanyDbPath                      string `json:"company_db_path"`
	DominoId                           string `json:"domino_id"`
	MysqlCompanyHost                   string `json:"mysql_company_host"`
	DominoTier                         int64  `json:"domino_tier"`
	StackNamePrefix                    string `json:"stack_name_prefix"`
	BackupUser                         string `json:"backup_user"`
	BackupUserPassword                 string `json:"backup_user_password"`
	BackupArchivePassword              string `json:"backup_archive_password"`
	BackupPath                         string `json:"backup_path"`
	RelocationSourceDumpPath           string `json:"relocation_source_dump_path"`              // директория, куда будет сниматься дамп при переезде на локальной машине
	RelocationTargetDumpPath           string `json:"relocation_target_dump_path"`              // директория, в которую будет копироваться при переезде дамп на удаленной машине
	DominoUser                         string `json:"domino_user"`                              // пользователь, от которого будет выполняться общение между домино
	BackupSshFileKeyFilePath           string `json:"backup_ssh_key_file_path"`                 // имя файла-ключа, который используется для общение между домино при снятии бекапов/дампов
	DominoMysqlInnodbFlushMethod       string `json:"domino_mysql_innodb_flush_method"`         // параметр innodb_flush_method устанавливаемый в mysql_daemon.cnf.tpl каждого тира
	DominoMysqlInnodbFlushLogAtTimeout int    `json:"domino_mysql_innodb_flush_log_at_timeout"` // параметр mysql_innodb_flush_log_at_timeout устанавливаемый в mysql_daemon.cnf.tpl каждого тира
	MysqlHostCertificate               string `json:"mysql_host_certificate"`
	MysqlHostPrivateKey                string `json:"mysql_host_private_key"`
	DominoSecretKey                    string `json:"domino_secret_key"`
	DominoNetwork                      string `json:"domino_network"`
	DatabaseDriver                     string `json:"database_driver"`
}

// переменная содержащая конфигурацию
var configuration atomic.Value

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// обновляем конфигурацию
func UpdateConfig() error {

	// сохраняем конфигурацию
	decodedInfo, err := getConfigFromFile(flags.ConfDir + "/conf.json")
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
