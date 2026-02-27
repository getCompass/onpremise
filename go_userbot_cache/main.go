package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"go_userbot_cache/api/conf"
	"go_userbot_cache/api/observer"
	"go_userbot_cache/www"

	"github.com/getCompassUtils/go_base_frame/api/system/server"
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

	config := conf.GetConfig()
	err := server.Init(config.ServerTagList, config.ServiceLabel, config.DominoConfigPath, config.CompaniesRelationshipFile)

	if err != nil {
		panic(err)
	}

	// начиаем слушать внешнее окружение
	www.StartListenRabbit()
	www.StartListenTcp()
	www.StartListenGrpc()
	www.StartListenHttp()
}
