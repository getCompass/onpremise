package www

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/tcp"
	"go_event/api/conf"
	"go_event/api/includes/controller"
	"go_event/api/includes/type/request"
	"go_event/api/system/grpc"
	"go_event/api/system/sharding"
	"sync"
)

// -------------------------------------------------------
// пакет предназначенный для прослушивания внешненго
// окружения и возвращения результатов
// -------------------------------------------------------

// переменная предназначеная ожидания рутин
var waitGroupItem = sync.WaitGroup{}

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// начинаем слушать основную очередь rabbitMq. Через неё приходят обычные события
func StartListenRabbit() {

	// добавляем рутину
	waitGroupItem.Add(1)

	for i := 0; i < 10; i++ {

		// слушаем rabbitMq
		go func(i int) {

			queue := fmt.Sprintf("%s_%d", conf.GetConfig().RabbitQueue, i)
			exchange := fmt.Sprintf("%s_%d", conf.GetConfig().RabbitExchange, i)

			sharding.Rabbit("local").Listen(queue, exchange, handleRequest)
			waitGroupItem.Done()
		}(i)
	}
}

// начинаем слушать сервисную очередь rabbitMq. Через неё приходят события на добавление подписок
func StartListenServiceRabbit() {

	// добавляем рутину
	waitGroupItem.Add(1)

	for i := 0; i < 10; i++ {

		// слушаем rabbitMq
		go func(i int) {

			queue := fmt.Sprintf("%s_%d", conf.GetEventConf().EventService.Queue, i)
			exchange := fmt.Sprintf("%s_%d", conf.GetEventConf().EventService.Exchange, i)

			sharding.Rabbit("service").Listen(queue, exchange, handleRequest)
			waitGroupItem.Done()
		}(i)
	}
}

// перестаем слушать основную очередь rabbitMq. Через неё приходят обычные события
func CloseListenRabbit() {

	sharding.Rabbit("local").CloseAll()
}

// перестаем слушать сервисную очередь rabbitMq Через неё приходят события на добавление подписок
func CloseListenServiceRabbit() {

	sharding.Rabbit("service").CloseAll()
}

// начинаем слушать TCP
func StartListenTcp() {

	// добавляем рутину
	waitGroupItem.Add(1)
	port := conf.GetConfig().TcpPort

	// слушаем TCP
	go func() {

		tcp.Listen("0.0.0.0", port, handleRequest)
		waitGroupItem.Done()
	}()
}

// начинаем слушать grpc
func StartListenGrpc() {

	// добавляем рутину
	waitGroupItem.Add(1)

	// получаем конфиг
	config := conf.GetConfig()

	// конфигурация соединения
	connectionHost := "0.0.0.0" // хост
	port := config.GrpcPort     // порт

	// слушаем запросы по протоколу gRPC
	go func() {

		grpc.Listen(connectionHost, port)
		waitGroupItem.Done()
	}()
}

// функция для обработки реквеста от tcp и rabbit соединения
func handleRequest(body []byte) []byte {

	// переводим json в структуру для получения метода
	requestItem := request.Request{}
	err := go_base_frame.Json.Unmarshal(body, &requestItem)
	if err != nil {

		log.Warningf("unable to parse method, error: %v", err)
		return []byte{}
	}

	requestItem.Body = body

	// выполняем запрос
	return controller.DoStart(requestItem)
}

// ожидаем завершения прослушивания внешнего окружения
func Wait() {
	waitGroupItem.Wait()
}
