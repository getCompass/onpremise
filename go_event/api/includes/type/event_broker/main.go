package EventBroker

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	AsyncTask "go_event/api/includes/type/async_task"
	Event "go_event/api/includes/type/event"
	EventData "go_event/api/includes/type/event_data"
	Isolation "go_event/api/includes/type/isolation"
	"time"
)

// OnStart выполняет дефолтные подписки
// @long много структур
func OnStart(ctx context.Context) {

	// форсим подписку на события подписки — это основной функционал
	err1 := globalListenerListStore.AttachListenerToEvent("system_event_broker.subscription_updated", &listener{
		subscriber: "go_event",
		group:      "system",
		trigger:    onSubscriptionUpdated,
	})

	// форсим подписку на события отписки — это основной функционал
	err2 := globalListenerListStore.AttachListenerToEvent("system_event_broker.subscription_removed", &listener{
		subscriber: "go_event",
		group:      "system",
		trigger:    onSubscriptionRemoved,
	})

	// форсим подписку на события добавления генератора
	err3 := globalListenerListStore.AttachListenerToEvent(EventData.SystemEventBrokerEventList.EventGeneratorAdded, &listener{
		subscriber: "go_event",
		group:      "system",
		trigger:    onEventGeneratorAdded,
	})

	// форсим подписку на события удаления генератора
	err4 := globalListenerListStore.AttachListenerToEvent(EventData.SystemEventBrokerEventList.EventGeneratorRemoved, &listener{
		subscriber: "go_event",
		group:      "system",
		trigger:    onEventGeneratorRemoved,
	})

	// форсим подписку на события добавления генератора
	err5 := globalListenerListStore.AttachListenerToEvent(EventData.SystemEventBrokerEventList.TaskGeneratorAdded, &listener{
		subscriber: "go_event",
		group:      "system",
		trigger:    onTaskGeneratorAdded,
	})

	// форсим подписку на события удаления генератора
	err6 := globalListenerListStore.AttachListenerToEvent(EventData.SystemEventBrokerEventList.TaskGeneratorRemoved, &listener{
		subscriber: "go_event",
		group:      "system",
		trigger:    onTaskGeneratorRemoved,
	})

	if err1 != nil || err2 != nil || err3 != nil || err4 != nil || err5 != nil || err6 != nil {
		panic("panic: can't start system subscriptions")
	}

	// далее загружаем дефолтные и сохраненные подписки
	log.Info("loading default subscriptions...")

	// поднимаем дефолтные подписки, не сохраняем их, они в конфиге и могут меняться
	for _, subscriptionData := range getDefault() {
		_ = ProcessSubscriptionRequest(&subscriptionData)
	}

	log.Info("loading stored subscriptions...")

	// поднимаем сохраненные подписки, не сохраняем их, они и так сохранены
	for _, subscriptionData := range getAllStoredSubscriptions(ctx) {
		_ = ProcessSubscriptionRequest(&subscriptionData)
	}

	// инициализируем самопроверку брокера
	go StartPersistentLifeCheck(-1)
	log.Success("default subscription done...")
}

// PokeSubscribers после старта рассылает событие об обновлении подписок
func PokeSubscribers() {

	log.Info("sending subscription refresh event")

	broadcast(Isolation.Global(), &Event.ApplicationEvent{
		EventType:        EventData.SystemEventList.SubscriptionRefreshingRequired,
		SourceType:       "service",
		SourceIdentifier: "go_event",
		CreatedAt:        functions.GetCurrentTimeStamp(),
		EventData:        []byte("{}"),
		Version:          1,
		DataVersion:      1,
		Uuid:             functions.GenerateUuid(),
	})
}

// добавляет подписчика
func onSubscriptionUpdated(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	eventData := Event.SubscriptionData{}

	// разбираем событие
	err := go_base_frame.Json.Unmarshal(appEvent.EventData, &eventData)
	if err != nil {
		return err
	}

	// дергаем менеджер подписок
	if err = ProcessSubscriptionRequest(&eventData); err == nil {
		updateSubscriberStorage(isolation.GetContext(), &eventData)
	}

	return err
}

