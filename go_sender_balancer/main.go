package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender_balancer/api/conf"
	"go_sender_balancer/api/includes/gateway/tcp"
	"go_sender_balancer/api/includes/type/balancer"
	"go_sender_balancer/api/observer"
	"go_sender_balancer/www"
	"runtime"
	"sync"
)

const (
	// количество юзеров, онлайн которых можно получить за раз
	limitUserList = 50
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

	// устанавливаем максимальное количество ядер
	numCPU := runtime.NumCPU()
	runtime.GOMAXPROCS(numCPU)

	// запускаем observer
	observer.Work()

	balancer.UpdateConfig()

	// начиаем слушать внешнее окружение
	www.StartListenRabbit()
	www.StartListenGrpc()
	www.StartListenTcp()

	// получаем онлайн пользователей с нод go_sender
	_initializeGetSenderNodeOnlineProcess()
}

// функция инициализирует процесс получения онлайн юзеров с нод go_sender
func _initializeGetSenderNodeOnlineProcess() {

	// генерируем uuid и получаем список активных нод go_sender
	uuid := functions.GenerateUuid()
	senderNodeList := balancer.GetSenderIdList()

	var wg sync.WaitGroup
	for _, nodeId := range senderNodeList {

		// получаем онлайн с каждой ноды параллельно
		wg.Add(1)
		go func(nodeId int64) {

			// получаем список онлайн юзеров и добавляем каждому подключение
			senderNodeOnlineUserList := tcp.GetOnlineUserList(nodeId, uuid, limitUserList)

			for _, userOnlineDevice := range senderNodeOnlineUserList {
				balancer.AddUserConnection(userOnlineDevice.UserId, nodeId)
			}

			wg.Done()
		}(nodeId)
	}
	wg.Wait()
}
