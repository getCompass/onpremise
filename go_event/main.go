package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"context"
	"go_event/api/conf"
	AppTask "go_event/api/includes/type/async_task"
	AsyncTrap "go_event/api/includes/type/async_trap"
	CompanyConfig "go_event/api/includes/type/company_config"
	Database "go_event/api/includes/type/database"
	"go_event/api/includes/type/event_broker"
	Isolation "go_event/api/includes/type/isolation"
	"go_event/api/includes/type/role"
	SystemBot "go_event/api/includes/type/system_bot"
	"go_event/www"
	"net/http"
	_ "net/http/pprof"
	"os"
	"os/signal"
	"syscall"

	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/server"
)

// основаная функция
func main() {

	// стартуем микросервис
	start()

	// врубаем ендпоинт для профайлера на тестовом
	if conf.GetConfig().ServerType == "test-server" {

		go func() {

			err := http.ListenAndServe("0.0.0.0:6060", nil)
			if err != nil {
				return
			}
		}()
	}

	// ловим комаду sigterm и отписываемся от ребит
	c := make(chan os.Signal)
	signal.Notify(c, os.Interrupt, syscall.SIGTERM)
	go func() {
		<-c
		www.CloseListenServiceRabbit()
		www.CloseListenRabbit()
	}()

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

	// регистрируем модификации пакетов для изоляций
	CompanyConfig.IsolationReg()
	Database.IsolationReg()
	AsyncTrap.IsolationReg()
	AppTask.IsolationReg()
	EventBroker.IsolationReg()

	ctx := context.Background()
	globalCtx, cancelFn := context.WithCancel(ctx)

	// инициализируем глобальную изоляцию для всего модуля
	// все глобальные вызовы будут происходить в ней
	// в дальнейшем этот вызов делать нельзя
	Isolation.MakeGlobalIsolation(globalCtx)

	// запускаем функционал в зависимости от роли сервиса
	role.Begin(globalCtx)

	flags.SetFlags()
	flags.Parse()

	log.SetLoggingLevel(config.LoggingLevel)

	// подписка на свои события
	EventBroker.OnStart(ctx)

	// подписки ботов
	SystemBot.OnStart()

	// начинаем слушать сервисную шину
	www.StartListenServiceRabbit()

	// говорим всем подписчикам, что мы перезапустились
	// и готовы слушать события и новые подписки
	EventBroker.PokeSubscribers()

	// начинаем слушать внешнее окружение
	www.StartListenRabbit()

	// нормально отключаемся, если пришел сигнал
	gracefulStop(cancelFn)

	www.StartListenTcp()
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

		www.CloseListenRabbit()
		www.CloseListenServiceRabbit()
	}()
}
