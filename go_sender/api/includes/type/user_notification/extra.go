package user_notification

import (
	"encoding/json"
	"go_sender/api/includes/type/db/company_data"
)

// константы с версиями
const (
	_handler1 = 1
	_handler2 = 2
)

// структура поля extra
type ExtraField struct {
	Version       int             `json:"handler_version"`
	Extra         json.RawMessage `json:"extra"`
	isInitialized bool
	ExtraBody     interface{}
}

// нужно ли отправлять push уведомление пользователю в зависимости от event_type и поля event_mask в extra
func (d *UserNotificationStruct) IsEventSnoozed(eventType int64) bool {

	// инициализируем extra
	d.initExtra()

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case _handler1:
		return false

	case _handler2:
		return false
	}

	return false
}

// инициализируем, распаковываем входящую extra
func (d *UserNotificationStruct) initExtra() {

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

	case _handler2:

		temp := extraHandlerVersion2{}

		err := json.Unmarshal(d.ExtraField.Extra, &temp)
		if err != nil {
			panic(err)
		}

		d.ExtraField.ExtraBody = temp
	}

	// помечаем, что инициализирована
	d.ExtraField.isInitialized = true
}

// получаем extra пользователя
func GetUserExtra(row *company_data.NotificationRow) (ExtraField, error) {

	var extra ExtraField

	// получаем extra пользователя
	err := json.Unmarshal([]byte(row.Extra), &extra)

	return extra, err
}
