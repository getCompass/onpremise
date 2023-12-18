package usernotification

import (
	"encoding/json"
)

// константы с версиями
const (
	_handler1 = 1
	_handler2 = 2
)

// тип пользовательских уведомлений
// !!! дублируется в php_pivot
const eventTypeConversationMessageMask = 1 << 1
const eventTypeThreadMessageMask = 1 << 2
const eventTypeInviteMessageMask = 1 << 3
const eventTypeBelongsToGGroupConversationMask = 1 << 4
const eventTypeMemberNotificationMask = 1 << 5

const eventTypeAllMask = eventTypeConversationMessageMask |
	eventTypeThreadMessageMask |
	eventTypeInviteMessageMask |
	eventTypeBelongsToGGroupConversationMask |
	eventTypeMemberNotificationMask

// структура поля extra
type extraField struct {
	Version       int             `json:"handler_version"`
	Extra         json.RawMessage `json:"extra"`
	isInitialized bool
	ExtraBody     interface{}
}

// нужно ли отправлять analyticspush уведомление пользователю в зависимости от event_type и поля event_mask в extra
func (d *UserNotificationStruct) IsEventSnoozed(eventType int) bool {

	// инициализируем extra
	d.initExtra()

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case _handler1:
		return d.ExtraField.ExtraBody.(extraHandlerVersion1).isEventSnoozed(d.SnoozedUntil, eventType)
	case _handler2:
		return d.ExtraField.ExtraBody.(extraHandlerVersion2).isEventSnoozed(d.SnoozedUntil, eventType)
	}

	return false
}

// нужно ли отправлять analyticspush уведомление пользователю в зависимости от event_type и поля snooze_mask в extra
func (d *UserNotificationStruct) IsEventDisabled(eventType int) bool {

	// инициализируем extra
	d.initExtra()

	// в зависимости от версии extra вызываем нужный обработчик
	switch d.ExtraField.Version {

	case _handler1:
		return d.ExtraField.ExtraBody.(extraHandlerVersion1).isEventDisabled(eventType)
	case _handler2:
		return d.ExtraField.ExtraBody.(extraHandlerVersion2).isEventDisabled(eventType)
	}

	return false
}

// инициализируем, распаковываем входящую extra
// @long - switch..case
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

		// добавляем маску уведомлений для поддержки маски уведомлений для участников
		temp.EventMask |= eventTypeMemberNotificationMask

		if temp.SnoozeMask != 0 {
			temp.SnoozeMask |= eventTypeMemberNotificationMask
		}

		if temp.SnoozeMask == eventTypeAllMask {
			temp.SnoozeMask = 0
		}

		d.ExtraField.ExtraBody = temp

	case _handler2:

		temp := extraHandlerVersion2{}

		err := json.Unmarshal(d.ExtraField.Extra, &temp)

		if err != nil {
			panic(err)
		}

		if temp.SnoozeMask != 0 {
			temp.SnoozeMask |= eventTypeMemberNotificationMask
		}

		if temp.SnoozeMask == eventTypeAllMask {
			temp.SnoozeMask = 0
		}

		d.ExtraField.ExtraBody = temp
	}

	// помечаем, что инициализирована
	d.ExtraField.isInitialized = true
}
