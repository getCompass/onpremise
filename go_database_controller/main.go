package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/server"

	"go_database_controller/api/conf"
	"go_database_controller/www"
	"runtime"
)

// основаная функция
func main() {

	// стартуем микросервис
	start()

	// не завершаем работу микросервиса, пока слушается внешнее окружение
	www.Wait()
}

// стартуем микросервис
func start() {

	config := conf.GetConfig()
	err := server.Init(config.ServerTagList, config.ServiceLabel, config.DominoConfigPath, config.CompaniesRelationshipFile)

	if err != nil {
		panic(err)
	}

	flags.SetFlags()
	flags.Parse()

	// устанавливаем уровень логирования микросервиса
	log.SetLoggingLevel(conf.GetConfig().LoggingLevel)

	// устанавливаем максимальное количество ядер
	numCPU := runtime.NumCPU()
	runtime.GOMAXPROCS(numCPU)

	// начинаем слушать внешнее окружение
	www.StartListenGrpc()
}
