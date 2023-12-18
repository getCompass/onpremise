package Event

/** Пакет системных событий **/
/** В этом файле описана абстракция хендлера **/

/** В отличии от прямой подписки, хендлер заворачивает всю логику под капот
  Для работы хендлера достояно только объявить callback для события  **/

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
	Isolation "go_event/api/includes/type/isolation"
)

// тип — хранилище подписок
type subscriptionEventCallbackStore = map[string]map[string]SubscriptionEventCallback

type RegisterHandlerData struct {
	EventType  string
	HandlerKey string
	Callback   SubscriptionEventCallback
}

// хранилище всех подписок
var subscriptionStore = subscriptionEventCallbackStore{}

// создает обработчик события
func MakeHandler(key string, eventType string, callback SubscriptionEventCallback) RegisterHandlerData {

	return RegisterHandlerData{
		EventType:  eventType,
		HandlerKey: key,
		Callback:   callback,
	}
}

// регистрирует обработчики событий
func RegisterHandlers(isolation *Isolation.Isolation, subscriber string, registerHandlerList *[]RegisterHandlerData) {

	var itemList []SubscriptionItem

	for _, registerHandler := range *registerHandlerList {

		// проверяем наличие события в списке подписчиков, если его нет, то создает
		_, isExist := subscriptionStore[registerHandler.EventType]

		if !isExist {
			subscriptionStore[registerHandler.EventType] = map[string]SubscriptionEventCallback{}
		}

		// добавляем подписчика
		subscriptionStore[registerHandler.EventType][registerHandler.HandlerKey] = registerHandler.Callback

		itemList = append(itemList, SubscriptionItem{
			Event:         registerHandler.EventType,
			AddressMethod: "event.handle",
			Address:       conf.GetConfig().RabbitQueue,
			TriggerType:   AddressQueue,
		})
	}

	_ = SubscribeMultipleViaRabbit(isolation, subscriber, &itemList)
}

// инвалидирует подписчика
// не выполняет отписку, потому что там есть трудности с этим
func InvalidateHandler(eventType string, subscriber string) {

	// убеждаемся, что на такое событие есть подписчики
	if _, isExist := subscriptionStore[eventType]; !isExist {
		return
	}

	// пробегаемся по подписчикам события
	for handlerSubscriber := range subscriptionStore[eventType] {

		// если это интересующий нас подписчик
		if handlerSubscriber == subscriber {

			// удалям callback
			delete(subscriptionStore[eventType], handlerSubscriber)

			// если на событие не осталось подписчиков, то удаляем все событие
			if len(subscriptionStore[eventType]) == 0 {
				delete(subscriptionStore, eventType)
			}

			break
		}
	}
}

// вызывает все существующие обработчики для события
// сюда прилетит все, что имеет в качестве метода обработчик event.handle
func Handle(appEvent *ApplicationEvent) {

	log.Infof("handling event %s", appEvent.EventType)

	// проверяем, есть ли подписчик соответствующий
	if _, isExists := subscriptionStore[appEvent.EventType]; !isExists {

		log.Infof("event %s doesn't exist", appEvent.EventType)
		return
	}

	// бежим по всем подписчикам и вызываем все коллбэки
	for subscriptionKey, callback := range subscriptionStore[appEvent.EventType] {

		log.Infof("calling callback %s for event %s", subscriptionKey, appEvent.EventType)
		if err := callback(appEvent); err != nil {
			log.Errorf("error occurred during processing event %s with %s subscription key: %s", appEvent.EventType, subscriptionKey, err.Error())
		}
	}
}
