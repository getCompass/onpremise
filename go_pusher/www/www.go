package www

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/tcp"
	"go_pusher/api/conf"
	handlerTcp "go_pusher/api/includes/handler/tcp"
	"go_pusher/api/system/grpc"
	"go_pusher/api/system/http"
	"go_pusher/api/system/sharding"
	"sync"
)

// -------------------------------------------------------
// пакет предназначенный для прослушивания внешненго
// окружения и возвращения результатов
// -------------------------------------------------------

// переменная предназначеная ожидания рутин
var waitGroupItem = sync.WaitGroup{}

// структура для определения метода в json
type requestStruct struct {
	Method string `json:"method"` // метод
}

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// начинаем слушать rabbitMq
func StartListenRabbit() {

	// добавляем рутину
	waitGroupItem.Add(1)

	// слушаем rabbitMq
	go func() {

		queue := conf.GetConfig().RabbitQueue
		exchange := conf.GetConfig().RabbitExchange

		sharding.Rabbit("local").Listen(queue, exchange, handleRequest)
		waitGroupItem.Done()
	}()
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

// функция для обработки реквеста от tcp и rabbit соединения
func handleRequest(body []byte) []byte {

	// переводим json в структуру для получения метода
	requestItem := requestStruct{}
	err := json.Unmarshal(body, &requestItem)
	if err != nil {

		log.Warningf("unable to parse method, error: %v", err)
		return []byte{}
	}

	// выполняем запрос
	return handlerTcp.DoStart(requestItem.Method, body)
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

// StartListenHttp начинаем слушать http
func StartListenHttp() {

	// добавляем рутину
	waitGroupItem.Add(1)

	// получаем конфиг
	config := conf.GetConfig()

	// конфигурация соединения
	port := config.HttpPort // порт

	// слушаем запросы по протоколу gRPC
	go func() {

		http.Listen(port)
		waitGroupItem.Done()
	}()
}

// ожидаем завершения прослушивания внешнего окружения
func Wait() {
	waitGroupItem.Wait()
}
