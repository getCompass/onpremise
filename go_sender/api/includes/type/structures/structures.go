package structures

// Пакет содержит описание структур используемых в микросервисе

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_sender/api/includes/type/push"
	"go_sender/api/includes/type/user_notification"
)

// -------------------------------------------------------
// основные структуры, аналог define.php
// -------------------------------------------------------

// структура запроса на отправку события
type UserConnectionStruct struct {
	UserId          int64
	LastConnectedAt int64
}

// структура юзера
type UserStruct struct {
	UserId int64
}

// структура item пользователя, которому необходимо отправить сообщение
type SendEventUserStruct struct {
	UserId        int64 `json:"user_id"`
	NeedPush      int   `json:"need_push,omitempty"`
	NeedForcePush int   `json:"need_force_push"`
}

// ожидаемая структура каждой версии события
type SendEventVersionItemStruct struct {
	Version int         `json:"version"`
	Data    interface{} `json:"data"`
}

// структура запроса на отправку события для go_sender
type SendEventRequestStruct struct {
	UserList         []SendEventUserStruct        `json:"user_list"`
	Event            string                       `json:"event"`
	EventVersionList []SendEventVersionItemStruct `json:"event_version_list"`
	PushData         push.PushDataStruct          `json:"push_data,omitempty"`
	WSUsers          interface{}                  `json:"ws_users,omitempty"`
	Uuid             string                       `json:"uuid"`
	RoutineKey       string                       `json:"routine_key"`
	ServerTime       int64                        `json:"server_time"`
	Channel          string                       `json:"channel"`
}

// структура запроса на отправку события всем юзерам для go_sender
type SendEventToAllRequestStruct struct {
	Event            string                       `json:"event"`
	EventVersionList []SendEventVersionItemStruct `json:"event_version_list"`
	PushData         push.PushDataStruct          `json:"push_data,omitempty"`
	WSUsers          interface{}                  `json:"ws_users,omitempty"`
	Uuid             string                       `json:"uuid"`
	RoutineKey       string                       `json:"routine_key"`
	IsNeedPush       int                          `json:"is_need_push"`
	ServerTime       int64                        `json:"server_time"`
	Channel          string                       `json:"channel"`
}

type SendPushRequestStruct struct {
	UserNotificationList []user_notification.UserNotificationStruct `json:"user_notification_list"`
	PushData             push.PushDataStruct                        `json:"push_data"`
	Uuid                 string                                     `json:"uuid"`
	NeedForcePush        int                                        `json:"need_force_push"`
}

type SendVoIPRequestStruct struct {
	UserNotification user_notification.UserNotificationStruct `json:"user_notification"`
	PushData         interface{}                              `json:"push_data"`
	Uuid             string                                   `json:"uuid"`
	TimeToLive       int64                                    `json:"time_to_live,omitempty"`
	SentDeviceList   []string                                 `json:"sent_device_list,omitempty"`
}

type SendJitsiVoIPRequestStruct struct {
	UserId         int64       `json:"user_id"`
	PushData       interface{} `json:"push_data"`
	Uuid           string      `json:"uuid"`
	TimeToLive     int64       `json:"time_to_live,omitempty"`
	SentDeviceList []string    `json:"sent_device_list,omitempty"`
}

type SendJitsiConferenceCreatedEventRequestStruct struct {
	UserId           int64                        `json:"user_id"`
	Event            string                       `json:"event"`
	EventVersionList []SendEventVersionItemStruct `json:"event_version_list"`
	PushData         push.PushDataStruct          `json:"push_data,omitempty"`
	WSUsers          interface{}                  `json:"ws_users,omitempty"`
	Uuid             string                       `json:"uuid"`
	TimeToLive       int64                        `json:"time_to_live"`
	RoutineKey       string                       `json:"routine_key"`
	Channel          string                       `json:"channel"`
}

type SendJitsiVoipPushRequestStruct struct {
	UserId     int64               `json:"user_id"`
	PushData   push.PushDataStruct `json:"push_data,omitempty"`
	Uuid       string              `json:"uuid"`
	RoutineKey string              `json:"routine_key"`
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

// конвертируем массив соединений в массив пользователей
func ConvertUserConnectionListToUserStructList(userConnectionList []UserConnectionStruct) []int64 {

	// массив пользователей который вернем
	var userList []int64

	// пробегаемся по массиву соединений
	for _, item := range userConnectionList {

		// проверяем есть ли в массиве уже такой элемент
		isExist, _ := functions.InArray(item.UserId, userList)

		if !isExist {

			// добавляем пользователя в ответ
			userList = append(userList, item.UserId)
		}
	}

	return userList
}
