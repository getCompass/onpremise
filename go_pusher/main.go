package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pusher/api/conf"
	"go_pusher/api/includes/type/device"
	"go_pusher/api/observer"
	"go_pusher/www"
	"net/http"
	_ "net/http/pprof"
)

// сначало парсим флаги
func init() {

	flags.SetFlags()
	flags.Parse()
}

// основаная функция
func main() {

	observer.Work(context.Background())

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

	// не завершаем работу микросервиса, пока слушается внешнее окружение
	www.Wait()
}

// стартуем микросервис
func start() {

	// начиаем слушать внешнее окружение
	www.StartListenRabbit()
	www.StartListenTcp()
	www.StartListenGrpc()
	www.StartListenHttp()

	// запускаем модель для актуализации токенов пользователя
	device.Init(context.Background())

	log.SetLoggingLevel(conf.GetConfig().LoggingLevel)
}
