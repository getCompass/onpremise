package usernotification

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
)

// структура обработчика версии 2
type extraHandlerVersion2 struct {
	EventMask  int `json:"event_mask"`
	SnoozeMask int `json:"snooze_mask"`
}

// определяет, был ли ивент замьючен с таймером
func (d extraHandlerVersion2) isEventSnoozed(snoozedUntil int64, eventBitMask int) bool {

	return snoozedUntil > functions.GetCurrentTimeStamp() && eventBitMask&d.SnoozeMask != eventBitMask
}

// определяет, был ли ивент замьючен глобально
func (d extraHandlerVersion2) isEventDisabled(eventBitMask int) bool {

	return eventBitMask == 0 || eventBitMask&d.EventMask != eventBitMask
}
