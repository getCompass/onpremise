package socket

import (
	"crypto/tls"
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"io/ioutil"
	"net/http"
	"net/url"
	"strconv"
)

// внутренний тип — запакованные данные события
type getInfoParams struct {
	Method string `json:"method"`
	Token  string `json:"token"`
}

// тип ответа от сервера
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

// выполнить tcp запрос по url
func DoCall(socketUrl string, method string, jsonParams json.RawMessage, signature string, userId int64) ([]byte, error) {

	// формируем данные которые пошлём в модуль
	data := url.Values{
		"method":        {method},
		"user_id":       {strconv.FormatInt(userId, 10)},
		"sender_module": {"userbot_cache"},
		"json_params":   {string(jsonParams)},
		"signature":     {signature},
	}

	log.Infof("json params: %s", string(jsonParams))

	// выполняем пост запрос
	resp, err := client.PostForm(socketUrl, data)
	if err != nil {
		return []byte{}, err
	}

	// считываем ответ
	bodyBytes, err := ioutil.ReadAll(resp.Body)

	return bodyBytes, nil
}
