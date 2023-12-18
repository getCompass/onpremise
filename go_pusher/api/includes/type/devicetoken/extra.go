package devicetoken

import (
	"encoding/json"
)

// константы с версиями
const (
	_handler1 = 1
)

// структура поля extra
type extraField struct {
	Version       int             `json:"handler_version"`
	Extra         json.RawMessage `json:"extra"`
	isInitialized bool
	ExtraBody     interface{}
}

// нужно ли отправлять analyticspush уведомление пользователю в зависимости от event_type и поля event_mask в extra
func (d *RowStruct) IsEventSnoozed(eventType int) bool {

	// инициализируем extra
	d.initExtra()

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case _handler1:
		return d.ExtraField.ExtraBody.(extraHandlerVersion1).isEventSnoozed(d.SnoozedUntil, eventType)
	}

	return false
}

// нужно ли отправлять analyticspush уведомление пользователю в зависимости от event_type и поля snooze_mask в extra
func (d *RowStruct) IsEventDisabled(eventType int) bool {

	// инициализируем extra
	d.initExtra()

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case _handler1:
		return d.ExtraField.ExtraBody.(extraHandlerVersion1).isEventDisabled(eventType)
	}

	return false
}

// инициализируем, распаковываем входящую extra
func (d *RowStruct) initExtra() {

	// если уже инициализирована
	if d.ExtraField.isInitialized {
		return
	}

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case _handler1:

		temp := extraHandlerVersion1{}

		err := json.Unmarshal(d.ExtraField.Extra, &temp)
		if err != nil {
			panic(err)
		}

		d.ExtraField.ExtraBody = temp
	}

	// помечаем, что инициализирована
	d.ExtraField.isInitialized = true
}
