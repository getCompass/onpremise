package socket

import (
	"crypto/tls"
	"crypto/x509"
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_event/api/conf"
	"io"
	"net/http"
	"net/url"
	"strconv"
)

// тип ответа от сервера
type Response struct {
	Status   string      `json:"status"`
	Response interface{} `json:"response"`
	Message  string      `json:"message"`
	HttpCode int         `json:"http_code"`
}

var client *http.Client

// инициализируем клиент
func init() {

	config := conf.GetConfig()

	rootCAs := x509.NewCertPool()
	rootCAs.AppendCertsFromPEM([]byte(config.CaCertificate))
	client = &http.Client{
		Transport: &http.Transport{

			TLSClientConfig: &tls.Config{
				InsecureSkipVerify: false,
				RootCAs:            rootCAs,
			},
		},
	}
}

// выполнить tcp запрос по url
func DoCall(socketUrl string, method string, jsonParams json.RawMessage, signature string, userId int64, companyId int64) (Response, error) {

	// формируем данные которые пошлём в модуль
	data := url.Values{
		"method":        {method},
		"user_id":       {strconv.FormatInt(userId, 10)},
		"sender_module": {"go_event"},
		"json_params":   {string(jsonParams)},
		"signature":     {signature},
		"company_id":    {functions.Int64ToString(companyId)},
	}

	// log.Infof("json params: %s", string(jsonParams))

	// выполняем пост запрос
	resp, err := client.PostForm(socketUrl, data)
	if err != nil {
		return Response{}, err
	}

	// считываем ответ
	bodyBytes, err := io.ReadAll(resp.Body)

	// fmt.Println(string(bodyBytes))

	if err != nil {
		return Response{}, err
	}

	response := Response{}

	go_base_frame.Json.Unmarshal(bodyBytes, &response)

	return response, nil
}
