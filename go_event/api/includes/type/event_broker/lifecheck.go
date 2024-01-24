package EventBroker

import (
	"errors"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/includes/type/event"
	Isolation "go_event/api/includes/type/isolation"
	"time"
)

// StartPersistentLifeCheck запускает бесконечный цикл проверок
func StartPersistentLifeCheck(period int64) {

	// если период меньше ноля, не запускам лайфчек
	if period < 0 {
		return
	}

	log.Infof("starting continuous lifecheck cycle")

	for {

		// фиксируем время начала
		startAt := functions.GetCurrentTimeStamp()

		// делаем проверку
		if err := DoLifecheck(); err != nil {
			log.Errorf("life check failed, reason: %s", err.Error())
		}

		// ждем следующий цикл проверки
		time.Sleep(time.Second * time.Duration(period-functions.GetCurrentTimeStamp()+startAt))
	}
}

// DoLifecheck выполняем проверку функционала брокера событий
func DoLifecheck() error {

	// канал, на котором будем проверять жизнеспособность
	var trigger = make(chan bool)

	// триггер, который переводит канал в иное состояние
	callback := func(_ *Event.ApplicationEvent) error {

		trigger <- true
		return nil
	}

	// уникальный подписчик, каждый раз разный, по сути не важно, что тут будет
	subscriberUniq := functions.GenerateUuid()
	eventType := "system_event_broker.self_" + functions.GenerateUuid()

	// создаем хендлер
	if initLifecheck(subscriberUniq, eventType, callback) != nil {
		return errors.New("can't init a lifecheck handler")
	}

	select {
	case <-trigger:

		// все хорошо, мы получили событие
	case <-time.After(time.Second * 5):

		// таймаут, событие не было получено
		return errors.New("lifecheck event was not received")
	}

	if invalidateLifecheck(subscriberUniq, eventType) != nil {
		return errors.New("can't unsubscribe from lifecheck event")
	}

	return nil
}

// создаем хендлер для события проверки плавучести
func initLifecheck(subscriberUniq string, eventType string, callback Event.SubscriptionEventCallback) error {

	// подписка на событие самодиагностики
	handlerData := Event.MakeHandler(subscriberUniq, eventType, callback)
	Event.RegisterHandlers(Isolation.Global(), subscriberUniq, &[]Event.RegisterHandlerData{handlerData})

	// ждем, потому что подписка может сработать с задержкой
	// и тестовый ивент вылетит раньше и сфелится
	time.Sleep(2 * time.Second)

	// создаем событие
	err := Event.Dispatch(Isolation.Global(), &Event.ApplicationEvent{
		EventType:        eventType,
		SourceType:       "testing",
		SourceIdentifier: "testing",
		CreatedAt:        functions.GetCurrentTimeStamp(),
		EventData:        nil,
		Version:          1,
		DataVersion:      1,
		Uuid:             functions.GenerateUuid(),
	})

	return err
}

// инвалидируем хендлер для события проверки плавучести
func invalidateLifecheck(subscriberUniq string, eventType string) error {

	// инвалидируем хендлер и выполняем отписку
	Event.InvalidateHandler(eventType, subscriberUniq)
	err := Event.Unsubscribe(Isolation.Global(), subscriberUniq)

	if err != nil {
		log.Error("unable to unsubscribe")
	}

	return err
}
