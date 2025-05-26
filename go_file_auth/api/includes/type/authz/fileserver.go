package authz

import (
	"net/http"
)

// Пакет описывает способы авторизации запросов, приходящих в сервис.
// Данный файл содержит правило авторизации — есть заголовок авторизации загрузки файла.

const dataSourceCookie = "cookie"
const dataSourceAuthHeader = "header"

// ClientAuthzData структура клиентски данных авторизации
type ClientAuthzData struct {
	Source string `json:"source"`
	Value  string `json:"value"`
}

// FileServerEntrypointAuthorizer метод авторизации загрузки файлов
type FileServerEntrypointAuthorizer struct{}

// Try проверяет запрос, пришедший от внешнего сервиса.
func (pa *FileServerEntrypointAuthorizer) Try(req *http.Request, _ []byte) (interface{}, error) {

	if header := req.Header.Get("X-Compass-File-Authz-Key"); header != "" {
		return &ClientAuthzData{Source: dataSourceCookie, Value: header}, nil
	}

	if header := req.Header.Get("Authorization"); header != "" {
		return &ClientAuthzData{Source: dataSourceAuthHeader, Value: header}, nil
	}

	return &ClientAuthzData{"", ""}, nil
}
