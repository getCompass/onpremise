package handlerHttp

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_activity/api/includes/type/request"
	"strings"
)

// -------------------------------------------------------
// пакет, реализующий роутинг поступивших событий в функции
// -------------------------------------------------------

// поддерживаемые контроллры
var allowControllers = controllerMap{
	"user": userMethods,
}

// вспомогательные типы даннных
type controllerMap map[string]methodMap
type methodMap map[string]func(data request.Data) []byte // массив методов

// ResponseStruct структура ответа
type ResponseStruct struct {
	Status   string      `json:"status"`
	Response interface{} `json:"response"`
}

// ErrorStruct структура ошибки
type ErrorStruct struct {
	ErrorCode int    `json:"error_code"`
	Message   string `json:"message"`
}

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// DoStart вызываем необходимый метод
func DoStart(request request.Request) []byte {

	// вызываем метод
	response := work(request)

	return response
}

// Ok возвращает ответ
func Ok(responseList ...interface{}) []byte {

	// получаем ответ из массива
	responseInterface := getResponseFromList(responseList...)

	response := ResponseStruct{
		Status:   "ok",
		Response: responseInterface,
	}

	// переводим ответ в json
	bytes, err := json.Marshal(response)
	if err != nil {

		log.Errorf("unable to marshal response, error: %v", err)
		return []byte{}
	}

	return bytes
}

// Error возвращает код ошибки с сообщением
func Error(errorCode int, message string) []byte {

	response := ResponseStruct{
		Status: "error",
		Response: ErrorStruct{
			ErrorCode: errorCode,
			Message:   message,
		},
	}

	// переводим ответ в json
	bytes, err := json.Marshal(response)
	if err != nil {

		log.Errorf("unable to marshal response, error: %v", err)
		return []byte{}
	}

	return bytes
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// вызываем метод
func work(requestItem request.Request) []byte {

	// приводим метод к нижнему регистру
	method := strings.ToLower(requestItem.Method)

	// разбираем метод на контроллер и название
	controllerName, methodName := splitMethod(method)

	// проходим по всем методам контроллера
	for method, item := range allowControllers[controllerName] {

		// если нашли нужный
		if methodName == strings.ToLower(method) {

			data := request.Data{
				RequestData: requestItem.Body,
			}

			// выполняем метод
			return item(data)
		}
	}

	log.Warningf("method '%s' in controller is not available", method)
	return Error(103, "method in controller is not available")
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

// получаем ответ из массива
func getResponseFromList(responseList ...interface{}) interface{} {

	// проверяем наличие ответа
	if len(responseList) < 1 {
		return struct{}{}
	}

	return responseList[0]
}
