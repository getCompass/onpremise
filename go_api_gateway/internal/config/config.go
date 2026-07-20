package config

import (
	"encoding/json"
	"fmt"
	"strconv"
)

// структура основного конфига
type ConfigStruct struct {
	ServerTagList []string `json:"server_tag_list"`
	ServerType    string   `json:"server_type"`
	ServiceLabel  string   `json:"service_label"`
	LoggingLevel  int      `json:"logging_level"`
}

// LoadMainConfig инициализировать основной конфиг
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

	config := &ConfigStruct{
		ServerType:    getEnv("SERVER_TYPE", ""),
		ServiceLabel:  getEnv("SERVICE_LABEL", ""),
		LoggingLevel:  loggingLevel,
		ServerTagList: serverTagList,
	}

	fmt.Println("Loaded main config")
	return config, nil
}
