package request

import Isolation "go_company/api/includes/type/isolation"

// структура для передачи в методы
type Data struct {
	RequestData      []byte
	CompanyId        int64
	CompanyIsolation *Isolation.Isolation
}

// структура запроса
type Request struct {
	Method    string `json:"method"`     // метод
	CompanyId int64  `json:"company_id"` // id компании для которой необходимо выполнить запрос
	Body      []byte `json:"body"`       // тело запроса
}
