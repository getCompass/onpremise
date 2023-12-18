package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/db/domino"
	"go_database_controller/www"
	"net/http"
	_ "net/http/pprof"
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

	// врубаем ендпоинт для профайлера на тестовом
	if conf.GetConfig().ServerType == "test-server" || conf.GetConfig().ServerType == "local" {

		profilerPort := conf.GetConfig().ProfilerPort
		go func() {

			err := http.ListenAndServe(fmt.Sprintf("%s:%d", "127.0.0.1", profilerPort), nil)
			if err != nil {
				return
			}
		}()
	}

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
