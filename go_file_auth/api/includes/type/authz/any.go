package authz

import (
	"net/http"
)

// Пакет описывает способы авторизации запросов, приходящих в сервис.
// Данный файл содержит правило авторизации — доступ есть у всех.

// AnyEntrypointAuthorizer метод авторизации
// который проходит без проверки условий
type AnyEntrypointAuthorizer struct{}

// MakeAnyEntrypointAuthorizer создает экземпляр способа авторизации Any
func MakeAnyEntrypointAuthorizer() *Authorizer {

	// волшебство интерфейсов в golang
	var item Authorizer = &AnyEntrypointAuthorizer{}
	return &item
}

// Try проверяет запрос, пришедший от внешнего сервиса.
func (pa *AnyEntrypointAuthorizer) Try(_ *http.Request, _ []byte) (interface{}, error) {

	return nil, nil
}
