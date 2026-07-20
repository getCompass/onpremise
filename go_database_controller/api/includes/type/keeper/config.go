package keeper

import (
	"bytes"
	"crypto/sha1"
	"fmt"
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
	Host                    string `json:"host"`
	Port                    int32  `json:"port"`
	CompanyId               int64  `json:"company_id"`
	DbPath                  string `json:"db_path"`
	InnodbBufferPoolSizeMb  int    `json:"innodb_buffer_pool_size_mb"`
	InnodbThreadConcurrency int    `json:"innodb_thread_concurrency"`
	TableOpenCache          int    `json:"table_open_cache"`
}

// данные для подстановки в шаблон
type templateStruct struct {
	RegistryServicePath                string
	DominoNetwork                      string `json:"domino_network"`
	ConfPath                           string `json:"conf_path"`
	MysqlConfBlockList                 []*MysqlConfBlockStruct
	MysqlConfHash                      string `json:"mysql_conf_hash"`
	MysqlServerId                      int64  `json:"mysql_server_id"`
	MysqlSslPath                       string `json:"mysql_ssl_path"`
	DominoId                           string `json:"domino_id"`
	ServerName                         string `json:"server_name"`
	CompanyDbPath                      string `json:"company_db_path"`
	StackNamePrefix                    string `json:"stack_name_prefix"`
	ServiceLabel                       string `json:"service_label"`
	MonolithMysqlNetwork               string `json:"monolith_mysql_network"`
	MysqlSslPrefix                     string `json:"mysql_ssl_prefix"`
	DominoMysqlInnodbFlushMethod       string `json:"domino_mysql_innodb_flush_method"`
	DominoMysqlInnodbFlushLogAtTimeout int    `json:"domino_mysql_innodb_flush_log_at_timeout"`
}

type resolvedMysqlSettingsStruct struct {
	InnodbBufferPoolSizeMb  int
	InnodbThreadConcurrency int
	TableOpenCache          int
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
	mysqlConfBlockList := makePortBlockData(portList)
	templateData := makeTemplateData(mysqlConfBlockList, confPath)

	mysqlConfBytes, err := renderMysqlConfig(templateData)
	if err != nil {
		return err
	}

	if err = os.WriteFile(confPath, mysqlConfBytes, 0644); err != nil {
		return err
	}

	templateData.MysqlConfHash = fmt.Sprintf("%x", sha1.Sum(mysqlConfBytes))[:12]

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

	return templ.WriteToFile(file, t, templateData)
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
		mysqlSettings := resolveMysqlSettings(port)
		mysqlConfBlockList = append(mysqlConfBlockList, &MysqlConfBlockStruct{
			Host:                    company.GetCompanyHost(port),
			Port:                    port.Port,
			CompanyId:               port.CompanyId,
			DbPath:                  getDataDirPath(port.CompanyId),
			InnodbBufferPoolSizeMb:  mysqlSettings.InnodbBufferPoolSizeMb,
			InnodbThreadConcurrency: mysqlSettings.InnodbThreadConcurrency,
			TableOpenCache:          mysqlSettings.TableOpenCache,
		})
	}

	return mysqlConfBlockList
}

func makeTemplateData(mysqlConfBlockList []*MysqlConfBlockStruct, confPath string) *templateStruct {

	mysqlSslPrefix := "master"
	if conf.GetConfig().MysqlServerId > 1 {
		mysqlSslPrefix = "replica"
	}

	return &templateStruct{
		DominoNetwork:                      conf.GetConfig().DominoNetwork,
		RegistryServicePath:                conf.GetConfig().RegistryServicePath,
		MysqlConfBlockList:                 mysqlConfBlockList,
		ConfPath:                           confPath,
		DominoId:                           conf.GetConfig().DominoId,
		ServerName:                         conf.GetConfig().DominoId,
		CompanyDbPath:                      conf.GetConfig().CompanyDbPath + "/" + conf.GetConfig().DominoId,
		StackNamePrefix:                    conf.GetConfig().StackNamePrefix,
		MysqlServerId:                      conf.GetConfig().MysqlServerId,
		MysqlSslPath:                       conf.GetConfig().MysqlSslPath,
		ServiceLabel:                       conf.GetConfig().ServiceLabel,
		MonolithMysqlNetwork:               conf.GetConfig().MonolithMysqlNetwork,
		MysqlSslPrefix:                     mysqlSslPrefix,
		DominoMysqlInnodbFlushMethod:       conf.GetConfig().DominoMysqlInnodbFlushMethod,
		DominoMysqlInnodbFlushLogAtTimeout: conf.GetConfig().DominoMysqlInnodbFlushLogAtTimeout,
	}
}

func renderMysqlConfig(data *templateStruct) ([]byte, error) {

	tplPath := fmt.Sprintf("%s/etc/tier%d/mysql_daemon.cnf.tpl", flags.ExecutableDir, conf.GetConfig().DominoTier)
	if _, err := os.Stat(tplPath); err != nil {
		tplPath = flags.ExecutableDir + "/etc/tier1/mysql_daemon.cnf.tpl"
	}

	bs, err := ioutil.ReadFile(tplPath)
	if err != nil {
		return nil, err
	}

	t, err := templ.MakeFromBytes(bs, templ.MissingKeyPolicyError)
	if err != nil {
		return nil, err
	}

	buf := new(bytes.Buffer)
	if err = t.Execute(buf, data); err != nil {
		return nil, err
	}

	return buf.Bytes(), nil
}

func resolveMysqlSettings(port *port_registry.PortRegistryStruct) resolvedMysqlSettingsStruct {

	settings := defaultMysqlSettings()
	customSettings := port.GetMysqlSettings()

	if customSettings == nil {
		return settings
	}

	if customSettings.InnodbBufferPoolSizeMb != nil {
		settings.InnodbBufferPoolSizeMb = *customSettings.InnodbBufferPoolSizeMb
	}
	if customSettings.InnodbThreadConcurrency != nil {
		settings.InnodbThreadConcurrency = *customSettings.InnodbThreadConcurrency
	}
	if customSettings.TableOpenCache != nil {
		settings.TableOpenCache = *customSettings.TableOpenCache
	}

	return settings
}

func defaultMysqlSettings() resolvedMysqlSettingsStruct {

	switch conf.GetConfig().DominoTier {

	case 2:
		return resolvedMysqlSettingsStruct{InnodbBufferPoolSizeMb: 32, InnodbThreadConcurrency: 8, TableOpenCache: 400}
	case 3:
		return resolvedMysqlSettingsStruct{InnodbBufferPoolSizeMb: 128, InnodbThreadConcurrency: 8, TableOpenCache: 400}
	default:
		return resolvedMysqlSettingsStruct{InnodbBufferPoolSizeMb: 8, InnodbThreadConcurrency: 4, TableOpenCache: 200}
	}
}
