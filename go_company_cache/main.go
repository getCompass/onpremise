package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/conf"
	"go_company_cache/api/global_observer"
	GlobalIsolation "go_company_cache/api/includes/type/global_isolation"
	Isolation "go_company_cache/api/includes/type/isolation"
	"go_company_cache/www"
	"net/http"
	_ "net/http/pprof"
	"os"
	"os/signal"
	"runtime"
	"syscall"
)

// основная функция
func main() {

	flags.SetFlags()
	flags.Parse()

	config, err := conf.GetConfig()
	ctx := context.Background()

	// врубаем ендпоинт для профайлера на тестовом
	if config.ServerType == "test-server" {

		go func() {
			err := http.ListenAndServe("0.0.0.0:6060", nil)
			if err != nil {
				return
			}
		}()
	}

	// если не спраисил конфиг то падаем, делать нечего)
	if err != nil {
		panic(err)
	}

	shardingConfig, err := conf.GetShardingConfig()

	// если не спраисил конфиг то падаем, делать нечего)
	if err != nil {
		panic(err)
	}

	companyContextList := Isolation.MakeCompanyEnvList()
	globalIsolation := GlobalIsolation.MakeGlobalIsolationIsolation(config, shardingConfig)

	// устанавливаем уровень логирования микросервиса
	log.SetLoggingLevel(globalIsolation.GetConfig().LoggingLevel)

	// устанавливаем максимальное количество ядер
	numCPU := runtime.NumCPU()
	runtime.GOMAXPROCS(numCPU)

	// запускаем обсервера
	global_observer.WorkGlobalObserver(ctx, globalIsolation, companyContextList)

	// начиаем слушать внешнее окружение
	rabbit := globalIsolation.GetShardingConfig().Rabbit["local"]
	err = www.StartListenRabbit(rabbit.User, rabbit.Pass, rabbit.Host, rabbit.Port, globalIsolation.GetConfig().RabbitQueue, globalIsolation.GetConfig().RabbitExchange, companyContextList)
	if err != nil {
		panic(err)
	}

	www.StartListenTcp(companyContextList, globalIsolation.GetConfig().TcpPort)
	www.StartListenGrpc(companyContextList, globalIsolation.GetConfig().GrpcPort)

	interrupt := make(chan os.Signal, 1)
	signal.Notify(interrupt, os.Interrupt, syscall.SIGTERM)
	select {
	case x := <-interrupt:
		log.Infof("Received a signal.", "signal", x.String())
	case <-ctx.Done():
		log.Infof("Background ctx done")
	}

	log.Infof("Microservice stopped")
}
