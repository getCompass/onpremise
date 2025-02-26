package socket

import (
	"crypto/tls"
	"crypto/x509"
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/conf"
	"io/ioutil"
	"net/http"
	"net/url"
	"strconv"
)

// Response тип ответа от сервера
type Response struct {
	Status   string      `json:"status"`
	Response interface{} `json:"response"`
	Message  string      `json:"message"`
	HttpCode int         `json:"http_code"`
}

var client *http.Client

// инициализируем клиент
func init() {

	config, err := conf.GetConfig()

	if err != nil {
		panic(err)
	}

	rootCAs := x509.NewCertPool()
	rootCAs.AppendCertsFromPEM([]byte(config.CaCertificate))
	client = &http.Client{
		Transport: &http.Transport{

			// nosemgrep
			TLSClientConfig: &tls.Config{
				InsecureSkipVerify: false,
				RootCAs:            rootCAs,
			},
		},
	}
}

// DoCall выполнить tcp запрос по url
func DoCall(module string, method string, signature string, jsonParams string, userId int64, companyId int64) (Response, error) {

	config := conf.GetSocketConfig()
	socketUrl := config.SocketUrl[module] + config.SocketModule[module]

	// формируем данные которые пошлём в модуль
	data := url.Values{
		"method":        {method},
		"user_id":       {functions.Int64ToString(userId)},
		"sender_module": {"sender"},
		"company_id":    {functions.Int64ToString(companyId)},
		"json_params":   {jsonParams},
		"signature":     {signature},
	}

	// выполняем пост запрос
	resp, err := client.PostForm(socketUrl, data)
	if err != nil {

		log.Errorf("%v", err.Error())
		return Response{}, err
	}

	// считываем ответ
	bodyBytes, err := ioutil.ReadAll(resp.Body)

	if err != nil {

		log.Errorf("%v", err.Error())
		return Response{}, err
	}

	response := Response{}

	err = json.Unmarshal(bodyBytes, &response)
	if err != nil {

		log.Errorf("%v", err.Error())
		return Response{}, err
	}

	return response, nil
}

// выполнить tcp запрос по url в pivot
func DoCallPivot(socketUrl string, method string, jsonParams json.RawMessage, signature string, userId int64) ([]byte, error) {

	// формируем данные которые пошлём в модуль
	data := url.Values{
		"method":        {method},
		"user_id":       {strconv.FormatInt(userId, 10)},
		"sender_module": {"sender"},
		"json_params":   {string(jsonParams)},
		"signature":     {signature},
	}

	// выполняем пост запрос
	resp, err := client.PostForm(socketUrl, data)
	if err != nil {
		return []byte{}, err
	}

	// считываем ответ
	bodyBytes, err := ioutil.ReadAll(resp.Body)

	return bodyBytes, nil
}
