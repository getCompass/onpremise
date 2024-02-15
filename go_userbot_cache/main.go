package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"go_userbot_cache/api/observer"
	"go_userbot_cache/www"
)

// основаная функция
func main() {

	// стартуем микросервис
	start()

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
