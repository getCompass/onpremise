package user_notification

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
)

// структура обработчика версии 2
type extraHandlerVersion2 struct {
	SnoozeMask int64 `json:"snooze_mask"`
	IsSnoozed  int   `json:"is_snoozed"`
}

// определяет, был ли ивент замьючен с таймером
func (d extraHandlerVersion2) isEventSnoozed(snoozedUntil int64, eventBitMask int64) bool {

	if d.IsSnoozed == 1 {
		return true
	}

	return int64(snoozedUntil) > functions.GetCurrentTimeStamp() && (eventBitMask&d.SnoozeMask != eventBitMask || d.SnoozeMask == 0)
}
