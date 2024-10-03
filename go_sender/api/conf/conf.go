package conf

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"os"
	"path"
	"runtime"
	"time"
)

// структура конфига
type ConfigStruct struct {
	LoggingLevel  int    `json:"logging_level"`
	ServerType    string `json:"server_type"`
	CurrentServer string `json:"current_server"`
	Role          string `json:"role"`

	TcpPort       int64 `json:"tcp_port"`
	GrpcPort      int64 `json:"grpc_port"`
	NodeId        int64 `json:"node_id"`
	IsHasBalancer bool  `json:"is_has_balancer"`

	RabbitQueue    string `json:"rabbit_queue"`
	RabbitExchange string `json:"rabbit_exchange"`

	RabbitSenderBalancerQueue    string `json:"rabbit_sender_balancer_queue"`
	RabbitSenderBalancerExchange string `json:"rabbit_sender_balancer_exchange"`

	CustomSalt string `json:"custom_salt"`

	WebsocketPort         int    `json:"websocket_port"`
	WebsocketIsTls        bool   `json:"websocket_tls"`
	WebsocketCertFilePath string `json:"websocket_cert_file_path"`
	WebsocketKeyFilePath  string `json:"websocket_key_file_path"`
	WebsocketPassPhrase   string `json:"websocket_passphrase"`

	ListenAddress string `json:"listen_address"`
	SocketKeyMe   string `json:"socket_key_me"`
	CaCertificate string `json:"ca_certificate"`

	CapacityLimit   int    `json:"capacity_limit"`
	WorldConfigPath string `json:"world_config_path"`

	ServerTagList                       []string      `json:"server_tag_list"`
	ForceCompanyConfigUpdateIntervalSec time.Duration `json:"force_company_config_update_interval_sec"`
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
