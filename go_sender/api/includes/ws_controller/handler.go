package wsController

import (
	"encoding/json"
	Isolation "go_sender/api/includes/type/isolation"
	"go_sender/api/includes/type/ws"
	wsControllerV1 "go_sender/api/includes/ws_controller/v1"
)

type requestStruct struct {
	Version  int    `json:"version"`
	Type     string `json:"type"`
	WSMethod string `json:"ws_method"`
}

// вызываем необходимый метод
func DoStart(companyEnvList *Isolation.CompanyEnvList, connection *ws.ConnectionStruct, requestBytes []byte) error {

	// сюда запишем параметры реквеста
	request := requestStruct{}
	err := json.Unmarshal(requestBytes, &request)
	if err != nil {
		return err
	}

	// если подключение авторизовано
	if connection.UserId > 0 {
		request.Version = connection.HandlerVersion
	}

	// роутим обработчик запроса на основе версии
	switch request.Version {
	case 1:

		wsControllerV1.Work(companyEnvList, request.WSMethod, connection, requestBytes)
		return nil

		// если не нашли хендлер то идем по дефолту :)
	default:

		wsControllerV1.Work(companyEnvList, request.WSMethod, connection, requestBytes)
		return nil
	}
}
