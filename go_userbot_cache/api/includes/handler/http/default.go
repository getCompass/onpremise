package handlerHttp

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"strings"
)

// -------------------------------------------------------
// пакет, реализующий роутинг поступивших событий в функции
// -------------------------------------------------------

// поддерживаемые контроллры
var allowControllers = controllerMap{
	"userbot": userbotMethods,
}

// вспомогательные типы даннных
type controllerMap map[string]methodMap
type methodMap map[string]func([]byte) ResponseStruct // массив методов

// структура ответа
type ResponseStruct struct {
	Status   string      `json:"status"`
	Response interface{} `json:"response"`
}

// структура ошибки
type ErrorStruct struct {
	ErrorCode int    `json:"error_code"`
	Message   string `json:"message"`
}

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// вызываем необходимый метод
func DoStart(method string, request string) ResponseStruct {

	// вызываем метод
	response := work(method, request)

	return response
}

// возвращает ответ
func Ok(responseList ...interface{}) ResponseStruct {

	// получаем ответ из массива
	responseInterface := getResponseFromList(responseList...)

	response := ResponseStruct{
		Status:   "ok",
		Response: responseInterface,
	}

	return response
}

// возвращает код ошибки с сообщением
func Error(errorCode int, message string) ResponseStruct {

	response := ResponseStruct{
		Status: "error",
		Response: ErrorStruct{
			ErrorCode: errorCode,
			Message:   message,
		},
	}

	return response
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// вызываем метод
func work(method string, request string) ResponseStruct {

	// приводим метод к нижнему регистру
	method = strings.ToLower(method)

	// разбираем метод на контроллер и название
	controllerName, methodName := splitMethod(method)

	// проходим по всем методам контроллера
	for method, item := range allowControllers[controllerName] {

		// если нашли нужный
		if methodName == strings.ToLower(method) {

			// выполняем метод
			return item([]byte(request))
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
