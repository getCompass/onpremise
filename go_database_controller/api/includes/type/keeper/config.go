package keeper

import (
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/db/company"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/includes/type/templ"
	"io/ioutil"
	"os"
	"sync"
)

// данные для подстановки в блок демона в шаблоне
type MysqlConfBlockStruct struct {
	Host   string `json:"host"`
	Port   int32  `json:"port"`
	DbPath string `json:"db_path"`
}

// данные для подстановки в шаблон
type templateStruct struct {
	RegistryServicePath string
	DominoNetwork       string `json:"domino_network"`
	ConfPath            string `json:"conf_path"`
	MysqlConfBlockList  []*MysqlConfBlockStruct
	DominoId            string `json:"domino_id"`
	StackNamePrefix     string `json:"stack_name_prefix"`
}

// мьютексы для защиты от гонок; не защищают от косяков,
// которые могут быть вызваны несколькими экземплярами микросервиса
var databaseConfigGenerationMutex sync.Mutex

// GenerateComposeConfig генерируем файл, в котором задаем настройки сервисов базы данных (compose.yaml)
func GenerateComposeConfig(portList []*port_registry.PortRegistryStruct) error {

	// блокируем, чтобы конфиги не поломались в результате гонки
	databaseConfigGenerationMutex.Lock()
	defer databaseConfigGenerationMutex.Unlock()

	confPath := flags.ExecutableDir + "/etc/mysql_daemon.cnf"

	// открываем шаблон конфига
	bs, err := ioutil.ReadFile(flags.ExecutableDir + "/etc/compose.yaml.tpl")
	if err != nil {
		return err
	}

	// генерим шаблон
	t, err := templ.MakeFromBytes(bs, templ.MissingKeyPolicyError)
	if err != nil {
		return err
	}

	// чистим текущий конфиг mysql
	file, err := os.Create(conf.GetConfig().ComposeFilePath)
	if err != nil {
		return err
	}

	//goland:noinspection GoUnhandledErrorResult
	defer file.Close()

	return templ.WriteToFile(file, t, &templateStruct{
		DominoNetwork:       conf.GetConfig().DominoNetwork,
		RegistryServicePath: conf.GetConfig().RegistryServicePath,
		MysqlConfBlockList:  makePortBlockData(portList),
		ConfPath:            confPath,
		DominoId:            conf.GetConfig().DominoId,
		StackNamePrefix:     conf.GetConfig().StackNamePrefix,
	})
}

// на основе данных портов генерирует список данных
// для генерации блоков конфига для демонов
func makePortBlockData(portList []*port_registry.PortRegistryStruct) []*MysqlConfBlockStruct {

	var mysqlConfBlockList []*MysqlConfBlockStruct

	// заполняем данные для блоков демонов
	for _, port := range portList {

		// проверяем, нужен ли демон для порта
		if !port.NeedDaemon() {
			continue
		}

		// составляем объект, из которого будем составлять конфиг
		mysqlConfBlockList = append(mysqlConfBlockList, &MysqlConfBlockStruct{
			Host:   company.GetCompanyHost(port.Port),
			Port:   port.Port,
			DbPath: getDataDirPath(port.CompanyId),
		})
	}

	return mysqlConfBlockList
}
