package http

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_userbot_cache/api/conf"
	handlerHttp "go_userbot_cache/api/includes/handler/http"
	"go_userbot_cache/api/includes/type/socket"
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
	jsonParams := v.Get("json_params")
	signature := v.Get("signature")

	// проверяем подпись
	verifySignature := socket.GetPivotSignature(conf.GetConfig().SocketKeyMe, json.RawMessage(jsonParams))
	if verifySignature != signature {

		log.Error("http request verifySignature error")
		return
	}

	response := handlerHttp.DoStart(method, jsonParams)
	_, err = w.Write(response)
	if err != nil {

		log.Errorf("Write http error: %v", err)
		return
	}
}

// слушаем http порт
func Listen(port int64) {

	handler := httpHandler{}

	err := http.ListenAndServe(fmt.Sprintf(":%d", port), &handler)
	if err != nil {
		panic(err)
	}
}
