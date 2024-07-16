package structures

// Пакет содержит описание структур используемых в микросервисе

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
)

// -------------------------------------------------------
// основные структуры, аналог define.php
// -------------------------------------------------------

// структура запроса на отправку события
type UserConnectionStruct struct {
	UserId          int64
	SenderNodeId    int64
	LastConnectedAt int64
}

// структура юзера
type UserStruct struct {
	UserId int64 `json:"user_id"`
}

// структура запроса на отправку события для go_sender_balancer
type SendEventRequestStruct struct {
	UserList         []UserStruct `json:"user_list"`
	Event            string       `json:"event"`
	EventVersionList interface{}  `json:"event_version_list"`
	PushData         interface{}  `json:"push_data"`
	WSUsers          interface{}  `json:"ws_users,omitempty"`
	RoutineKey       string       `json:"routine_key"`
	Uuid             string       `json:"uuid"`
	IsNeedPush       int          `json:"is_need_push"`
}

// структура запроса на отправку события батчинг-методом для go_sender_balancer
type SendEventBatchingRequestStruct struct {
	BatchingData []SendEventRequestStruct `json:"batching_data"`
}

// структура запроса на отправку события для go_sender_balancer
type BroadcastEventRequestStruct struct {
	Event            string      `json:"event"`
	EventVersionList interface{} `json:"event_version_list"`
	WSUsers          interface{} `json:"ws_users,omitempty"`
	RoutineKey       string      `json:"routine_key"`
	Uuid             string      `json:"uuid"`
}

// структура запроса при создании конференции Jitsi
type JitsiConferenceCreatedRequestStruct struct {
	UserId           int64       `json:"user_id"`
	Event            string      `json:"event"`
	EventVersionList interface{} `json:"event_version_list"`
	PushData         interface{} `json:"push_data"`
	WSUsers          interface{} `json:"ws_users,omitempty"`
	Uuid             string      `json:"uuid"`
	TimeToLive       int64       `json:"time_to_live"`
	RoutineKey       string      `json:"routine_key"`
}

// структура запроса при создании конференции Jitsi
type SendJitsiVoIPPushRequestStruct struct {
	UserId     int64       `json:"user_id"`
	PushData   interface{} `json:"push_data"`
	Uuid       string      `json:"uuid"`
	RoutineKey string      `json:"routine_key"`
}

// -------------------------------------------------------
// Rabbit
// -------------------------------------------------------

// структура для отправки задач в очередь RabbitMq
type RabbitTask struct {
	Messages [][]byte `json:"message"`
}

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

// получаем список пользователей из списка соединений
func ConvertUserConnectionListToUserList(userConnectionList []UserConnectionStruct) []int64 {

	var userList []int64

	for _, item := range userConnectionList {
		userList = append(userList, item.UserId)
	}

	userList = functions.UniqueInt64(userList)

	return userList
}
