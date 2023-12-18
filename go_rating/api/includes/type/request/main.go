package request

import (
	Isolation "go_rating/api/includes/type/isolation"
)

// структура для передачи в методы
type Data struct {
	RequestData      []byte
	SpaceId          int64
	CompanyIsolation *Isolation.Isolation
}

// структура запроса
type Request struct {
	Method  string `json:"method"`   // метод
	SpaceId int64  `json:"space_id"` // id пространства для которой необходимо выполнить запрос
	Body    []byte `json:"body"`     // тело запроса
}
