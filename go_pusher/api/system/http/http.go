package http

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	handlerHttp "go_pusher/api/includes/handler/http"
	socketAuthKey "go_pusher/api/includes/type/socket/auth"
	"net/http"
	"sync"
)

type httpHandler struct{}

var socketKeyStore sync.Map

// handler для http
func (h *httpHandler) ServeHTTP(w http.ResponseWriter, req *http.Request) {

	err := req.ParseForm()
	if err != nil {

		log.Errorf("Parse form error: %v", err)
		return
	}
	v := req.Form

	method := v.Get("method")
	userId := functions.StringToInt64(v.Get("user_id"))
	companyId := functions.StringToInt(v.Get("company_id"))
	jsonParams := v.Get("json_params")
	signature := v.Get("signature")

	// проверяем подпись
	err = socketAuthKey.VerifyCompanySignature(jsonParams, signature, companyId)
	if err != nil {
		log.Errorf("VerifyCompanySignature error: %v", err)
		return
	}

	response := handlerHttp.DoStart(method, jsonParams, userId, companyId)
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
