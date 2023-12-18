package push

import (
	"go_pusher/api/includes/type/apns"
	"go_pusher/api/includes/type/firebase"
	"go_pusher/api/includes/type/huawei"
	"go_pusher/api/includes/type/push/badge"
	"go_pusher/api/includes/type/push/textpush"
	"go_pusher/api/includes/type/push/voippush"
)

// структура объекта с пуш уведомлением
type PushDataStruct struct {
	BadgeIncCount   int64                `json:"badge_inc_count,omitempty"`
	EventType       int                  `json:"event_type,omitempty"`
	PushType        int                  `json:"push_type,omitempty"`
	BadgePush       *badge.PushStruct    `json:"badge_push,omitempty"`
	TextPush        *textpush.PushStruct `json:"text_push,omitempty"`
	VoipPush        *voippush.PushStruct `json:"voip_push,omitempty"`
	IsNeedForcePush int                  `json:"is_need_force_push,omitempty"`
}

// информация, необходимая для отправки пушей
type ProviderInfo struct {
	Type             int                `json:"type"`
	FirebaseSendInfo *firebase.SendInfo `json:"firebase_send_info,omitempty"`
	ApnsSendInfo     *apns.SendInfo     `json:"apns_send_info,omitempty"`
	HuaweiSendInfo   *huawei.SendInfo   `json:"huawei_send_info,omitempty"`
}

// структура таска на отправку пуша
type PushTaskStruct struct {
	ServerUid        string
	Type             int
	Uuid             string         `json:"uuid"`
	ProviderInfoList []ProviderInfo `json:"provider_info_list"`
	PushData         PushDataStruct `json:"push_data"`
}
