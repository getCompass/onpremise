package www

import (
	"go_activity/api/conf"
	"go_activity/api/system/grpc"
	"go_activity/api/system/http"
	"sync"
)

// -------------------------------------------------------
// пакет предназначенный для прослушивания внешнего
// окружения и возвращения результатов
// -------------------------------------------------------

// переменная для ожидания рутин
var waitGroupItem = sync.WaitGroup{}

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// StartListenGrpc начинаем слушать grpc
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
	port := config.TcpPort // порт

	// слушаем запросы по протоколу gRPC
	go func() {

		http.Listen(port)
		waitGroupItem.Done()
	}()
}

// Wait ожидаем завершения прослушивания внешнего окружения
func Wait() {
	waitGroupItem.Wait()
}
