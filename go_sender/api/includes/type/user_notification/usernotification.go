package user_notification

// структура для бд user_notification
type UserNotificationStruct struct {
	UserID        int64      `json:"user_id"`
	SnoozedUntil  int64      `json:"snoozed_until,omitempty"`
	CreatedAt     int        `json:"created_at,omitempty"`
	UpdatedAt     int        `json:"updated_at,omitempty"`
	Token         string     `json:"token"`
	DeviceList    []string   `json:"device_list"`
	ExtraField    ExtraField `json:"extra,omitempty"`
	NeedForcePush int        `json:"need_force_push"`
}
