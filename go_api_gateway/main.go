package main

import (
	"context"
	"go_api_gateway/cmd/api_gateway"
	"go_api_gateway/cmd/profiler"
	"os"
	"os/signal"
	"runtime"
	"syscall"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
)

func main() {

	ctx := context.Background()

	// устанавливаем максимальное количество ядер
	numCPU := runtime.NumCPU()
	runtime.GOMAXPROCS(numCPU)

	// запускаем профайлер
	profiler.Exec()

	// запускаем гейтвей и получаем в ответ канал, куда прилетит ошибка с сервера
	serverErr := api_gateway.Exec()

	// устанавливаем выключение при получении SIGTERM сигнала
	interrupt := make(chan os.Signal, 1)
	signal.Notify(interrupt, os.Interrupt, syscall.SIGTERM)
	select {
	case x := <-interrupt:
		log.Infof("Received a signal. Signal %s", x.String())
	case <-ctx.Done():
		log.Infof("Background ctx done")
	case err := <-serverErr:
		log.Errorf("Server shutdown with error %v", err)
	}

	log.Infof("Microservice stopped")
}
