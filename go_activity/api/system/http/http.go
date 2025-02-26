package http

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	handlerHttp "go_activity/api/includes/handler/http"
	"go_activity/api/includes/type/request"
	"net/http"
)

type httpHandler struct{}

// handler для http
func (h *httpHandler) ServeHTTP(w http.ResponseWriter, req *http.Request) {

	err := req.ParseForm()
	if err != nil {

		log.Errorf("Parse form error: %v", err)
		return
	}
	v := req.Form

	method := v.Get("method")
	jsonParams := []byte(v.Get("request"))

	requestItem := request.Request{
		Method: method,
		Body:   jsonParams,
	}

	response := handlerHttp.DoStart(requestItem)

	// nosemgrep
	_, err = w.Write(response)
	if err != nil {

		log.Errorf("Write http error: %v", err)
		return
	}
}

// слушаем http порт
func Listen(port int64) {

	handler := httpHandler{}

	// nosemgrep
	err := http.ListenAndServe(fmt.Sprintf(":%d", port), &handler)
	if err != nil {
		panic(err)
	}
}
