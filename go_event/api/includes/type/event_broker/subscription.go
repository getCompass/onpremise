package EventBroker

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	Event "go_event/api/includes/type/event"
)

// Subscriber тип — подписчик
type Subscriber struct {
	SubscriberUnique string
}

// Subscription тип — подписка
type Subscription struct {
	Subscriber  Subscriber `json:"subscriber"`   // кто подписывается, уникальный идентификатор
	SubscribeTo []string   `json:"subscribe_to"` // на какие события подписывается
	Group       string     `json:"group"`        // группа подписки, все слушатели будут привязаны к ней и будут удалены при обновлении подписки
}

// ProcessSubscriptionRequest обрабатывает запрос на подписку
// по-хорошему тут тоже должна быть изоляция, но ее не удалось прокинуть сюда
func ProcessSubscriptionRequest(subscriptionData *Event.SubscriptionData) error {

	log.Info(fmt.Sprintf("got subscription request from %s", subscriptionData.Subscriber))

	// проходимся по всем желаемым событиям
	for _, subscriptionItem := range subscriptionData.SubscriptionList {

		// создаем триггер
		trigger, err := makeSubscriptionTrigger(&subscriptionItem, subscriptionData.Subscriber)

		if err != nil {

			log.Error(err.Error())
			continue
		}

		err = globalListenerListStore.AttachListenerToEvent(subscriptionItem.Event, &listener{
			subscriber: subscriptionData.Subscriber,
			group:      "default",
			trigger:    trigger,
		})

		if err != nil {

			log.Error(fmt.Sprintf("subscription request from %s for event %s has returned an error: %s", subscriptionData.Subscriber, subscriptionItem.Event, err.Error()))
			continue
		}
	}

	return nil
}

// ProcessUnsubscriptionRequest обрабатывает запрос на отписку от события
// по-хорошему тут тоже должна быть изоляция, но ее не удалось прокинуть сюда
func ProcessUnsubscriptionRequest(unsubscriptionData *Event.UnsubscriptionData) error {

	log.Info(fmt.Sprintf("got unsubscription request from %s", unsubscriptionData.Subscriber))

	err := globalListenerListStore.DetachListenerFromEvent(unsubscriptionData.SubscriptionItem.Event, &listener{
		subscriber: unsubscriptionData.Subscriber,
		group:      "default",
		trigger:    nil,
	})

	if err != nil {

		log.Error(fmt.Sprintf("subscription request from %s for event %s has returned an error: %s", unsubscriptionData.Subscriber, unsubscriptionData.SubscriptionItem.Event, err.Error()))
		return err
	}

	return nil
}
