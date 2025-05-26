package main

// -------------------------------------------------------
// основной пакет, который компилится при старте
// -------------------------------------------------------

import (
	"context"
	"github.com/service/go_base_frame/api/system/flags"
	"github.com/service/go_base_frame/api/system/log"
	"go_file_auth/api/conf"
	"go_file_auth/www"
	"os"
	"os/signal"
	"syscall"
)

// основаная функция
func main() {

	// стартуем микросервис
	start()
}

// стартуем микросервис
func start() {

	config := conf.GetConfig()

	ctx := context.Background()
	globalCtx, cancelFn := context.WithCancel(ctx)

	flags.SetFlags()
	flags.Parse()

	log.SetLoggingLevel(config.LoggingLevel)

	// нормально отключаемся, если пришел сигнал
	go waitGracefulStop(cancelFn)

	// запускаем прослушивание окружения,
	// сервис работает, пока окружение прослушивается
	<-www.Listen(globalCtx)
}

// нормальное завершение работы
func waitGracefulStop(cancel context.CancelFunc) {

	c := make(chan os.Signal, 1)
	signal.Notify(c, os.Interrupt, syscall.SIGTERM)

	osCall := <-c
	log.Warningf("received stop signal:%+v", osCall)

	cancel()
}
