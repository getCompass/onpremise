package wsControllerV1

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	Isolation "go_sender/api/includes/type/isolation"
	"go_sender/api/includes/type/ws"
	"strings"
)

// поддерживаемые контроллры
var allowControllers = controllerMap{
	"client": clientMethods,
}

var handlerVersion = 1

// вспомогательные типы даннных
type controllerMap map[string]methodMap     // массив контроллеров
type methodMap map[string]func(*dataStruct) // массив методов

// структура ответа
type ResponseStruct struct {
	WSMethod string      `json:"ws_method"`
	WSData   interface{} `json:"ws_data"`
}

// структура ошибки
type ErrorStruct struct {
	Code int `json:"code"`
}

// структура для передачи в методы
type dataStruct struct {
	requestData    []byte
	connection     *ws.ConnectionStruct
	companyEnvList *Isolation.CompanyEnvList
}

// вызываем метод
func Work(companyEnvList *Isolation.CompanyEnvList, method string, connection *ws.ConnectionStruct, request []byte) {

	// приводим метод к нижнему регистру
	method = strings.ToLower(method)

	// разбираем метод на контроллер и название
	controllerName, methodName := splitMethod(method)

	// проходим по всем методам контроллера
	for method, item := range allowControllers[controllerName] {

		// если нашли нужный
		if methodName == strings.ToLower(method) {

			data := &dataStruct{
				requestData:    request,
				connection:     connection,
				companyEnvList: companyEnvList,
			}

			// выполняем метод
			item(data)
			return
		}
	}

	Error(connection, 100, fmt.Sprintf("method '%s' in controller is not available", method))
}

// разбираем метод на контроллер и название
func splitMethod(method string) (string, string) {

	// разбираем строку
	splitList := strings.Split(method, ".")

	// проверяем что получилось разобрать
	if len(splitList) < 2 {
		return "", ""
	}

	return splitList[0], splitList[1]
}

// возвращает код ошибки с сообщением
func Error(connection *ws.ConnectionStruct, errorCode int, msg interface{}) {

	// соединение не авторизовано, не отпправляем ничего
	if connection.UserId < 1 {
		return
	}

	wsUniqueID := functions.GenerateUuid()
	connection.SendInternalEventViaConnection(connection.UserId, "sender.error", ErrorStruct{Code: errorCode}, wsUniqueID)
}
