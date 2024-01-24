package Event

/** Пакет системных событий **/
/** В этом файле описана базовая структура события и методы для работы с ней **/

import (
	"encoding/json"
	"errors"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"strings"
	"time"
)

// тип, описывающий событие, созданное в каком-либо модуле системы
type ApplicationEvent struct {
	EventType        string          `json:"event_type"`
	SourceType       string          `json:"source_type"`
	SourceIdentifier string          `json:"source_identifier"`
	CreatedAt        int64           `json:"created_at"`
	EventData        json.RawMessage `json:"event_data"`
	Version          int             `json:"version,omitempty"`
	DataVersion      int             `json:"data_version,omitempty"`
	Uuid             string          `json:"uuid,omitempty"`
}

// создает событие с указанными параметрами
// события следует создавать только с помощью этой функции
func CreateEvent(eventType string, sourceType string, sourceIdentifier string, eventData interface{}) (ApplicationEvent, error) {

	eventDataRaw, err := go_base_frame.Json.Marshal(eventData)
	if err != nil {
		return ApplicationEvent{}, errors.New("can not create event, event data encoded was failed")
	}

	return ApplicationEvent{
		EventType:        eventType,
		SourceType:       sourceType,
		SourceIdentifier: sourceIdentifier,
		CreatedAt:        time.Now().UnixNano() / 1000,
		EventData:        eventDataRaw,
		Version:          1,
		DataVersion:      1,
		Uuid:             functions.GenerateUuid(),
	}, nil
}

// тип - событие в зпросе
type requestWithEvent struct {
	Event ApplicationEvent `json:"event"`
}

// получить событие из запроса
func CreateEventFromRequest(request []byte) (ApplicationEvent, error) {

	requestData := requestWithEvent{}

	// разбираем запрос
	err := go_base_frame.Json.Unmarshal(request, &requestData)
	if err != nil {
		return ApplicationEvent{}, err
	}

	return requestData.Event, nil
}

// парсим тип события
func ParseEventType(eventType string) (string, string, error) {

	// разбираем строку
	splitted := strings.Split(eventType, ".")

	// проверяем что получилось разобрать
	if len(splitted) < 2 {
		return "", "", errors.New("invalid event")
	}

	return splitted[0], splitted[1], nil
}

// получить категорию и тип сообщения
func GetEventType(appEvent *ApplicationEvent) (string, string, error) {

	return ParseEventType(appEvent.EventType)
}
