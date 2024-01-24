package handlerTcp

import (
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	Isolation "go_company_cache/api/includes/type/isolation"
	"go_company_cache/api/includes/type/request"
	"strings"
	"time"
)

// -------------------------------------------------------
// пакет, реализующий роутинг поступивших событий в функции
// -------------------------------------------------------

// вспомогательные типы даннных
type controllerMap map[string]methodMap                           // массив контроллеров
type methodMap map[string]func(data *request.Data) ResponseStruct // массив методов

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

type Controller struct {
	companyEnvList *Isolation.CompanyEnvList
}

// таймаут запроса
const requestTimeout = 200 * time.Millisecond

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

func Make(companyEnvList *Isolation.CompanyEnvList) *Controller {

	return &Controller{
		companyEnvList: companyEnvList,
	}
}

// вызываем необходимый метод
func (controller *Controller) DoStart(request request.Request) []byte {

	// вызываем метод
	response := work(controller.companyEnvList, request)

	// переводим ответ в json
	bytes, err := go_base_frame.Json.Marshal(response)
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
func work(companyEnvList *Isolation.CompanyEnvList, requestItem request.Request) ResponseStruct {

	// поддерживаемые контроллры
	var allowControllers = controllerMap{
		"system":  systemMethods,
		"session": sessionMethods,
		"member":  memberMethods,
	}

	// удаляем пользовательские сессии из кэша
	isolation := companyEnvList.GetEnv(requestItem.CompanyId)
	if isolation == nil {
		return Error(503, "isolation not found")
	}

	// приводим метод к нижнему регистру
	method := strings.ToLower(requestItem.Method)

	// разбираем метод на контроллер и название
	controllerName, methodName := splitMethod(method)

	// проходим по всем методам контроллера
	for method, item := range allowControllers[controllerName] {

		// если нашли нужный
		if methodName == strings.ToLower(method) {

			data := &request.Data{
				RequestData:      requestItem.Body,
				CompanyId:        requestItem.CompanyId,
				CompanyIsolation: isolation,
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
