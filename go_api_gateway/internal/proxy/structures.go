package proxy

// структура ответа
type ResponseStruct struct {
	Status   string `json:"status"`
	Response any    `json:"response"`
}
