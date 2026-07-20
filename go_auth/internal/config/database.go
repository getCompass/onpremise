package config

import (
	"fmt"
	"strconv"
)

// структура конфига базы данных
type DatabaseConfigStruct struct {
	IsSslEnabled   bool   `json:"is_ssl_enabled"`
	Port           int    `json:"port"`
	MaxConnections int    `json:"max_connections"`
	Host           string `json:"host"`
	User           string `json:"user"`
	Password       string `json:"password"`
}

// LoadDatabaseConfig загрузить конфиг базы данных
func LoadDatabaseConfig() (*DatabaseConfigStruct, error) {

	port, err := strconv.Atoi(getEnv("MYSQL_PORT", ""))

	if err != nil {
		return nil, fmt.Errorf("port is not int in database config. Passed value: %v", port)
	}

	databaseConfig := &DatabaseConfigStruct{
		Host:           getEnv("MYSQL_HOST", ""),
		User:           getEnv("MYSQL_USER", ""),
		Password:       getEnv("MYSQL_PASS", ""),
		Port:           port,
		MaxConnections: 100,
		IsSslEnabled:   false,
	}

	fmt.Println("Loaded database config")
	return databaseConfig, nil
}
