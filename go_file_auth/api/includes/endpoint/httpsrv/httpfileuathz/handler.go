package httpfileuathz

import (
	"context"
	"encoding/json"
	"github.com/service/go_base_frame/api/system/log"
	"go_file_auth/api/includes/type/authz"
	"net/http"
	"strings"
)

/**
 * Пакет обработки входящих http-запросов.
 */

// AnswerStruct структура данных, содержащих результат обработки запроса
type AnswerStruct struct {
	HttpCode int
	Bytes    []byte
}

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

// основания функция метода
type methodFunc func(context.Context, *authz.ClientAuthzData, *http.Request) *AnswerStruct

// метод контроллера
type controllerMethod struct {
	call methodFunc
	auth authz.Authorizer
}

// Controller контроллер группы методов
type Controller struct {
	name       string
	methodList map[string]*controllerMethod
}

// зарегистрированные контроллеры
var knownControllerList = map[string]*Controller{}

// структуры ответов по умолчанию, часть ответов всегда
// одинакова, нет смысла каждый раз выделять память на структуру
var default401Answer = AnswerStruct{HttpCode: 401}
var default200Answer = AnswerStruct{HttpCode: 200, Bytes: []byte{}}

// функция инициализации пакета, будет вызвана
// автоматически при запуске микросервиса
func init() {

	// функция регистрации контроллера
	reg := func(n string, c *Controller) {

		knownControllerList[strings.ToLower(n)] = c
		log.Infof("controller %s registered", n)
	}

	// добавляем контроллер пушей
	reg(InitFileAuthzController())
}

// DoStart вызывает необходимый метод
func DoStart(ctx context.Context, req *http.Request) *AnswerStruct {

	// получаем метод из URL-пути
	method := strings.ToLower(strings.Replace(req.URL.Path, "/", ".", -1))

	// разбираем метод на контроллер и название
	controllerName, methodName := splitMethod(method)

	var methodHandler *controllerMethod
	var exists bool

	// проверим наличие запрошенного контроллера
	if _, exists = knownControllerList[controllerName]; !exists {
		return Error(404, 103, "invalid entrypoint")
	}

	// проверим наличие нужного метода в запрошенном контроллере
	if methodHandler, exists = knownControllerList[controllerName].methodList[methodName]; !exists {
		return Error(404, 103, "invalid entrypoint")
	}

	// выполняем метод
	authorized, err := methodHandler.auth.Try(req, []byte{})
	if err != nil {
		return Http401()
	}

	caData, ok := authorized.(*authz.ClientAuthzData)
	if !ok {
		panic("something went wrong during ClientAuthzData type casting")
	}

	// запускаем контекст запроса, все должно успеть отработать в ожидаемое время
	return methodHandler.call(ctx, caData, req)
}

// Ok возвращает ответ
func Ok(responseList ...interface{}) *AnswerStruct {

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
		return &default200Answer
	}

	return &AnswerStruct{200, bytes}
}

// Error возвращает код ошибки с сообщением
func Error(httpCode, errorCode int, message string) *AnswerStruct {

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
		return &default200Answer
	}

	return &AnswerStruct{httpCode, bytes}
}

// Http401 возвращает 401 http код
func Http401() *AnswerStruct {

	return &default401Answer
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// разбираем метод на контроллер и название
func splitMethod(method string) (string, string) {

	// разбираем строку
	splitList := strings.Split(strings.TrimPrefix(method, "."), ".")

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
