package gwsolution

import (
	"crypto/tls"
	"encoding/json"
	"fmt"
	"go_pusher/api/conf"
	"go_pusher/api/includes/type/premise"
	"io"
	"net/http"
	"net/url"
	"strings"

	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/server"
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
var client *http.Client

// поддерживаемые протоколы прокси
const (
	proxyProtocolHttp    = "http"
	proxyProtocolHttps   = "https"
	proxyProtocolSocks5  = "socks5"
	proxyProtocolSocks5h = "socks5h"
)

func init() {
	initClient()
}

func initClient() {

	client = &http.Client{

		Transport: &http.Transport{

			// nosemgrep
			TLSClientConfig: &tls.Config{
				InsecureSkipVerify: true,
			},
		},
	}

	log.Infof("Инициализируем прокси сервер...")
	config := conf.GetProxyConfig()

	if config.ProxyProtocol == "" {
		log.Infof("Прокси сервер не используется")
		return
	}

	allowedProxyProtocolList := []string{proxyProtocolHttp, proxyProtocolHttps, proxyProtocolSocks5, proxyProtocolSocks5h}
	exists, _ := functions.InArray(config.ProxyProtocol, allowedProxyProtocolList)
	if !exists {

		log.Errorf("Введен неподдерживаемый тип прокси %s, останавливаем его использование... Поддерживаемые протоколы %v", config.ProxyProtocol, allowedProxyProtocolList)
		return
	}

	proxyStr := fmt.Sprintf("%s://%s:%d", config.ProxyProtocol, config.ProxyHost, config.ProxyPort)
	if config.ProxyUsername != "" {
		proxyStr = fmt.Sprintf("%s://%s:%s@%s:%d", config.ProxyProtocol, config.ProxyUsername, config.ProxyPassword, config.ProxyHost, config.ProxyPort)
	}

	proxyURL, err := url.Parse(proxyStr)
	if err != nil {
		log.Errorf("Не смогли спарсить адрес прокси %s, останавливаем его использование...", proxyStr)
		return
	}

	client.Transport = &http.Transport{

		// nosemgrep
		TLSClientConfig: &tls.Config{
			InsecureSkipVerify: true,
		},
		Proxy: http.ProxyURL(proxyURL),
	}

	log.Infof("Загрузили прокси сервер %s://%s:%d", config.ProxyProtocol, config.ProxyHost, config.ProxyPort)

}

// ReloadClient перезагрузить конфигурацию клиента, только для теста
func ReloadClient() {

	if !server.IsTest() {
		return
	}

	initClient()
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
	socketUrl := config.SocketUrl[module]
	if len(socketUrl) == 0 {
		return ""
	}

	moduleUrl := socketUrl + config.SocketModule[module]

	return moduleUrl
}

// Call выполняет вызов удаленного сервера по premise-интерфейсу
// @long
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
