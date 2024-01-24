package Event

/** Пакет системных событий **/
/** В этом файле описана логика подписки на события **/

import (
	"encoding/json"
	"errors"
	"go_event/api/conf"
	Isolation "go_event/api/includes/type/isolation"
)

// типы адресов для подписок
// на основании типа ведется вещание событий
const (
	AddressQueue    = 1
	AddressExchange = 2
	Tcp             = 3
	TcpQueue        = 4
	TaskTcpQueue    = 5
)

// тип - предмет подписки (кто хочет какое событие и в каком виде), на его основе создается слушатель;
// строится на основе данных извне
type SubscriptionItem struct {
	Event         string          `json:"event"`          // на какое событие оформлена подписка
	TriggerType   int             `json:"trigger_type"`   // тип получателя (exchange, queue, tcp)
	Address       string          `json:"address"`        // сам адрес
	AddressMethod string          `json:"address_method"` // метод, которые долен быть задействован на адресе
	Extra         json.RawMessage `json:"extra"`
}

// тип - объект подписки, на его основе формируется подписка
type SubscriptionData struct {
	Subscriber       string             `json:"subscriber"`
	SubscriptionList []SubscriptionItem `json:"subscription_list"`
}

// тип - объект отписки, на его основе слушатель будет удален;
type UnsubscriptionData struct {
	Subscriber       string           `json:"subscriber"`
	SubscriptionItem SubscriptionItem `json:"subscription_item"`
}

// тип — callback на событие
type SubscriptionEventCallback = func(appEvent *ApplicationEvent) error

// оформляет подписку на одиночное событие
func Subscribe(isolation *Isolation.Isolation, subscriber string, event string, method string, addressType int, address string) error {

	if addressType != AddressQueue && addressType != AddressExchange && addressType != Tcp {
		return errors.New("incorrect address type")
	}

	// создаем список подписок из одного предмета подписки
	itemList := []SubscriptionItem{{
		Event:         event,
		AddressMethod: method,
		Address:       address,
		TriggerType:   addressType,
	}}

	// подписываемся на созданный список
	return SubscribeMultipleViaRabbit(isolation, subscriber, &itemList)
}

// выполняет подписку на события через реббит
func SubscribeMultipleViaRabbit(isolation *Isolation.Isolation, subscriber string, subscriptionList *[]SubscriptionItem) error {

	// формируем данные подписки
	eventData := SubscriptionData{
		Subscriber:       subscriber,
		SubscriptionList: *subscriptionList,
	}

	// создаем событие о подписке
	subscriptionEvent, err := CreateEvent("system_event_broker.subscription_updated", conf.GetEventConf().SourceIdentifier, conf.GetEventConf().SourceType, eventData)
	if err != nil {
		return err
	}

	// пушим событие о желании подписаться
	return Dispatch(isolation, &subscriptionEvent)
}

// отписываемся
func Unsubscribe(isolation *Isolation.Isolation, subscriber string) error {

	eventData := UnsubscriptionData{
		Subscriber: subscriber,
	}

	// создаем событие о подписке
	subscriptionEvent, err := CreateEvent("system_event_broker.subscription_removed", conf.GetEventConf().SourceIdentifier, conf.GetEventConf().SourceType, eventData)
	if err != nil {
		return err
	}

	// пушим событие о желании отписаться
	return Dispatch(isolation, &subscriptionEvent)
}
