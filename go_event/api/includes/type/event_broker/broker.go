package EventBroker

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	Event "go_event/api/includes/type/event"
	Isolation "go_event/api/includes/type/isolation"
)

// Handle обработка всех событий, тут можно навешать всяких пред обработок
func Handle(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

	broadcast(isolation, appEvent)
	return nil
}

// рассылает сообщение подписчикам по данным изоляции
func broadcast(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) {

	// получаем список всех слушателей из изоляции
	llStore := isolation.Get(isolationListenerListStoreKey).(*listenerListStore)

	// получаем слушателей события из списка слушателей
	listenerList, err := llStore.GetEventListeners(appEvent.EventType)

	if err != nil || len(listenerList) == 0 {

		log.Warningf("there is no listeners for %s", appEvent.EventType)
		return
	}

	// получаем всех подписчиков и кладем событие в шину для каждого из них
	for _, eListener := range listenerList {

		log.Infof("sending event %s to %s", appEvent.EventType, eListener.subscriber)

		// вызываем каждый триггер с передачей контекста
		if err := eListener.trigger(isolation, appEvent); err != nil {
			log.Errorf("error was occurred during event %s sending to %s: %s", appEvent.EventType, eListener.subscriber, err.Error())
		}
	}
}
