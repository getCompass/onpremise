package conf

import (
	"encoding/json"
	"fmt"
	"os"
	"path"
	"runtime"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/flags"
)

// ConfigStruct структура конфига
type ConfigStruct struct {
	LoggingLevel int    `json:"logging_level"`
	ServerType   string `json:"server_type"`

	SocketKeyMe string `json:"socket_key_me"`
	TcpPort     int64  `json:"tcp_port"`
	GrpcPort    int64  `json:"grpc_port"`

	RabbitQueue    string `json:"rabbit_queue"`
	RabbitExchange string `json:"rabbit_exchange"`
	CompanyId      int64  `json:"company_id"`

	NoticeBotUserId int64  `json:"notice_bot_user_id"`
	NoticeBotToken  string `json:"notice_bot_token"`
	NoticeEndpoint  string `json:"notice_endpoint"`
	NoticeChannel   string `json:"notice_channel"`
	ServerName      string `json:"server_name"`

	CapacityLimit    int    `json:"capacity_limit"`
	WorldConfigPath  string `json:"world_config_path"`
	DominoConfigPath string `json:"domino_config_path"`

	ServiceLabel              string `json:"service_label"`
	CompaniesRelationshipFile string `json:"companies_relationship_file"`

	ServerTagList                       []string      `json:"server_tag_list"`
	ForceCompanyConfigUpdateIntervalSec time.Duration `json:"force_company_config_update_interval_sec"`
	IsIsolationCreateRepeat             bool          `json:"is_isolation_create_repeat"`

	EncryptIvAction         string `json:"encrypt_iv_action"`
	EncryptPassphraseAction string `json:"encrypt_passphrase_action"`
}

var config *ConfigStruct = nil

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// GetConfig получаем конфигурацию custom
func GetConfig() (*ConfigStruct, error) {

	if config != nil {
		return config, nil
	}

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b)) // nosemgrep
	}

	// сохраняем конфигурацию
	decodedInfo, err := getConfigFromFile(tempPath + "/conf.json")
	if err != nil {
		return nil, err
	}

	config = decodedInfo

	return decodedInfo, nil
}

// получаем конфиг из файла
func getConfigFromFile(path string) (*ConfigStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return nil, fmt.Errorf("unable read file conf.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := json.NewDecoder(file)
	var decodedInfo ConfigStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return nil, fmt.Errorf("unable decode file conf.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return &decodedInfo, nil
}
