package handlerTcp

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"strings"
)

// -------------------------------------------------------
// пакет, реализующий роутинг поступивших событий в функции
// -------------------------------------------------------

// поддерживаемые контроллры
var allowControllers = controllerMap{
	"system": systemMethods,
	"pusher": pusherMethods,
}

// вспомогательные типы даннных
type controllerMap map[string]methodMap               // массив контроллеров
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
func DoStart(method string, request []byte) []byte {

	// вызываем метод
	response := work(method, request)

	// переводим ответ в json
	bytes, err := json.Marshal(response)
	if err != nil {

		log.Errorf("unable to marshal response, error: %v", err)
		return []byte{}
	}

	return bytes
}

// возвращает ответ
func Ok(responseList ...interface{}) ResponseStruct {

	// получаем ответ из массива
	responseInterface := getResponseFromList(responseList...)

	return ResponseStruct{
		Status:   "ok",
		Response: responseInterface,
	}
}

// возвращает код ошибки с сообщением
func Error(errorCode int, message string) ResponseStruct {

	return ResponseStruct{
		Status: "error",
		Response: ErrorStruct{
			ErrorCode: errorCode,
			Message:   message,
		},
	}
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// вызываем метод
func work(method string, request []byte) ResponseStruct {

	// приводим метод к нижнему регистру
	method = strings.ToLower(method)

	// разбираем метод на контроллер и название
	controllerName, methodName := splitMethod(method)

	// проходим по всем методам контроллера
	for method, item := range allowControllers[controllerName] {

		// если нашли нужный
		if methodName == strings.ToLower(method) {

			// выполняем метод
			return item(request)
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
