package www

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_file_auth/api/conf"
	"go_file_auth/api/includes/endpoint/httpsrv"
	"sync"
)

// переменная предназначеная ожидания рутин
var waitGroupItem = sync.WaitGroup{}

// Listen запускает прослушивание внешнего окружения
// возвращает функцию, исполнение которой означает,
// что внешнее окружение больше не прослушивается
// @long обработка каналов
func Listen(ctx context.Context) chan bool {

	stopChan := make(chan bool, 1)
	listenChan := make(chan string, 1)

	listenCtx, cancelFn := context.WithCancel(ctx)

	// эта горутна нужна для того, чтобы функция слушателя смогла завершиться
	// и не зависнуть в ожидании освобождения канала listenChan
	go func() {

		isActive := true

		// ждем, пока один из слушателей окружения не завершит работу,
		// как только кто-то отвалится, считаем что прослушивание окружения
		// завершилось и закрываем контекст прослушивания окружения
		for {

			listener := ""
			ok := true

			// слушаем до тех пор, пока канал не закроется,
			// канал закроется, когда waitGroupItem завершится
			if listener, ok = <-listenChan; !ok {
				break
			}

			log.Warningf("listener %s has been stopped", listener)

			if isActive {

				cancelFn()
				isActive = false
			}

			waitGroupItem.Done()
		}
	}()

	listenHttp(listenCtx, listenChan)

	// эта горутина отправит stop-сигнал инициатору,
	// когда все слушатели окружения завершат свою работу
	go func() {

		// отправляем вызывающему информацию о завершении прослушивания
		waitGroupItem.Wait()

		log.Warningf("the environment no longer listen")
		close(stopChan)   // закрываем, чтобы вызывающий понял, что мы завершили работу
		close(listenChan) // закрываем, чтобы не болтался
	}()

	return stopChan
}

// начинаем слушать http-подключения
func listenHttp(ctx context.Context, lChan chan string) {

	waitGroupItem.Add(1)

	go func() {

		httpsrv.Listen(ctx, conf.GetConfig().TcpPort)
		lChan <- "http"
	}()
}
