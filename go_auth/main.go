package main

import (
	"context"
	"go_auth/cmd/auth"
	"go_auth/cmd/profiler"
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

	// запускаем сервис авторизации
	serverErr := auth.Exec(ctx)

	// устанавливаем выключение при получении SIGTERM сигнала
	interrupt := make(chan os.Signal, 1)
	signal.Notify(interrupt, os.Interrupt, syscall.SIGTERM)
	select {
	case x := <-interrupt:
		log.Infof("Received a signal. Signal %s", x.String())
	case <-ctx.Done():
		log.Infof("Background ctx done")
	case err := <-serverErr:
		log.Errorf("Shutdown because of GRPC Server failure: %v", err)
	}

	log.Infof("Microservice stopped")
}
