package www

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/rabbit"
	"github.com/getCompassUtils/go_base_frame/api/system/tcp"
	"go_sender/api/includes/controller"
	GlobalIsolation "go_sender/api/includes/type/global_isolation"
	Isolation "go_sender/api/includes/type/isolation"
	"go_sender/api/includes/type/request"
	"go_sender/api/system/grpc"
	"go_sender/api/system/ws"
)

// -------------------------------------------------------
// пакет предназначенный для прослушивания внешненго
// окружения и возвращения результатов
// -------------------------------------------------------

// StartListenRabbit начинаем слушать rabbitMq
func StartListenRabbit(user string, pass string, host string, port string, queuePrefix string, exchangePrefix string, companyEnvList *Isolation.CompanyEnvList) error {

	controllerItem := controller.Make(companyEnvList)

	handleRequest := func(body []byte) []byte {

		// переводим json в структуру для получения метода
		requestItem := request.Request{}
		err := go_base_frame.Json.Unmarshal(body, &requestItem)
		if err != nil {

			log.Warningf("unable to parse method, error: %v", err)
			return []byte{}
		}

		requestItem.Body = body

		return controllerItem.DoStart(requestItem)
	}

	// слушаем rabbitMq
	for i := 0; i < 10; i++ {

		connectionItem, err := rabbit.OpenRabbitConnection("local", user, pass, host, port)
		if err != nil {
			return err
		}

		iterator := i

		queue := fmt.Sprintf("%s_%d", queuePrefix, iterator)
		exchange := fmt.Sprintf("%s_%d", exchangePrefix, iterator)

		go connectionItem.Listen(queue, exchange, handleRequest)
	}
	return nil
}

// StartListenTcp начинаем слушать TCP
func StartListenTcp(companyEnvList *Isolation.CompanyEnvList, port int64) {

	controllerItem := controller.Make(companyEnvList)

	handleRequest := func(body []byte) []byte {

		// переводим json в структуру для получения метода
		requestItem := request.Request{}
		err := go_base_frame.Json.Unmarshal(body, &requestItem)
		if err != nil {

			log.Warningf("unable to parse method, error: %v", err)
			return []byte{}
		}

		requestItem.Body = body

		return controllerItem.DoStart(requestItem)
	}

	go tcp.Listen("0.0.0.0", port, handleRequest)
}

// StartListenGrpc начинаем слушать grpc
func StartListenGrpc(companyEnvList *Isolation.CompanyEnvList, port int64) {

	// конфигурация соединения
	connectionHost := "0.0.0.0"
	go grpc.Listen(connectionHost, port, companyEnvList)
}

// начинаем слушать ws
func StartListenWs(globalIsolation *GlobalIsolation.GlobalIsolation, companyEnvList *Isolation.CompanyEnvList) {

	// слушаем TCP
	go func() {
		ws.Start(globalIsolation, companyEnvList)
	}()
}
