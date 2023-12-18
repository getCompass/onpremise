package employeeObserverBot

/* Пакет для обработки событий системным ботом "бот карточки сотрудника" */

import (
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
	"go_event/api/includes/type/chat_message"
	"go_event/api/includes/type/event"
	"go_event/api/includes/type/event_data"
	Isolation "go_event/api/includes/type/isolation"
	"go_event/api/includes/type/localizer"
	"go_event/api/includes/type/validation_rule"
	"time"
)

// идентификатор пользователя, от имени которого вещает бот
var botUserId int64 = 0

// инициализируем локальные данные для бота
func Init() {

	// получаем токен бота из конфига == боту авторизации
	botUserId = conf.GetConfig().AuthBotUserId

	if botUserId == 0 {

		log.Error("employee observer bot can not fetch user id, will try next time")
		return
	}

	log.Infof("employee observer bot was started with user id %d", botUserId)
}

// получаем список событий, которые нужны этому боту, и их обработчики
func GetSubscriptionEventList() map[string]func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	return map[string]func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error{
		EventData.UserCompanyEventList.SystemBotMoved:     OnSystemBotMoved,
		EventData.UserCompanyEventList.AnniversaryReached: OnAnniversaryReached,
	}
}

// отправляет собщение о смене типа чата от бота
func OnSystemBotMoved(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные события
	eventData, err := EventData.SystemBotMoved{}.Decode(appEvent.EventData)

	// если с событием что-то не так
	if err != nil {
		return err
	}

	// шлем от лица бота оповещений
	botId := conf.GetConfig().AuthBotUserId
	if botId == 0 {
		return errors.New("can't fetch bot id for message resend")
	}

	// формируем сообщение
	message := ChatMessage.MakeSystemBotMessagesMovedNotificationMessage()

	return ChatMessage.SendMessageListToUser(
		isolation,
		botId,
		eventData.UserId,
		ChatMessage.ConversationMessageDataList{message},
	)
}

// метод-реакция на событие дельты времени работы в компании
// @long
func OnAnniversaryReached(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные события
	eventData, err := EventData.MemberAnniversaryReached{}.Decode(appEvent.EventData)

	// если с событием что-то не так
	if err != nil {
		return err
	}

	// проверяем, что руководитель может получить сообщение
	if validationRule.CheckSendingRule(appEvent.EventType) {

		// генерируем сообщение для руководителя
		editorText, err := makeTextForEditorAnniversaryMessage(eventData.HiredAt, eventData.EmployeeUserId)

		if err == nil {

			editorMessage := ChatMessage.MakeEmployeeEditorAnniversaryMessage(eventData.EmployeeUserId, editorText, eventData.HiredAt)

			// шлем сообщение руководителю
			_ = ChatMessage.SendMessageListToConversation(
				isolation, getBotUserId(), eventData.ConversationMap, ChatMessage.ConversationMessageDataList{editorMessage},
			)
		}
	}

	// здесь не обрабатываются отсылки отдельно взятых сообщений
	// чтобы логика не ломалась
	return nil
}

/** region protected **/

// получает локализованный текст для сообщения-напоминания о годовщине работы в компании
func makeTextForEditorAnniversaryMessage(hiredAt int64, employeeUserId int64) (string, error) {

	// получаем локаль для пользователя
	messageLocale := conf.GetConfig().CompanyLocale

	// считаем время в годах
	currentYear := time.Now().Year()
	hireYear := time.Unix(hiredAt, 0).Year()

	// список подстановок
	substitutionList := map[string]string{
		"employee_name": fmt.Sprintf("[\"$\"|%d|\"user_id\"]", employeeUserId),
		"year_count":    functions.IntToString(currentYear - hireYear),
	}

	return localizer.GetStringWithSubstitutions("employee.editor_anniversary_reached", messageLocale, substitutionList)
}

// возвращает идентификатор пользователя, от имени которого вещает бот
func getBotUserId() int64 {

	// если идентификатор не установлен, то пытаемся его получить
	if botUserId == 0 {
		Init()
	}

	return botUserId
}

/** endregion protected **/
