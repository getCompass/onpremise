package config

import (
	"os"
	"path/filepath"
)

var fullConfigPath string = ""
var configSubPath = "/configs"

// getFullConfigDir получить путь до конфигов
func getFullConfigDir() string {

	if fullConfigPath != "" {
		return fullConfigPath
	}

	ex, err := os.Executable()

	// если не смогли найти путь до исполняемого файла - лучше отвалиться сразу
	if err != nil {
		panic(err)
	}

	fullConfigPath = filepath.Dir(ex) + configSubPath + "/"
	return fullConfigPath
}

// getEnv получить переменную из env
func getEnv(key string, defaultValue string) string {

	value, found := os.LookupEnv(key)

	if !found {
		return defaultValue
	}

	return value
}
