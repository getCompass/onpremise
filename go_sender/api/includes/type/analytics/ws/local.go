package analyticsWs

import (
	"sync"
)

/*
* - это локальное хранилище для накопления аналитики по отправке ws пользователю
* - а также взаимодействия с основным хранилищем
* - при отправлении собранной аналитики из локльного в main хранилище - вся аналитика из объекта очищается
 */

// данная структура накапливает в себя всю аналитику ws
type WsStruct struct {
	Uuid         string `json:"uuid"`           // уникальный id ws
	TypeWs       int64  `json:"type_ws"`        // тип ws который отправили
	UserId       int64  `json:"user_id"`        // id пользователя который должен получить ws
	CompanyId    int64  `json:"company_id"`     // id компании из которой произошло ws
	SenderUserId int64  `json:"sender_user_id"` // id пользователя который должен отправить ws
	EventName    string `json:"event_name"`     // название ws которое нужно
	CreatedAt    int64  `json:"created_at"`     // Время создания ws
	EndAt        int64  `json:"end_at"`         // Время окончания ws(соединения)
	TimeMs       int64  `json:"time_ms"`        // Время работы ws
	Platform     string `json:"platform"`       // С какой платформы было
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

type AnalyticStore struct {
	store map[string]*WsStruct

	// хранилище счетчиков
	// структура – map[row]row_value
	counterStore map[string]int

	mx sync.RWMutex
}

func MakeAnalyticWsStore() *AnalyticStore {

	return &AnalyticStore{
		store:        make(map[string]*WsStruct),
		counterStore: make(map[string]int),
		mx:           sync.RWMutex{},
	}
}

// добавляем status к одному айтему с основном хранилище
func (a *WsStruct) AddType(status int64) {

	// обновляем поле TypeWs в объекте analyticItem, устанавливая туда присланное в функцию значение
	a.TypeWs = status
}

// добавим аналитику в хранилищеincludes/type/ws/connection.go:351
func (aStore *AnalyticStore) Add(a *WsStruct, key string) {

	aStore.mx.Lock()
	aStore.store[key] = a
	aStore.mx.Unlock()
}

// инкрементим счетчик
func (aStore *AnalyticStore) IncCounter(row string, incValue int) {

	aStore.mx.Lock()

	_, exist := aStore.counterStore[row]
	if !exist {
		aStore.counterStore[row] = incValue
	} else {
		aStore.counterStore[row] += incValue
	}

	aStore.mx.Unlock()
}

// получаем все записи из update кэша
func (aStore *AnalyticStore) GetAllFromUpdateCache() (map[string]*WsStruct, map[string]int) {

	cache := make(map[string]*WsStruct)

	// содержимое хранилища счетчиков
	counterCache := make(map[string]int)

	aStore.mx.RLock()

	for k, v := range aStore.store {
		cache[k] = v
	}

	for k, v := range aStore.counterStore {
		counterCache[k] = v
	}

	aStore.store = make(map[string]*WsStruct)
	aStore.counterStore = make(map[string]int)

	aStore.mx.RUnlock()

	return cache, counterCache
}
