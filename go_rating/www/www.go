package www

import (
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/tcp"
	"go_rating/api/conf"
	"go_rating/api/includes/controller"
	"go_rating/api/includes/type/request"
	"go_rating/api/system/grpc"
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

// StartListenTcp начинаем слушать TCP
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
