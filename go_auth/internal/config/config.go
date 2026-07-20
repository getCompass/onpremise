package config

import (
	"encoding/base64"
	"encoding/json"
	"fmt"
	"strconv"
)

// структура основного конфига
type ConfigStruct struct {
	ServerTagList    []string `json:"server_tag_list"`
	ServerType       string   `json:"server_type"`
	LoggingLevel     int      `json:"logging_level"`
	ServiceLabel     string   `json:"service_label"`
	AuthSecretKeyB64 string   `json:"auth_secret_key_b64"`
}

// LoadMainConfig загрузить основной конфиг
func LoadMainConfig() (*ConfigStruct, error) {

	loggingLevel, err := strconv.Atoi(getEnv("LOGGING_LEVEL", ""))

	if err != nil {
		return nil, fmt.Errorf("logging level is not int in config. Passed value: %v", loggingLevel)
	}

	var serverTagList []string

	err = json.Unmarshal([]byte(getEnv("SERVER_TAG_LIST", "[]")), &serverTagList)

	if err != nil {
		return nil, fmt.Errorf("server tag list is not string slice. Passed value: %v", getEnv("SERVER_TAG_LIST", "[]"))
	}

	// переводим b64 ключ в URL кодировку
	tempKey, err := base64.StdEncoding.DecodeString(getEnv("AUTH_SECRET_KEY_B64", ""))

	if err != nil {
		return nil, fmt.Errorf("cant decode b64 key. Error: %v", err)
	}

	config := &ConfigStruct{
		ServerType:       getEnv("SERVER_TYPE", ""),
		ServiceLabel:     getEnv("SERVICE_LABEL", ""),
		AuthSecretKeyB64: base64.RawURLEncoding.EncodeToString(tempKey),
		LoggingLevel:     loggingLevel,
		ServerTagList:    serverTagList,
	}

	fmt.Println("Loaded main config")
	return config, nil
}
