package EventData

/* Пакет для работы с данными события.
   Важно — имя каждого типа должно начинаться с категории, к которой принадлежит событие */
/* В этом файле описаны структуры данных событий категории testing */

import (
	"encoding/json"
	"errors"
	"github.com/getCompassUtils/go_base_frame"
	SystemEvent "go_event/api/includes/type/event"
)

// список событий, чтобы не использовать строки при создании подписок
var TestingEventList = struct {
	SystemBotFileMessageRequested string
	SystemBotTextMessageRequested string
	WrappedSystemBotTrigger       string
}{
	SystemBotFileMessageRequested: "testing.system_bot_file_message_requested",
	SystemBotTextMessageRequested: "testing.system_bot_text_message_requested",
	WrappedSystemBotTrigger:       "testing.system_bot_trigger",
}

// событие запрошена пересылка текста через бота
type TestingSystemBotTextMessageRequested struct {
	UserId int64  `json:"user_id"` // кому переслать
	Text   string `json:"text"`    // текст, который нужно переслать
}

// декодит событие запрошена пересылка текста через бота
func (eventData TestingSystemBotTextMessageRequested) Decode(raw json.RawMessage) (TestingSystemBotTextMessageRequested, error) {

	// декодим данные события
	err := go_base_frame.Json.Unmarshal(raw, &eventData)

	// проверяем, что это нужное событие
	if err != nil {
		return eventData, errors.New("error occurred during decoding employee.metric_delta event data")
	}

	return eventData, nil
}

// событие запрошена пересылка текста через бота
type TestingSystemBotFileMessageRequested struct {
	UserId   int64  `json:"user_id"`   // кому переслать
	FileMap  string `json:"file_map"`  // файл для пересылки
	FileName string `json:"file_name"` // название файла для пересылки
}

// декодит событие запрошена пересылка текста через бота
func (eventData TestingSystemBotFileMessageRequested) Decode(raw json.RawMessage) (TestingSystemBotFileMessageRequested, error) {

	// декодим данные события
	err := go_base_frame.Json.Unmarshal(raw, &eventData)

	// проверяем, что это нужное событие
	if err != nil {
		return eventData, errors.New("error occurred during decoding employee.metric_delta event data")
	}

	return eventData, nil
}

// упакованное событие
type TestingSystemBotTrigger struct {
	EventType string                       `json:"event_type"`
	EventBody SystemEvent.ApplicationEvent `json:"event_body"`
}

// декодит событие запрошена пересылка текста через бота
func (eventData TestingSystemBotTrigger) Decode(raw json.RawMessage) (TestingSystemBotTrigger, error) {

	// декодим данные события
	err := go_base_frame.Json.Unmarshal(raw, &eventData)

	// проверяем, что это нужное событие
	if err != nil {
		return eventData, errors.New("error occurred during decoding employee.metric_delta event data")
	}

	return eventData, nil
}
