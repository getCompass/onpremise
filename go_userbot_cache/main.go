package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"go_userbot_cache/api/conf"
	"go_userbot_cache/api/observer"
	"go_userbot_cache/www"
	"net/http"
	_ "net/http/pprof"
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

	observer.Work()

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
}
