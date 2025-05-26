package socket

import (
	"crypto/tls"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/conf"
	"io/ioutil"
	"net/http"
	"net/url"
)

// Response тип ответа от сервера
type Response struct {
	Status   string      `json:"status"`
	Response interface{} `json:"response"`
	Message  string      `json:"message"`
	HttpCode int         `json:"http_code"`
}

var client = &http.Client{

	Transport: &http.Transport{
		TLSClientConfig: &tls.Config{
			InsecureSkipVerify: true,
		},
	},
}

// DoCall выполнить tcp запрос по url
func DoCall(module string, method string, signature string, jsonParams string, userId int64, companyId int64) (Response, error) {

	config := conf.GetSocketConfig()
	socketUrl := config.SocketUrl[module] + config.SocketModule[module]

	// формируем данные которые пошлём в модуль
	data := url.Values{
		"method":        {method},
		"user_id":       {functions.Int64ToString(userId)},
		"sender_module": {"go_company"},
		"company_id":    {functions.Int64ToString(companyId)},
		"json_params":   {jsonParams},
		"signature":     {signature},
	}

	log.Infof("%s", jsonParams)
	// выполняем пост запрос
	resp, err := client.PostForm(socketUrl, data)
	if err != nil {
		return Response{}, err
	}

	// считываем ответ
	bodyBytes, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		return Response{}, err
	}

	response := Response{}

	err = go_base_frame.Json.Unmarshal(bodyBytes, &response)
	if err != nil {
		return Response{}, err
	}

	return response, nil
}
