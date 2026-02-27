package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/server"
	"go_rating/api/conf"
	CompanyConfig "go_rating/api/includes/type/company_config"
	Database "go_rating/api/includes/type/database"
	Isolation "go_rating/api/includes/type/isolation"
	"go_rating/api/includes/type/rating/collecting/pivot"
	"go_rating/api/includes/type/rating/scrapping/user_action"
	"go_rating/api/includes/type/rating/scrapping/user_answer_time"
	"go_rating/api/includes/type/rating/scrapping/user_screen_time"
	"go_rating/api/observer"
	"go_rating/www"
	_ "net/http/pprof"
	"os"
	"os/signal"
	"runtime"
)

// основная функция
func main() {

	// инициализируем пакеты
	CompanyConfig.IsolationReg()
	Database.IsolationReg()
	pivot.IsolationReg()
	user_screen_time.IsolationReg()
	user_action.IsolationReg()
	user_answer_time.IsolationReg()

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

	ctx := context.Background()
	globalCtx, cancelFn := context.WithCancel(ctx)

	// инициализируем глобальную изоляцию для всего модуля
	// все глобальные вызовы будут происходить в ней
	// в дальнейшем этот вызов делать нельзя
	Isolation.MakeGlobalIsolation(globalCtx)

	// подгружаем конфиги
	CompanyConfig.UpdateWorldConfig()

	flags.SetFlags()
	flags.Parse()

	log.SetLoggingLevel(conf.GetConfig().LoggingLevel)

	// устанавливаем максимальное количество ядер
	numCPU := runtime.NumCPU()
	runtime.GOMAXPROCS(numCPU)

	// нормально отключаемся, если пришел сигнал
	gracefulStop(cancelFn)

	www.StartListenTcp()
	www.StartListenGrpc()

	observer.Work(globalCtx)
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
