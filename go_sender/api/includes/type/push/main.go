package push

// типы пушей
const TextPushType = 2

// типы сообщений
const TextMessageType = "text"
const FileMessageType = "file"

// общая структура push_data
type PushDataStruct struct {
	BadgeIncCount   int64           `json:"badge_inc_count,omitempty"`
	EventType       int64           `json:"event_type,omitempty"`
	PushType        int             `json:"push_type,omitempty"`
	TextPush        *TextPushStruct `json:"text_push,omitempty"`
	VoipPush        *VoipPushStruct `json:"voip_push,omitempty"`
	IsNeedForcePush int             `json:"is_need_force_push,omitempty"`
}
