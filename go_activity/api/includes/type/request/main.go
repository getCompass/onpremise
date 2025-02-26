package request

// структура для передачи в методы
type Data struct {
	RequestData []byte `json:"request_data"` // тело запроса
}

// структура запроса
type Request struct {
	Method string `json:"method"` // метод
	Body   []byte `json:"body"`   // тело запроса
}
