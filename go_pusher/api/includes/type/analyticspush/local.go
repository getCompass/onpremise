package analyticspush

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_pusher/api/includes/type/privatize"
	"sync"
	"time"
)

/*
* - это локальное хранилище для накопления аналитики по отправке пуша пользователю
* - а также взаимодействия с основным хранилищем
* - локальное хранилище устанавливает статусы токенов пуша
* - в зависимости от статусов, принимает решение для отправки в основное хранилище main - последний в свою очередь отпрвляет аналитику
* - в локальном хранилище определены основные структуры для взаимодействия
* - при отправлении собранной аналитики из локльного в main хранилище - вся аналитика из объекта очищается
 */

// данная структура накапливает в себя всю аналитику пуша а также всю информацию с историей действий с токенами
type PushStruct struct {
	Uuid         string `json:"uuid"`          // уникальный id пуша
	UserId       int64  `json:"user_id"`       // user_id пользователя
	EventTime    int64  `json:"event_time"`    // время создания записи
	EventType    int64  `json:"event_type"`    // тип записи
	DeviceId     string `json:"device_id"`     // id устройства
	TokenHash    string `json:"token_hash"`    // токен на который отправили пуш
	PushId       string `json:"push_id"`       // Id пуша пришедшего от сервиса
	PushResponse int    `json:"push_response"` // Ответ пуша от сервиса
}

type HttpStruct struct {
	RequestItem  httpRequestStruct  `json:"request"`  // запрос
	ResponseItem httpResponseStruct `json:"response"` // ответ
}

// структура для запроса передаваемого по http
type httpRequestStruct struct {
	HeadersLen int                    `json:"headers_length"` // длина загловков
	BodyLength int                    `json:"body_length"`    // длина тела
	BodyMap    map[string]interface{} `json:"body"`           // тело
}

// структура для ответа передаваемого по http
type httpResponseStruct struct {
	StatusCode    int                    `json:"status_code"`    // код
	HeadersLength int                    `json:"headers_length"` // длина загловков
	HeadersMap    map[string]interface{} `json:"headers"`        // заголовки
	BodyLength    int                    `json:"body_length"`    // длина тела
	BodyMap       map[string]interface{} `json:"body"`           // тело
}

var storage sync.Map

// добавляем status к одному айтему с основном хранилище
func (a *PushStruct) AddType(status int64) {

	// обновляем поле EventType в объекте analyticItem, устанавливая туда присланное в функцию значение
	a.EventType = status
}

// обновим список аналитики пушей
// используется когда необходимо обновить статус сразу для множества объектов аналитики
// например когда устанавливается общий статус «отправлен в очередь», «pusher взял в работу» и подобные
func UpdateAnalyticPushList(pushItemList map[string]PushStruct, status int64) {

	for _, pushItem := range pushItemList {
		UpdateAnalyticPush(pushItem, status)
	}
}

// обновим аналитику
func UpdateAnalyticPush(pushItem PushStruct, status int64) PushStruct {

	if pushItem.Uuid == "" {

		return PushStruct{}
	}

	pushItem.AddType(status)

	// добавляем единичку чтобы отличать очередность
	pushItem.EventTime = time.Now().Unix() + 1

	Add(pushItem, functions.GenerateUuid())

	return pushItem
}

// обновим список аналитики пушей добавив ответ сервера
// используется когда необходимо обновить статус сразу для множества объектов аналитики
// например когда устанавливается общий статус «пуш упал с не известной ошибкой», «не смогли переотправить токен» и подобные
func UpdateAnalyticPushListWithServer(pushItemList map[string]PushStruct, status int64, PushResponse int) {

	for _, pushItem := range pushItemList {
		UpdateAnalyticWithServer(pushItem, status, PushResponse)
	}
}

// обновим аналитику пушей добавив ответ сервера
func UpdateAnalyticWithServer(pushItem PushStruct, status int64, PushResponse int) PushStruct {

	if pushItem.Uuid == "" {

		return PushStruct{}
	}

	pushItem.AddType(status)
	pushItem.PushResponse = PushResponse

	// добавляем единичку чтобы отличать очередность
	pushItem.EventTime = time.Now().Unix() + 1
	Add(pushItem, functions.GenerateUuid())

	return pushItem
}

// добавим аналитику в хранилище
func Add(a PushStruct, key string) {

	storage.Store(key, a)
}

// получаем все записи из update кэша
func GetAllFromUpdateCache() map[string]PushStruct {

	cache := map[string]PushStruct{}

	storage.Range(func(key, value interface{}) bool {
		cache[key.(string)] = value.(PushStruct)
		return true
	})

	return cache
}

// чистим update кэш
func ClearUpdateCacheAndSwap() {

	storage.Range(func(key interface{}, value interface{}) bool {

		storage.Delete(key)
		return true
	})
}

// устанавливаем device_id в объект аналитики
func (a *PushStruct) SetDeviceId(deviceId string) {

	a.DeviceId = privatize.MaskHalfPartOfString(deviceId)
}

// устанавливаем token_hash в объект аналитики
func (a *PushStruct) SetTokenHash(token string) {

	a.TokenHash = functions.GetSha1String(token)
}
