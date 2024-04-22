package gwsolution

import (
	"crypto/hmac"
	"crypto/sha1"
	"encoding/base64"
	"encoding/hex"
	"encoding/json"
)

// HttpAuthAppender интерфейс добавления данных авторизации к запросу
// на вход получает запрос, возвращает запрос с добавленными данными
type HttpAuthAppender interface {
	append(*rawHttpRequestData) (*rawHttpRequestData, error)
}

// HttpAuthAppenderSecretSign структура данных авторизации,
// авторизация происходит по подписи поля payload и идентификации
// сервера, передаваемых в заголовке запроса
type HttpAuthAppenderSecretSign struct {
	Domain         string
	ServerUid      string
	SecretKeyBytes []byte
}

// добавляет данные авторизации по заголовкам подписи полезной нагрузки
func (haa *HttpAuthAppenderSecretSign) append(reqData *rawHttpRequestData) (*rawHttpRequestData, error) {

	// формируем данные заголовка с подписью
	algo, signature := haa.signJsonParams([]byte(reqData.post["payload"]))
	dataSignatureHeaderValue := base64.StdEncoding.EncodeToString([]byte((algo + ";" + signature)))

	// формируем данные заголовка владельца подписи
	bearerHeaderValue := base64.StdEncoding.EncodeToString([]byte((haa.ServerUid + ";" + haa.Domain)))

	reqData.headers["Data-Signature"] = dataSignatureHeaderValue
	reqData.headers["Bearer"] = bearerHeaderValue

	return reqData, nil
}

// возвращает подпись закодированной в json полезной
// нагрузки и алгоритм, которым подпись была сформирована
func (haa *HttpAuthAppenderSecretSign) signJsonParams(jsonData json.RawMessage) (string, string) {

	hasher := hmac.New(sha1.New, haa.SecretKeyBytes)
	hasher.Write(jsonData)

	return "hmac:sha1", hex.EncodeToString(hasher.Sum(nil))
}

// HttpAuthAppenderTrusted структура данных авторизации
// без проверки доверенности, такой запрос не содержит
// никаких дополнительных данных, но имеет bearer-заголовок
type HttpAuthAppenderTrusted struct {
	Domain    string
	ServerUid string
}

// добавляет данные авторизации по заголовкам подписи полезной нагрузки
func (haa *HttpAuthAppenderTrusted) append(reqData *rawHttpRequestData) (*rawHttpRequestData, error) {

	// формируем данные заголовка владельца подписи
	bearerHeaderValue := base64.StdEncoding.EncodeToString([]byte((haa.ServerUid + ";" + haa.Domain)))
	reqData.headers["Sender"] = bearerHeaderValue

	return reqData, nil
}
