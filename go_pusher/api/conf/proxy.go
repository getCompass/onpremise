package conf

import (
	"encoding/json"
	"fmt"
	"os"
	"sync/atomic"

	"github.com/getCompassUtils/go_base_frame/api/system/server"
)

// структура конфига
type ProxyConfigStruct struct {
	ProxyProtocol string `json:"protocol"`
	ProxyHost     string `json:"host"`
	ProxyPort     int    `json:"port"`
	ProxyUsername string `json:"username"`
	ProxyPassword string `json:"password"`
}

// переменная содержащая конфигурацию
var proxyConfig atomic.Pointer[ProxyConfigStruct]

const proxyConfigName = "proxy.json"

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// LoadProxyConfig инициализировать конфиг прокси
func LoadProxyConfig() {

	filePath := getFullConfigDir() + proxyConfigName
	file, err := os.Open(filePath)

	if err != nil {
		panic(err)
	}

	defer file.Close()

	config := ProxyConfigStruct{}

	decoder := json.NewDecoder(file)
	err = decoder.Decode(&config)

	if err != nil {
		panic(err)
	}

	proxyConfig.Store(&config)
	fmt.Println("Loaded proxy config")
}

// OverrideProxyConfig переопределить конфиг solution сервера
func OverrideProxyConfig(protocol string, host string, port int, username string, password string) {

	// только для тестового сервера
	if !server.IsTest() {
		return
	}

	config := ProxyConfigStruct{
		ProxyProtocol: protocol,
		ProxyHost:     host,
		ProxyPort:     port,
		ProxyUsername: username,
		ProxyPassword: password,
	}

	proxyConfig.Store(&config)

}

// ResetProxyConfig сбросить конфигурацию прокси
func ResetProxyConfig() {

	// только для тестового сервера
	if !server.IsTest() {
		return
	}

	proxyConfig.Store(nil)
}

// GetProxyConfig получаем конфигурацию прокси
func GetProxyConfig() ProxyConfigStruct {

	// получаем конфиг
	config := proxyConfig.Load()

	// если конфига еще нет
	if config == nil {

		// обновляем конфиг
		LoadProxyConfig()

		// подгружаем новый
		config = proxyConfig.Load()
	}

	return *config
}
