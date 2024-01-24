package www

import (
	"go_database_controller/api/conf"
	"go_database_controller/api/system/grpc"
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

// ожидаем завершения прослушивания внешнего окружения
func Wait() {
	waitGroupItem.Wait()
}
