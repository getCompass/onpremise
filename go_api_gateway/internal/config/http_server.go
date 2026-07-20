package config

import (
	"encoding/json"
	"fmt"
	"os"
)

// структура конфига http сервера
type HttpServerConfigStruct struct {
	CACertificate       string                    `json:"ca_certificate"`
	Certificate         string                    `json:"certificate"`
	PrivateKey          string                    `json:"private_key"`
	RateLimiterOptions  *RateLimiterOptionsStruct `json:"rate_limiter_options"`
	PrometheusUsername  string                    `json:"prometheus_username"`
	PrometheusPassword  string                    `json:"prometheus_password"`
	MaxIdleConns        int                       `json:"max_idle_conns"`
	MaxIdleConnsPerHost int                       `json:"max_idle_conns_per_host"`
	MaxConnsPerHost     int                       `json:"max_conns_per_host"`
}

// структура конфига лимитера запросов
type RateLimiterOptionsStruct struct {
	LimitRequests      int    `json:"limit_requests"`
	CleanupIntervalSec int    `json:"cleanup_interval_sec"`
	WindowSizeSec      int    `json:"window_size_sec"`
	LimitHeader        string `json:"limit_header"`
	RemainingHeader    string `json:"remaining_header"`
}

const httpServerConfigName = "http_server.json"

// LoadHttpServerConfig инициализировать конфиг http сервера
func LoadHttpServerConfig() (*HttpServerConfigStruct, error) {

	filePath := getFullConfigDir() + httpServerConfigName
	file, err := os.Open(filePath)

	if err != nil {
		return nil, fmt.Errorf("cant load http server json from %s. Error: %v", filePath, err)
	}

	defer file.Close()

	httpServerConfig := &HttpServerConfigStruct{}

	decoder := json.NewDecoder(file)
	err = decoder.Decode(&httpServerConfig)

	if err != nil {
		return nil, fmt.Errorf("cant decode http server json from %s. Error: %v", filePath, err)
	}

	fmt.Println("Loaded http server config")
	return httpServerConfig, nil
}
