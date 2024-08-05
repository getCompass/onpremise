package conf

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"os"
	"path"
	"runtime"
	"sync/atomic"
)

// структура конфига
type SocketConfigStruct struct {
	CompassSocketUrl        string            `json:"compass_socket_url"`
	CompassSocketModule     map[string]string `json:"compass_socket_module"`
	PartnerSocketUrl        string            `json:"partner_socket_url"`
	PartnerSocketModule     map[string]string `json:"partner_socket_module"`
	PartnerWebSocketUrl     string            `json:"partner_web_socket_url"`
	PartnerWebSocketModule  map[string]string `json:"partner_web_socket_module"`
	PivotSocketUrl          string            `json:"pivot_socket_url"`
	PivotSocketModule       map[string]string `json:"pivot_socket_module"`
	PremiseSocketUrl        string            `json:"premise_socket_url"`
	PremiseSocketModule     map[string]string `json:"premise_socket_module"`
	BillingSocketUrl        string            `json:"billing_socket_url"`
	BillingSocketModule     map[string]string `json:"billing_socket_module"`
	CrmSocketUrl            string            `json:"crm_socket_url"`
	CrmSocketModule         map[string]string `json:"crm_socket_module"`
	IntegrationSocketUrl    string            `json:"integration_socket_url"`
	IntegrationSocketModule map[string]string `json:"integration_socket_module"`
	JitsiSocketUrl          string            `json:"jitsi_socket_url"`
	JitsiSocketModule       map[string]string `json:"jitsi_socket_module"`
	FederationSocketUrl     string            `json:"federation_socket_url"`
	FederationSocketModule  map[string]string `json:"federation_socket_module"`
}

// переменная содержащая конфигурацию
var socketConfig atomic.Value

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// обновляем конфигурацию
func UpdateSocketConfig() error {

	tempPath := flags.ConfDir
	if tempPath == "" {

		_, b, _, _ := runtime.Caller(0)
		tempPath = path.Join(path.Dir(b))
	}

	// сохраняем конфигурацию
	decodedInfo, err := getSocketConfigFromFile(tempPath + "/socket.json")
	if err != nil {
		return err
	}

	// записываем конфигурацию в хранилище
	socketConfig.Store(decodedInfo)

	return nil
}

// получаем конфиг из файла
func getSocketConfigFromFile(path string) (SocketConfigStruct, error) {

	// открываем файл с конфигурацией
	file, err := os.Open(path)
	if err != nil {
		return SocketConfigStruct{}, fmt.Errorf("unable read file conf.json, error: %v", err)
	}

	// считываем информацию из файла в переменную
	decoder := go_base_frame.Json.NewDecoder(file)
	var decodedInfo SocketConfigStruct
	err = decoder.Decode(&decodedInfo)
	if err != nil {
		return SocketConfigStruct{}, fmt.Errorf("unable decode file conf.json, error: %v", err)
	}

	// закрываем файл
	_ = file.Close()

	return decodedInfo, nil
}

// получаем конфигурацию custom
func GetSocketConfig() SocketConfigStruct {

	// получаем конфиг
	config := socketConfig.Load()

	// если конфига еще нет
	if config == nil {

		// обновляем конфиг
		err := UpdateSocketConfig()
		if err != nil {
			panic(err)
		}

		// подгружаем новый
		config = socketConfig.Load()
	}

	return config.(SocketConfigStruct)
}

// получаем ссылку на socket-endpoint модуля по его названию
func GetModuleSocketUrl(module string) (string, error) {

	// получаем содержимое сокета
	socketConfig := GetSocketConfig()

	// получаем ссылку для модуля из приложения Compass
	compassModuleSocketPath, isCompassModuleExist := socketConfig.CompassSocketModule[module]
	if isCompassModuleExist {
		return socketConfig.CompassSocketUrl + compassModuleSocketPath, nil
	}

	// получаем ссылку для модуля из партнерского ядра
	partnerModuleSocketPath, isPartnerModuleExist := socketConfig.PartnerSocketModule[module]
	if isPartnerModuleExist {
		return socketConfig.PartnerSocketUrl + partnerModuleSocketPath, nil
	}

	// получаем ссылку для модуля из партнерки
	partnerWebModuleSocketPath, isPartnerWebModuleExist := socketConfig.PartnerWebSocketModule[module]
	if isPartnerWebModuleExist {
		return socketConfig.PartnerWebSocketUrl + partnerWebModuleSocketPath, nil
	}

	// получаем ссылку для модуля из пивота
	pivotModuleSocketPath, isPivotModuleExist := socketConfig.PivotSocketModule[module]
	if isPivotModuleExist {
		return socketConfig.PivotSocketUrl + pivotModuleSocketPath, nil
	}

	// получаем ссылку для модуля из premise
	premiseModuleSocketPath, isPremiseModuleExist := socketConfig.PremiseSocketModule[module]
	if isPremiseModuleExist {
		return socketConfig.PremiseSocketUrl + premiseModuleSocketPath, nil
	}

	// получаем ссылку для модуля из биллинга
	billingModuleSocketPath, isBillingModuleExist := socketConfig.BillingSocketModule[module]
	if isBillingModuleExist {
		return socketConfig.BillingSocketUrl + billingModuleSocketPath, nil
	}

	// получаем ссылку для модуля из crm
	crmModuleSocketPath, isCrmModuleExist := socketConfig.CrmSocketModule[module]
	if isCrmModuleExist {
		return socketConfig.CrmSocketUrl + crmModuleSocketPath, nil
	}

	// получаем ссылку
	integrationModuleSocketPath, isIntegrationModuleExist := socketConfig.IntegrationSocketModule[module]
	if isIntegrationModuleExist {
		return socketConfig.IntegrationSocketUrl + integrationModuleSocketPath, nil
	}

	// получаем ссылку для модуля из jitsi
	jitsiModuleSocketPath, isJitsiModuleExist := socketConfig.JitsiSocketModule[module]
	if isJitsiModuleExist {
		return socketConfig.JitsiSocketUrl + jitsiModuleSocketPath, nil
	}

	// получаем ссылку для модуля federation
	federationModuleSocketPath, isFederationModuleExist := socketConfig.FederationSocketModule[module]
	if isFederationModuleExist {
		return socketConfig.FederationSocketUrl + federationModuleSocketPath, nil
	}

	return "", fmt.Errorf("unknown module %s", module)
}
