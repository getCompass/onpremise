package httpsrv

/**
 * Пакет обработки входящих http-запросов.
 */

import (
	"context"
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_file_auth/api/includes/endpoint/httpsrv/httpfileuathz"
	"net/http"
	"time"
)

type httpHandler struct {
	ctx context.Context
}

// ServeHTTP handler для http
func (h *httpHandler) ServeHTTP(w http.ResponseWriter, req *http.Request) {

	// запускаем контекст запроса, все должно успеть отработать в ожидаемое время
	requestContext, cancelFn := context.WithTimeout(h.ctx, time.Second*2)
	defer cancelFn()

	// парсим тело запроса
	if err := req.ParseForm(); err != nil {

		w.WriteHeader(http.StatusBadRequest)
		return
	}

	// запускаем метод
	response := httpfileuathz.DoStart(requestContext, req)

	// если обработчик вернул не 200 код, то просто возвращаем код ошибки без каких-либо данных
	// пока так примитивно, потом можно будет сделать какое-то ветвление при необходимости
	if response.HttpCode != 200 {

		w.WriteHeader(response.HttpCode)
		return
	}

	// nosemgrep
	if _, err := w.Write(response.Bytes); err != nil {

		log.Errorf("Write http error: %w", err)
		return
	}
}

// Listen слушаем http порт
func Listen(ctx context.Context, port int64) {

	// делаем контекст для обработчика
	handlerContext, cancelFn := context.WithCancel(ctx)

	handler := httpHandler{ctx: handlerContext}
	server := &http.Server{Addr: fmt.Sprintf("0.0.0.0:%d", port), Handler: &handler}

	// флаг работы сервера, чтобы при закрытии контекста
	// не падало ошибок закрытия закрытого сервера
	isListening := true

	go func() {

		<-ctx.Done()
		cancelFn()

		if isListening {

			// завершаем с context.Background(), потому что контекст, в котором работает
			// веб-сервер уже завершился, по сути это контекст ожидания завершения
			// работы веб-сервера, а его мы можем ждать до последнего
			_ = server.Shutdown(context.Background())
		}
	}()

	if err := server.ListenAndServe(); !errors.Is(err, http.ErrServerClosed) {
		panic(err.Error())
	}

	isListening = false
}
