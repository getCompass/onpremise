package authz

import "net/http"

// Пакет описывает способы авторизации запросов, приходящих в сервис.
// Данный файл содержит внешний интерфейс правил авторизации запросов.

// Authorizer интерфейс логики авторизации
type Authorizer interface {
	Try(*http.Request, []byte) (interface{}, error)
}

type AuthorizerMap map[string]*Authorizer
