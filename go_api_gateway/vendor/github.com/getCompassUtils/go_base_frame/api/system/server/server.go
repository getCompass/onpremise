package server

import (
	"encoding/json"
	"fmt"
	"unicode/utf8"

	"github.com/getCompassUtils/go_base_frame/api/system/fileutils"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
)

const (
	devTag        = "dev"
	ciTag         = "ci"
	masterTag     = "master"
	stageTag      = "stage"
	productionTag = "production"

	onPremiseTag = "on-premise"
	saasTag      = "saas"

	localTag       = "local"
	integrationTag = "integration"
)

// тип окружения
var environmentGroupTagList = []string{
	devTag,
	ciTag,
	masterTag,
	stageTag,
	productionTag,
}

// тип продукта
var productGroupTagList = []string{
	onPremiseTag,
	saasTag,
}

// мапа с тегами серверов
var serverTagMap map[string]bool

// лейбл сервера
var serviceLabel string

// путь до конфига
var dominoConfigPath string
var companiesRelationshipFileName string

// инициализировать теги сервера
func Init(tl []string, sl string, dcp string, crfn string) error {

	if len(tl) < 2 {
		return fmt.Errorf("server tag list is invalid")
	}

	if len(functions.ListIntersection(tl, environmentGroupTagList)) != 1 {
		return fmt.Errorf("incorrect environment group tag")
	}

	if len(functions.ListIntersection(tl, productGroupTagList)) != 1 {
		return fmt.Errorf("incorrect product group tag")
	}

	serverTagMap = make(map[string]bool)
	for _, value := range tl {
		serverTagMap[value] = true
	}
	serviceLabel = sl
	dominoConfigPath = dcp
	companiesRelationshipFileName = crfn

	return nil
}

// явлеяется ли сервером для разработки
func IsDev() bool {

	return hasTag(devTag)
}

// является ли CI сервером
func IsCi() bool {

	return hasTag(ciTag)
}

// является ли stage сервером
func IsStage() bool {

	return hasTag(stageTag)
}

// является ли onpremise сервером
func IsOnPremise() bool {

	return hasTag(onPremiseTag)
}

// являетя ли saas сервером
func IsSaas() bool {

	return hasTag(saasTag)
}

// является ли production сервером
func IsProduction() bool {

	return hasTag(productionTag)
}

// является ли локальным окружением
func IsLocal() bool {

	return hasTag(localTag)
}

// добавлена ли поддержка интеграций
func IsIntegration() bool {

	return hasTag(integrationTag)
}

// является ли мастер окружением
func IsMaster() bool {

	return hasTag(masterTag)
}

// является ли одним из тестовых серверов
func IsTest() bool {

	return IsDev() || IsCi() || IsMaster() || IsLocal()
}

// есть ли тег у сервера
func hasTag(tag string) bool {

	if _, ok := serverTagMap[tag]; ok {
		return true
	}

	return false
}

// возвращаем serviceLabel
func GetServiceLabel() string {
	return serviceLabel
}

type ServiceConfig struct {
	Master bool `json:"master"`
}

// проверяем резервный ли сервер
func IsReserveServer() bool {
	if !IsOnPremise() {
		return false
	}

	if utf8.RuneCountInString(serviceLabel) == 0 {
		return false
	}

	if companiesRelationshipFileName == "" {
		return false
	}

	companiesRelationshipFile, err := fileutils.Init(dominoConfigPath, companiesRelationshipFileName)
	if err != nil {
		return false
	}

	if !fileutils.Exists(companiesRelationshipFile.GetPath()) {
		return false
	}

	strData, err := companiesRelationshipFile.Read()
	if err != nil {
		return false
	}

	data := []byte(strData)

	// было: var cfg map[string]map[string]bool
	var cfg map[string]ServiceConfig

	if err = json.Unmarshal(data, &cfg); err != nil {
		return false
	}

	serviceCfg, ok := cfg[serviceLabel]
	if !ok {
		return true
	}

	master := serviceCfg.Master

	if !master {
		return true
	}

	return false
}
