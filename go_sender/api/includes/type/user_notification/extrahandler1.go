package user_notification

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
)

// структура обработчика версии 1
type extraHandlerVersion1 struct {
	SnoozeMask int64 `json:"snooze_mask"`
}

// определяет, был ли ивент замьючен с таймером
func (d extraHandlerVersion1) isEventSnoozed(snoozedUntil int64, eventBitMask int64) bool {

	return snoozedUntil > functions.GetCurrentTimeStamp() && (eventBitMask&d.SnoozeMask != eventBitMask || d.SnoozeMask == 0)
}