// обновляем подписки, прилетевшие извне
func updateSubscriberStorage(ctx context.Context, eventData *Event.SubscriptionData) {

	var existing Event.SubscriptionData
	var err error

	// читаем существующие подписки
	if existing, err = getStoredSubscription(ctx, eventData.Subscriber); err != nil {

		log.Error("can't read subscriber from storage " + err.Error())
		return
	}

	// формируем список, чтобы не бегать циклами по 50 раз
	toUpdate := map[string]Event.SubscriptionItem{}

	for _, item := range eventData.SubscriptionList {
		toUpdate[item.Event] = item
	}

	// перебираем существующие подписки на события
	for k, existingItem := range existing.SubscriptionList {

		// если среди новых есть такое событие, то перезаписываем данные
		if newItem, isExist := toUpdate[existingItem.Event]; isExist {

			existing.SubscriptionList[k] = newItem
			delete(toUpdate, existingItem.Event)
		}
	}

	// добавляем в массив те, что не были записаны
	for _, v := range toUpdate {

		log.Info(fmt.Sprintf("%s: new subscription %s stored", eventData.Subscriber, v.Event))
		existing.SubscriptionList = append(existing.SubscriptionList, v)
	}

	// пишем в бд новые подписки для подписчика
	_ = UpdateStoredSubscription(ctx, eventData.Subscriber, existing.SubscriptionList, time.Now().Unix())
	log.Info(eventData.Subscriber + ": subscriptions stored ")
}

// удаляет подписчика
func onSubscriptionRemoved(_ *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	eventData := Event.UnsubscriptionData{}

	// разбираем событие
	err := go_base_frame.Json.Unmarshal(appEvent.EventData, &eventData)
	if err != nil {
		return err
	}

	// дергаем менеджер подписок
	return ProcessUnsubscriptionRequest(&eventData)
}

// добавляет генератор
// генераторы пока что существуют вне изоляции
func onEventGeneratorAdded(_ *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	generatorData := EventData.SystemEventBrokerEventGeneratorAdded{}

	// разбираем событие
	err := go_base_frame.Json.Unmarshal(appEvent.EventData, &generatorData)
	if err != nil {
		return err
	}

	// создаем подписку для события генератора
	// в качестве подписчика используем идентификатор источника
	eventData := Event.SubscriptionData{
		Subscriber:       appEvent.SourceIdentifier,
		SubscriptionList: []Event.SubscriptionItem{generatorData.SubscriptionItem},
	}

	// обрабатываем подписку на событие генератора
	_ = ProcessSubscriptionRequest(&eventData)

	MakeEventGenerator(generatorData.SubscriptionItem.Event, generatorData.Period, generatorData.EventData, generatorData.OnlyForGlobal)
	return nil
}

// удаляет генератор
// генераторы пока что существуют вне изоляции
func onEventGeneratorRemoved(_ *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные для остановки генератора
	gData := EventData.SystemEventBrokerEventGeneratorRemoved{}

	// разбираем событие
	err := go_base_frame.Json.Unmarshal(appEvent.EventData, &gData)
	if err != nil {
		return err
	}

	StopEventGenerator(gData.EventName)
	return nil
}

// добавляет генератор
// генераторы пока что существуют вне изоляции
func onTaskGeneratorAdded(_ *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные для запуска генератора
	gData := EventData.SystemEventBrokerTaskGeneratorAdded{}

	// разбираем событие
	err := go_base_frame.Json.Unmarshal(appEvent.EventData, &gData)
	if err != nil {
		return err
	}

	AsyncTask.MakeTaskGenerator(gData.TaskName, AsyncTask.TaskType(gData.TaskType), gData.Period, gData.TaskData, gData.Module, gData.Group)
	return nil
}

// удаляет генератор
// генераторы пока что существуют вне изоляции
func onTaskGeneratorRemoved(_ *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	// данные для остановки генератора
	gData := EventData.SystemEventBrokerTaskGeneratorRemoved{}

	// разбираем событие
	err := go_base_frame.Json.Unmarshal(appEvent.EventData, &gData)
	if err != nil {
		return err
	}

	AsyncTask.StopTaskGenerator(gData.TaskName)
	return nil
}
