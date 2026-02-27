package main

// -------------------------------------------------------
// основной пакет, который компилируется при старте
// -------------------------------------------------------

import (
	"context"
	"go_activity/api/conf"
	"go_activity/api/observer"
	"go_activity/www"
	"net/http"
	_ "net/http/pprof"
	"os"
	"os/signal"

	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/server"
)

// основанная функция
func main() {

	// стартуем микросервис
	start()

	// врубаем endpoint для profiler на тестовом
	if conf.GetConfig().ServerType == "test-server" {

		go func() {

			err := http.ListenAndServe("0.0.0.0:6060", nil) // nosemgrep
			if err != nil {
				return
			}
		}()
	}

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

	log.SetLoggingLevel(conf.GetConfig().LoggingLevel)

	globalCtx, cancelFn := context.WithCancel(context.Background())
	observer.Work(globalCtx)

	// нормально отключаемся, если пришел сигнал
	gracefulStop(cancelFn)

	// начинаем слушать внешнее окружение
	www.StartListenHttp()
	www.StartListenGrpc()

}

// нормальное завершение работы
func gracefulStop(cancel context.CancelFunc) {

	c := make(chan os.Signal, 1)
	signal.Notify(c, os.Interrupt)

	go func() {

		osCall := <-c
		log.Warningf("received stop signal:%+v", osCall)

		cancel()
	}()
}
