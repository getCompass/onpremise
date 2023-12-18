package SystemBot

/* Пакет для инициализации системных ботов */

import (
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
	"go_event/api/includes/type/event"
	"go_event/api/includes/type/event_broker"
	EventData "go_event/api/includes/type/event_data"
	Isolation "go_event/api/includes/type/isolation"
	"go_event/api/includes/type/system_bot/employee_observer_bot"
	"go_event/api/includes/type/system_bot/rating_bot"
	"go_event/api/includes/type/system_bot/test_bot"
	validationRule "go_event/api/includes/type/validation_rule"
)

// подписки ботов
// имя бота -> (событие -> коллбек)
var botSubscriptionList = map[string]map[string]func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error{}

// инициализируем ботов и собираем их подписки
func OnStart() {

	// грузим ботов
	loadSystemBots()

	// инициализируем подписки
	subscriptionEventList := getSystemBotsSubscriptionEventList()
	subscribeForEvents(&subscriptionEventList)

	// подключаем правила загрузки сообщений
	err := validationRule.Init()

	if err != nil {
		panic(fmt.Sprintf("can't initiate message rule set: %s", err))
	}
}

// загружает всех системных ботов, инициализируя и получая их списки подписок
func loadSystemBots() {

	// подписываемся на триггер
	listenTrigger()

	testBot.Init()
	botSubscriptionList["test_bot"] = testBot.GetSubscriptionEventList()

	ratingBot.Init()
	botSubscriptionList["rating_bot"] = ratingBot.GetSubscriptionEventList()

	employeeObserverBot.Init()
	botSubscriptionList["employee_observer_bot"] = employeeObserverBot.GetSubscriptionEventList()
}

// выполняет обработку события, рассылая его всем ботам-подписчикам
func HandleEvent(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// пробегаемся по всем списку ботов
	for k1, v1 := range botSubscriptionList {

		// у каждого бота изучаем список подписок
		for k2, v2 := range v1 {

			// если подписка найдена, то дергаем обработчик
			if k2 == appEvent.EventType {

				log.Infof("sending event %s to %s", k2, k1)

				err := v2(isolation, appEvent)

				if err != nil {
					return errors.New(fmt.Sprintf("can't process event %s error — %s", k2, err.Error()))
				}
			}
		}
	}

	return nil
}

// обрабатываем событие-триггер для ботов
// можно использовать только для тестов
func HandleTrigger(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные события
	eventData, err := EventData.TestingSystemBotTrigger{}.Decode(appEvent.EventData)

	// если с событием что-то не так
	if err != nil {
		return errors.New(fmt.Sprintf("can't process trigger event error — %s", err.Error()))
	}

	// пробегаемся по всем ботам и ищем подписчиков,
	// если такие нашлись, то отдаем им событие
	for _, subscriptionList := range botSubscriptionList {

		for event, callback := range subscriptionList {

			if event == eventData.EventType {
				_ = callback(isolation, &eventData.EventBody)
			}
		}
	}

	return nil
}

// слушает особое событие-обертку, которое позволит триггерить ботов обернутым событием
// можно использовать только для тестов
func listenTrigger() {

	// триггер слушается только на тестовых серверах
	if conf.GetConfig().ServerType != "test-server" {
		return
	}

	// создаем слушателя триггера
	listener := EventBroker.MakeListener("go_event", "system_bot", HandleTrigger)

	// добавляем слушателя для бота
	if err := EventBroker.GetListenerListStore().AttachListenerToEvent(EventData.TestingEventList.WrappedSystemBotTrigger, listener); err != nil {
		panic(fmt.Sprintf("can't add bot trigger: %s", err.Error()))
	}
}

// собирает все желаемые системными ботами события
func getSystemBotsSubscriptionEventList() []string {

	// собираем список всех подписок в словарь, чтобы не было дублей
	var subscriptionEventList = map[string]bool{}
	for _, v1 := range botSubscriptionList {

		for k1 := range v1 {
			subscriptionEventList[k1] = true
		}
	}

	eventList := make([]string, len(subscriptionEventList))
	counter := 0

	// заполняем список подписок
	for k := range subscriptionEventList {

		eventList[counter] = k
		counter++
	}

	return eventList
}

// подписывает сервис ботов на события
func subscribeForEvents(eventList *[]string) {

	// формируем подписки
	for _, v := range *eventList {

		// создаем слушателя триггера
		listener := EventBroker.MakeListener("go_event", "system_bot", HandleEvent)

		// добавляем слушателя для бота
		if err := EventBroker.GetListenerListStore().AttachListenerToEvent(v, listener); err != nil {
			panic(fmt.Sprintf("can't add bot trigger: %s", err.Error()))
		}
	}
}
