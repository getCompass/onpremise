package ratingBot

/* Пакет для обработки событий системным ботом "бот рейтинга" */

import (
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
	"go_event/api/includes/type/chat_message"
	"go_event/api/includes/type/event"
	"go_event/api/includes/type/event_data"
	Isolation "go_event/api/includes/type/isolation"
	"go_event/api/includes/type/localizer"
	"go_event/api/includes/type/validation_rule"
)

// идентификатор пользователя, от имени которого вещает бот
var _botUserId int64 = 0

// инициализируем локальные данные для бота
func Init() {

	// получаем токен бота из конфига == боту авторизации
	_botUserId = conf.GetConfig().AuthBotUserId

	if _botUserId == 0 {

		log.Error("rating bot can not fetch user id, will try next time")
		return
	}

	log.Infof("Rating bot was started with user id %d", _botUserId)
}

// получаем список событий, которые нужны этому боту, и их обработчики
func GetSubscriptionEventList() map[string]func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	return map[string]func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error{
		EventData.CompanyRatingEventList.ActionTotalFixed:         OnSendRatingToConversation,
		EventData.CompanyRatingEventList.EmployeeMetricTotalFixed: OnSendCompanyEmployeeMetricStatistic,
		EventData.CompanyRatingEventList.WorksheetRatingFixed:     OnWorksheetFixed,
	}
}

// метод для отправки рейтинга в чат
func OnSendRatingToConversation(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные события
	eventData := EventData.CompanyRatingActionTotalFixed{}
	err := go_base_frame.Json.Unmarshal(appEvent.EventData, &eventData)
	if err != nil {
		return errors.New("source data is invalid")
	}

	// проверяем, подходит ли событие
	// не считаем это ошибкой, поскольку просто пользователь не может получать эти сообщения
	if !validationRule.CheckSendingRule(appEvent.EventType) {
		return nil
	}

	message := ChatMessage.MakeRatingMessageData(eventData.Year, eventData.Week, eventData.Count, eventData.Name)

	return ChatMessage.SendMessageListToConversation(
		isolation,
		_getBotUserId(),
		eventData.ConversationMap,
		ChatMessage.ConversationMessageDataList{message},
	)
}

// отсылает сообщение со статистикой о метриках сотрудника в компании
func OnSendCompanyEmployeeMetricStatistic(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные события
	eventData := EventData.CompanyRatingEmployeeMetricTotalFixed{}
	err := go_base_frame.Json.Unmarshal(appEvent.EventData, &eventData)

	if err != nil {
		return fmt.Errorf("error occurred during decoding %s event data", appEvent.EventType)
	}

	// конвертим из типов события в типы сообщения
	// это концептуальная штука, чтобы не связывать сообщения с событиями
	var messageMetricList = make([]ChatMessage.RatingCompanyMetricCountItemStruct, len(eventData.MetricCountItemList))

	for key, val := range eventData.MetricCountItemList {
		messageMetricList[key] = ChatMessage.RatingCompanyMetricCountItemStruct{MetricType: val.MetricType, Count: val.Count}
	}

	// проверяем, подходит ли событие
	if !validationRule.CheckSendingRule(appEvent.EventType) {
		return nil
	}

	// получаем локаль пользователя
	companyLocale := conf.GetConfig().CompanyLocale

	// готовим текст сообщения
	text, _ := localizer.GetString("rating.company_employee_metric_fixed", companyLocale)
	message := ChatMessage.MakeRatingCompanyEmployeeMetric(text, eventData.CompanyName, eventData.PeriodStartDate, eventData.PeriodEndDate, messageMetricList)

	err = ChatMessage.SendMessageListToConversation(
		isolation,
		_getBotUserId(),
		eventData.ConversationMap,
		ChatMessage.ConversationMessageDataList{message},
	)

	return err
}

// метод реакция на событие «зафиксированы рабочие часы за период времени»
// @long
func OnWorksheetFixed(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные события
	eventData, err := EventData.CompanyRatingWorksheetRatingFixed{}.Decode(appEvent.EventData)

	// если с событием что-то не так
	if err != nil {
		return err
	}

	// если нет сотрудников для напоминания, то ничего не делаем
	if len(eventData.LeaderUserWorkItemList) == 0 && len(eventData.DrivenUserWorkItemList) == 0 {
		return nil
	}

	// проверяем, подходит ли событие
	if !validationRule.CheckSendingRule(appEvent.EventType) {
		return nil
	}

	// получаем язык пользователя
	messageLocale := conf.GetConfig().CompanyLocale

	// конвертим из типов события в типы  сообщения
	// это концептуальная штука, чтобы не связывать сообщения с событиями
	// наверняка можно как-то проще скастить это дело, но у меня не вышло
	var messageLeaderList = make([]ChatMessage.EmployeeEditorWorksheetRatingUserItem, len(eventData.LeaderUserWorkItemList))
	var messageDrivenList = make([]ChatMessage.EmployeeEditorWorksheetRatingUserItem, len(eventData.DrivenUserWorkItemList))

	for key, val := range eventData.LeaderUserWorkItemList {
		messageLeaderList[key] = ChatMessage.EmployeeEditorWorksheetRatingUserItem{UserId: val.UserId, WorkTime: val.WorkTime}
	}

	for key, val := range eventData.DrivenUserWorkItemList {
		messageDrivenList[key] = ChatMessage.EmployeeEditorWorksheetRatingUserItem{UserId: val.UserId, WorkTime: val.WorkTime}
	}

	// формируем сообщение для пользователя
	text, _ := localizer.GetString("employee.editor_worksheet_fixed", messageLocale)
	message := ChatMessage.MakeEmployeeEditorWorksheetRatingMessage(
		text, eventData.PeriodId,
		eventData.PeriodStartDate,
		eventData.PeriodEndDate,
		messageLeaderList,
		messageDrivenList,
	)

	// шлем сообщения
	if err = ChatMessage.SendMessageListToConversation(isolation, _botUserId, eventData.ConversationMap, ChatMessage.ConversationMessageDataList{message}); err != nil {
		log.Infof("worksheet rating message wasn't send to %d: reason: %s", eventData.ConversationMap, err.Error())
	}

	return nil
}

// region protected

// возвращает идентификатор пользователя, от имени которого вещает бот
func _getBotUserId() int64 {

	// если идентификатор не установлен, то пытаемся его получить
	if _botUserId == 0 {
		Init()
	}

	return _botUserId
}

// endregion protected
