package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/server"
	"go_sender/api/conf"
	GlobalIsolation "go_sender/api/includes/type/global_isolation"
	Isolation "go_sender/api/includes/type/isolation"
	"go_sender/api/includes/type/role"
	WsMethodConfig "go_sender/api/includes/type/ws/event/method_config"
	"go_sender/www"
	"net/http"
	_ "net/http/pprof"
	"os"
	"os/signal"
	"runtime"
	"syscall"
)

// основаная функция
func main() {

	flags.SetFlags()
	flags.Parse()

	ctx := context.Background()
	config, err := conf.GetConfig()

	if err != nil {
		panic(err)
	}

	err = server.Init(config.ServerTagList)

	if err != nil {
		panic(err)
	}

	// врубаем ендпоинт для профайлера на тестовом
	if config.ServerType == "test-server" {

		go func() {

			err := http.ListenAndServe("0.0.0.0:6060", nil)
			if err != nil {
				return
			}
		}()
	}

	shardingConfig, err := conf.GetShardingConfig()

	// если не спарсил конфиг то падаем, делать нечего)
	if err != nil {
		panic(err)
	}

	// инициализируем хранилище версионных конфигов
	WsMethodConfig.Init()

	companyContextList := Isolation.MakeCompanyEnvList()
	globalIsolation := GlobalIsolation.MakeGlobalIsolationIsolation(config, shardingConfig)

	// устанавливаем уровень логирования микросервиса
	log.SetLoggingLevel(globalIsolation.GetConfig().LoggingLevel)

	// устанавливаем максимальное количество ядер
	numCPU := runtime.NumCPU()
	runtime.GOMAXPROCS(numCPU)

	// запускаем обсерверов
	role.Begin(ctx, globalIsolation, companyContextList)

	// начиаем слушать внешнее окружение
	www.StartListenWs(globalIsolation, companyContextList)

	// начиаем слушать внешнее окружение
	rabbit := globalIsolation.GetShardingConfig().Rabbit["local"]
	err = www.StartListenRabbit(rabbit.User, rabbit.Pass, rabbit.Host, rabbit.Port, globalIsolation.GetConfig().RabbitQueue, globalIsolation.GetConfig().RabbitExchange, companyContextList)
	if err != nil {
		panic(err)
	}

	www.StartListenTcp(companyContextList, globalIsolation.GetConfig().TcpPort)
	www.StartListenGrpc(companyContextList, globalIsolation.GetConfig().GrpcPort)

	globalIsolation.BalancerConn.ClearConnectionsInBalancer()

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
