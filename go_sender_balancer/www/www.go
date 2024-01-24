package www

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/tcp"
	"go_sender_balancer/api/conf"
	"go_sender_balancer/api/includes/controller"
	"go_sender_balancer/api/system/grpc"
	"go_sender_balancer/api/system/sharding"
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
	queue := conf.GetConfig().RabbitQueue
	exchange := conf.GetConfig().RabbitExchange

	// слушаем rabbitMq
	go func() {

		sharding.Rabbit("local").Listen(queue, exchange, handleRequest)
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

// начинаем слушать TCP
func StartListenTcp() {

	// добавляем рутину
	waitGroupItem.Add(1)

	// получаем конфиг
	config := conf.GetConfig()

	// конфигурация соединения
	connectionHost := "0.0.0.0" // хост
	port := config.TcpPort      // порт

	// слушаем TCP
	go func() {

		tcp.Listen(connectionHost, port, handleRequest)
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
	return controller.DoStart(requestItem.Method, body)
}

// ожидаем завершения прослушивания внешнего окружения
func Wait() {
	waitGroupItem.Wait()
}
