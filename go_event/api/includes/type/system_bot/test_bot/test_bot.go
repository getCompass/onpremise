package testBot

/* Пакет для обработки событий системным ботом "бот тестирования" */

import (
	"errors"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
	"go_event/api/includes/type/chat_message"
	"go_event/api/includes/type/event"
	"go_event/api/includes/type/event_data"
	Isolation "go_event/api/includes/type/isolation"
)

// идентификатор пользователя, от имени которого вещает бот
var _botUserId int64

// инициализируем локальные данные для бота
func Init() {

	// получаем токен бота из конфига
	_botUserId = conf.GetConfig().TestBotUserId

	if _botUserId == 0 {

		log.Error("test bot can not fetch user id, will try next time")
		return
	}

	log.Infof("test bot was started with user id %d", _botUserId)
}

// получаем список событий, которые нужны этому боту, и их обработчики
func GetSubscriptionEventList() map[string]func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	return map[string]func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error{
		EventData.TestingEventList.SystemBotTextMessageRequested: onResendTextMessage,
		EventData.TestingEventList.SystemBotFileMessageRequested: onResendFileMessageRequested,
	}
}

// отправляет указанный текст от лица бота
func onResendTextMessage(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные события
	eventData, err := EventData.TestingSystemBotTextMessageRequested{}.Decode(appEvent.EventData)

	// если с событием что-то не так
	if err != nil {
		return err
	}

	// шлем от лица бота оповещений
	botId := conf.GetConfig().AuthBotUserId
	if botId == 0 {
		return errors.New("can't fetch bot id for message resend")
	}

	return ChatMessage.SendMessageListToUser(
		isolation,
		botId,
		eventData.UserId,
		ChatMessage.ConversationMessageDataList{
			ChatMessage.MakeTextMessage(eventData.Text),
		},
	)
}

// отправляет указанный файл от лица бота
func onResendFileMessageRequested(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные события
	eventData, err := EventData.TestingSystemBotFileMessageRequested{}.Decode(appEvent.EventData)

	// если с событием что-то не так
	if err != nil {
		return err
	}

	// шлем от лица бота оповещений
	botId := conf.GetConfig().AuthBotUserId
	if botId == 0 {
		return errors.New("can't fetch bot id for message resend")
	}

	return ChatMessage.SendMessageListToUser(
		isolation,
		botId,
		eventData.UserId,
		ChatMessage.ConversationMessageDataList{
			ChatMessage.MakeFileMessage(eventData.FileMap, eventData.FileName),
		},
	)
}
