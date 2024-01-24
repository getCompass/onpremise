package ChatMessage

/** Пакет работы с сообщениями */
/** Вспомогательные функции для отправки сообщений от системных ботов */

import (
	"errors"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_event/api/includes/type/event"
	Isolation "go_event/api/includes/type/isolation"
)

type MessageTypeListStruct struct {
	Common   CommonMessageTypeListStruct
	Employee EmployeeMessageTypeListStruct
	Rating   RatingMessageTypeListStruct
}

// данные для отправки сообщения в чат
type GroupConversationMessageData struct {
	BotUserId       int64       `json:"bot_user_id"`
	ConversationMap string      `json:"conversation_map"`
	MessageList     interface{} `json:"message_list"`
}

// данные для отправки сообщения в сингл диалог
type SingleConversationMessageData struct {
	BotUserId      int64       `json:"bot_user_id"`
	ReceiverUserId int64       `json:"receiver_user_id"`
	MessageList    interface{} `json:"message_list"`
}

// данные для отправки сообщения тред
type ThreadMessageData struct {
	BotUserId   int64       `json:"bot_user_id"`
	ThreadMap   string      `json:"thread_map"`
	MessageList interface{} `json:"message_list"`
}

// список сообщений для сингла
type ConversationMessageDataList []interface{}

// список списков поддерживаемых типов сообщений
var MessageTypeList = MessageTypeListStruct{
	Common:   CommonMessageTypeList,
	Employee: EmployeeMessageTypeList,
	Rating:   RatingMessageTypeList,
}

// инициирует отправку списка сообщений от бота в сингл с пользователем
func SendMessageListToUser(isolation *Isolation.Isolation, botUserId int64, receiverUserId int64, messageListData ConversationMessageDataList) error {

	// формируем событие для отправки
	eventData := SingleConversationMessageData{
		BotUserId:      botUserId,
		ReceiverUserId: receiverUserId,
		MessageList:    messageListData,
	}

	appEvent, err := Event.CreateEvent("message.send_system_message_list_to_user", "bot", functions.Int64ToString(botUserId), eventData)
	if err != nil {
		return errors.New("can't encode the event")
	}

	// пушим событие
	return Event.Dispatch(isolation, &appEvent)
}

// инициирует отправку списка сообщений от бота в чат
func SendMessageListToConversation(isolation *Isolation.Isolation, botUserId int64, conversationMap string, messageListData ConversationMessageDataList) error {

	// формируем событие для отправки
	eventData := GroupConversationMessageData{
		BotUserId:       botUserId,
		ConversationMap: conversationMap,
		MessageList:     messageListData,
	}

	appEvent, err := Event.CreateEvent("message.send_system_message_list_to_conversation", "bot", functions.Int64ToString(botUserId), eventData)
	if err != nil {
		return errors.New("can't encode the event")
	}

	// пушим событие
	return Event.Dispatch(isolation, &appEvent)
}

// инициирует отправку сообщения от бота в тред
func SendMessageListToThread(isolation *Isolation.Isolation, botUserId int64, threadMap string, messageListData *ConversationMessageDataList) error {

	// формируем событие для отправки
	eventData := ThreadMessageData{
		BotUserId:   botUserId,
		ThreadMap:   threadMap,
		MessageList: messageListData,
	}

	appEvent, err := Event.CreateEvent("message.send_system_message_to_thread", "bot", functions.Int64ToString(botUserId), eventData)
	if err != nil {
		return errors.New("can't encode the event")
	}

	// пушим событие
	return Event.Dispatch(isolation, &appEvent)
}
