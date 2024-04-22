package gwsolution

import (
	"crypto/tls"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pusher/api/conf"
	"go_pusher/api/includes/type/premise"
	"io"
	"net/http"
	"net/url"
	"strings"
)

// ResponseStruct ожидаемая структура ответа от сервера
type ResponseStruct struct {
	Status    string          `json:"status"`
	Response  json.RawMessage `json:"response"`
	Message   string          `json:"message"`
	ErrorCode int             `json:"error_code"`
}

// структура заготовки для premise-запроса
type rawHttpRequestData struct {
	post    map[string]string
	headers map[string]string
}

// реализация добавления данных авторизации к запросу
var authAppender HttpAuthAppender

// http-клиент, который будет вызывать удаленный севрер
var client = &http.Client{
	Transport: &http.Transport{
		TLSClientConfig: &tls.Config{
			InsecureSkipVerify: true,
		},
	},
}

// получаем информацию по серверу
func getServerInfo() {

	config := conf.GetConfig()
	serverInfo, err := premise.GetCurrent()
	if err != nil {
		log.Errorf("cant request sender to send push. Error: %v", err)
	}

	// для saas разрешаем упрощенную авторизацию
	if config.ServerAccommodation == "saas" {

		authAppender = &HttpAuthAppenderTrusted{Domain: serverInfo.Domain, ServerUid: serverInfo.ServerUid}
		return
	}

	log.Infof("serverInfo %v", serverInfo)

	// все остальные решения используют авторизацию с подписью
	authAppender = &HttpAuthAppenderSecretSign{
		Domain:         serverInfo.Domain,
		ServerUid:      serverInfo.ServerUid,
		SecretKeyBytes: []byte(serverInfo.SecretKey),
	}
}

// подготовить параметры для запроса
func resolveUrl(module string) string {

	// получаем урл модуля в который уйдет событие
	config := conf.GetSocketConfig()
	moduleUrl := config.SocketUrl[module] + config.SocketModule[module]

	return moduleUrl
}

// Call выполняет вызов удаленного сервера по premise-интерфейсу
func call(socketUrl string, method string, jsonParams json.RawMessage) ([]byte, error) {

	post := map[string]string{
		"payload": string(jsonParams),
		"method":  method,
	}

	getServerInfo()
	rawRequestData, _ := authAppender.append(&rawHttpRequestData{
		headers: map[string]string{"Content-Type": "application/x-www-form-urlencoded"},
		post:    post,
	})

	// заполняем форму данными
	postValues := url.Values{}
	for key, value := range rawRequestData.post {
		postValues.Set(key, value)
	}

	// инициализируем новый запрос и добавляем заголовки
	req, err := http.NewRequest("POST", socketUrl, strings.NewReader(postValues.Encode()))
	for key, value := range rawRequestData.headers {
		req.Header.Set(key, value)
	}

	log.Infof("request: %s", req)

	rawResponse, err := client.Do(req)
	if err != nil {
		return nil, fmt.Errorf("request to %s/%s failed: %s", socketUrl, method, err.Error())
	}

	// считываем ответ
	bodyBytes, err := io.ReadAll(rawResponse.Body)
	if err != nil {
		return nil, fmt.Errorf("can not read response from %s/%s", socketUrl, method)
	}

	// дальше пропускам только 200 коды
	if rawResponse.StatusCode != http.StatusOK {

		log.Errorf("%s/%s returned %d; response is %s", socketUrl, method, rawResponse.StatusCode, string(bodyBytes))
		return nil, fmt.Errorf("request to %s/%s ended with %d http code", socketUrl, method, rawResponse.StatusCode)
	}

	response := ResponseStruct{}
	if err = json.Unmarshal(bodyBytes, &response); err != nil {
		return nil, fmt.Errorf("can not parse response from %s/%s %s", socketUrl, method, string(bodyBytes))
	}

	if response.Status != "ok" {
		return nil, fmt.Errorf("response from %s/%s is not ok — code %d, %s", socketUrl, method, response.ErrorCode, response.Message)
	}

	return response.Response, nil
}
