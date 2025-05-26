package socket

import (
	"context"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"strconv"
	"strings"
)

const myModuleName = "go_file_auth"

// Response ответа от сервера
type Response struct {
	Status   string      `json:"status"`
	Response interface{} `json:"response"`
	Message  string      `json:"message"`
	HttpCode int         `json:"http_code"`
}

// Call выполнить http запрос по url
func (sc *Client) Call(ctx context.Context, socketUrl string, method string, jsonParams json.RawMessage, signature string, userId int64, companyId int64) (*Response, error) {

	// формируем данные которые пошлём в модуль
	data := url.Values{
		"method":        {method},
		"user_id":       {strconv.FormatInt(userId, 10)},
		"sender_module": {myModuleName},
		"json_params":   {string(jsonParams)},
		"signature":     {signature},
		"company_id":    {strconv.FormatInt(companyId, 10)},
	}

	// проверим, что контекст еще живой, чтобы не передать
	// уже истекший контекст в вызов сокет метода
	if err := ctx.Err(); err != nil {
		return nil, fmt.Errorf("request con not be executed with passed context")
	}

	// формируем данные запроса
	req, err := http.NewRequestWithContext(ctx, http.MethodPost, socketUrl, strings.NewReader(data.Encode()))
	if err != nil {
		return nil, err
	}

	// выполняем запрос
	req.Header.Add("Content-Type", "application/x-www-form-urlencoded")
	res, err := sc.httpClient.Do(req)
	if err != nil {
		return nil, err
	}

	// считываем ответ
	bodyBytes, err := io.ReadAll(res.Body)
	if err != nil {
		return nil, err
	}

	response := Response{}
	if err := json.Unmarshal(bodyBytes, &response); err != nil {
		return nil, err
	}

	response.HttpCode = res.StatusCode
	return &response, nil
}

// Client клиент общения с другими модулями
type Client struct {
	httpClient *http.Client
}

// MakeClient возвращает экземпляр клиент общения с другими модулями
func MakeClient() *Client {

	return &Client{
		httpClient: &http.Client{

			Transport: &http.Transport{

				// nosemgrep
				TLSClientConfig: &tls.Config{
					InsecureSkipVerify: true,
				},
			},
		},
	}
}
