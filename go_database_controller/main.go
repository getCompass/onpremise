package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/db/domino"
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

	flags.SetFlags()
	flags.Parse()

	// устанавливаем уровень логирования микросервиса
	log.SetLoggingLevel(conf.GetConfig().LoggingLevel)

	// устанавливаем максимальное количество ядер
	numCPU := runtime.NumCPU()
	runtime.GOMAXPROCS(numCPU)

	// накатываем миграции
	err := domino.MigrateInit(flags.ExecutableDir)

	if err != nil {
		panic(err)
	}

	// начинаем слушать внешнее окружение
	www.StartListenGrpc()
}
