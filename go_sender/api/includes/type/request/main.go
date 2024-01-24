package request

import Isolation "go_sender/api/includes/type/isolation"

// структура для передачи в методы
type Data struct {
	RequestData    []byte `json:"request_data"` // тело запроса
	CompanyId      int64  `json:"company_id"`   // id компании для которой необходимо выполнить запрос
	Isolation      *Isolation.Isolation
	CompanyEnvList *Isolation.CompanyEnvList
}

// структура запроса
type Request struct {
	Method    string `json:"method"`     // метод
	CompanyId int64  `json:"company_id"` // id компании для которой необходимо выполнить запрос
	Body      []byte `json:"body"`       // тело запроса
}
