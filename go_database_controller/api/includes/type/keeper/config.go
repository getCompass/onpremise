package keeper

import (
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/includes/type/templ"
	"io/ioutil"
	"os"
	"sync"
)

// данные для подстановки в блок демона в шаблоне
type MysqlConfBlockStruct struct {
	Port   int32  `json:"port"`
	DbPath string `json:"db_path"`
}

// данные для подстановки в шаблон
type templateStruct struct {
	RegistryServicePath string
	ConfPath            string `json:"conf_path"`
	MysqlConfBlockList  []*MysqlConfBlockStruct
	DominoId            string `json:"domino_id"`
}

// мьютексы для защиты от гонок; не защищают от косяков,
// которые могут быть вызваны несколькими экземплярами микросервиса
var databaseConfigGenerationMutex sync.Mutex

// GenerateComposeConfig генерируем файл, в котором задаем настройки сервисов базы данных (compose.yaml)
func GenerateComposeConfig() error {

	// блокируем, чтобы конфиги не поломались в результате гонки
	databaseConfigGenerationMutex.Lock()
	defer databaseConfigGenerationMutex.Unlock()

	confPath := flags.ExecutableDir + "/etc/mysql_daemon.cnf"

	// получаем все порты, занятые компаниями, в мире
	portList, err := port_registry.GetAllCompanyPortList()
	if err != nil {
		return err
	}

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
		RegistryServicePath: conf.GetConfig().RegistryServicePath,
		MysqlConfBlockList:  makePortBlockData(portList),
		ConfPath:            confPath,
		DominoId:            conf.GetConfig().DominoId,
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
			Port:   port.Port,
			DbPath: getDataDirPath(port.CompanyId),
		})
	}

	return mysqlConfBlockList
}
