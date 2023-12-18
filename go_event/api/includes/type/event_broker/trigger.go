package EventBroker

import (
	"encoding/json"
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
	"go_event/api/includes/type/async_task"
	Event "go_event/api/includes/type/event"
	Isolation "go_event/api/includes/type/isolation"
	"go_event/api/includes/type/socket"
	socketAuthKey "go_event/api/includes/type/socket/auth"
	"go_event/api/system/sharding"
	"time"
)

// TriggerFn функция-тип — коллбек для события
// триггеры не создаются для каждой компании, поскольку будут полностью идентичны
// непосредственно сама среда не взаимодействует с триггерами, для этого используется локальный брокер
// в качестве первого параметра принимает контекст исполнения, параметр необязательный к использованию,
// но позволяет понять, для какой компании нужно стриггерить обработку события
type TriggerFn = func(isolation *Isolation.Isolation, event *Event.ApplicationEvent) error

// внутренний тип — запакованные данные события
type socketRequest struct {
	Method    string                 `json:"method"`
	Event     Event.ApplicationEvent `json:"event"`
	IsGlobal  bool                   `json:"is_global"`
	CompanyId int64                  `json:"company_id"`
}

// список функций, формирующих триггеры
var triggerList = map[int]func(listener *Event.SubscriptionItem) (TriggerFn, error){
	Event.AddressQueue:    makeRabbitQueueTrigger,     // для подписчика очереди
	Event.AddressExchange: makeRabbitExchangeTrigger,  // для подписчика обменника
	Event.Tcp:             makeSocketTrigger,          // для подписчика tcp
	Event.TcpQueue:        makeSocketQueueTrigger,     // для подписчика tcp-очередь
	Event.TaskTcpQueue:    makeTaskSocketQueueTrigger, // для передачи события как задачи
}

// формирует триггер для указанной подписки
func makeSubscriptionTrigger(subscriptionItem *Event.SubscriptionItem, subscriber string) (TriggerFn, error) {

	if _, isExist := triggerList[subscriptionItem.TriggerType]; !isExist {
		return nil, errors.New(fmt.Sprintf("subscription request from %s to event %s has incorrect type %d", subscriber, subscriptionItem.Event, subscriptionItem.TriggerType))
	}

	// создаем триггер
	trigger, err := triggerList[subscriptionItem.TriggerType](subscriptionItem)

	if err != nil {
		return nil, errors.New(fmt.Sprintf("creating trigger for subscription request from %s to event %s has returned an error: %s", subscriber, subscriptionItem.Event, err.Error()))
	}

	return trigger, nil
}

// RabbitQueueTriggerSubscriptionExtra экстра данные для подписчика по rabbit queueParcel
type RabbitQueueTriggerSubscriptionExtra struct {
	Queue  string `json:"queueParcel"`
	Method string `json:"method"`
}

// возвращает триггер для rabbit queueParcel
func makeRabbitQueueTrigger(subscriptionItem *Event.SubscriptionItem) (TriggerFn, error) {

	// получаем экстру подписки
	var extra RabbitQueueTriggerSubscriptionExtra

	if go_base_frame.Json.Unmarshal(subscriptionItem.Extra, &extra) != nil {
		return nil, errors.New("passed incorrect subscription extra")
	}

	// возвращаем замыкание
	return func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

		// готовим и кодируем сообщение для шины
		data, err := go_base_frame.Json.Marshal(socketRequest{
			Method:    extra.Method,
			Event:     *appEvent,
			CompanyId: isolation.GetCompanyId(),
			IsGlobal:  isolation.IsGlobal(),
		})

		if err != nil {
			log.Errorf("can not encode event for broadcasting %s!", appEvent.EventType)
		}

		sharding.Rabbit(conf.GetEventConf().EventService.RabbitKey).SendMessageToQueue(extra.Queue, data)
		return nil
	}, nil
}

// RabbitExchangeTriggerSubscriptionExtra экстра данные для подписчика по rabbit exchange
type RabbitExchangeTriggerSubscriptionExtra struct {
	Exchange string `json:"exchange"`
	Method   string `json:"method"`
}

// возвращает триггер для rabbit exchange
func makeRabbitExchangeTrigger(subscriptionItem *Event.SubscriptionItem) (TriggerFn, error) {

	// получаем экстру подписки
	var extra RabbitExchangeTriggerSubscriptionExtra

	if go_base_frame.Json.Unmarshal(subscriptionItem.Extra, &extra) != nil {
		return nil, errors.New("passed incorrect subscription extra")
	}

	// возвращаем замыкание
	return func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

		// готовим b кодируем сообщение для шины
		data, err := go_base_frame.Json.Marshal(socketRequest{
			Method:    extra.Method,
			Event:     *appEvent,
			CompanyId: isolation.GetCompanyId(),
			IsGlobal:  isolation.IsGlobal(),
		})

		if err != nil {
			log.Errorf("can not encode event for broadcasting %s!", appEvent.EventType)
		}

		sharding.Rabbit(conf.GetEventConf().EventService.RabbitKey).SendMessageToQueue(extra.Exchange, data)
		return nil
	}, nil
}

// SocketTriggerSubscriberExtra экстра данные для подписчика по tcp
type SocketTriggerSubscriberExtra struct {
	Method string `json:"method"`
	Module string `json:"module"`
}

// возвращает триггер для tcp
// @long функция, возвращающая функцию
func makeSocketTrigger(subscriptionItem *Event.SubscriptionItem) (TriggerFn, error) {

	// получаем экстру подписки
	var extra SocketTriggerSubscriberExtra

	if go_base_frame.Json.Unmarshal(subscriptionItem.Extra, &extra) != nil {
		return nil, errors.New("passed incorrect subscription extra")
	}

	// убеждаемся, что переданный модуль нам известен
	if _, err := conf.GetModuleSocketUrl(extra.Module); err != nil {
		return nil, err
	}

	return func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

		// кодируем сообщение для шины
		data, _ := go_base_frame.Json.Marshal(socketRequest{
			Method:    extra.Method,
			Event:     *appEvent,
			CompanyId: isolation.GetCompanyId(),
			IsGlobal:  isolation.IsGlobal(),
		})

		// получаем подпись модуля
		signature := socketAuthKey.GetSignature(conf.GetConfig().SocketKeyMe, data)

		// получаем урл модуля в который уйдет событие
		// чуть ранее проверили, что socket-endpoint модуля с которым работаем – известен
		url, _ := conf.GetModuleSocketUrl(extra.Module)

		count, maxCount, delay := 0, 10, 1000

		// пойдем в цикле обращаться, чтобы в случае ошибки можно было переотправить запрос
		for count <= maxCount {

			// делаем запрос в php модуль
			response, err := socket.DoCall(url, extra.Method, data, signature, 0, isolation.GetCompanyId())

			if err != nil {

				// увеличим счетчик ошибок на один
				count++
				log.Errorf("error socket request on event %s: %s", appEvent.EventType, err)
				log.Errorf("response: %s", response)

				// подождем секунду
				time.Sleep(time.Duration(delay))
			} else {
				break
			}
		}

		return nil
	}, nil
}

// SocketQueueTriggerSubscriberExtra экстра данные для подписчика по tcp
type SocketQueueTriggerSubscriberExtra struct {
	Method string `json:"method"`
	Module string `json:"module"`
	Group  string `json:"group"`
}

// возвращает триггер для tcp
func makeSocketQueueTrigger(subscriptionItem *Event.SubscriptionItem) (TriggerFn, error) {

	// получаем экстру подписки
	var extra SocketQueueTriggerSubscriberExtra

	if go_base_frame.Json.Unmarshal(subscriptionItem.Extra, &extra) != nil {
		return nil, errors.New("passed incorrect subscription extra")
	}

	// убеждаемся, что переданный модуль нам известен
	if _, err := conf.GetModuleSocketUrl(extra.Module); err != nil {
		return nil, err
	}

	// тут получателем мы считаем доставщика, а не конечного получателя
	return func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

		// вытаскиваем хранилище контроллеров дискретных доставок из изоляции
		discreteControllerStore := isolation.Get(isolationDiscreteStoreKey).(*discreteControllerStore)

		// получаем дискретного доставщика
		controller := discreteControllerStore.getParcelController(extra.Module, extra.Group)
		if controller == nil {
			controller = discreteControllerStore.makeParcelController(extra.Module, extra.Group)
		}

		return controller.queue(appEvent)
	}, nil
}

// TaskSocketQueueTriggerSubscriberExtra экстра данные для подписчика по tcp
type TaskSocketQueueTriggerSubscriberExtra struct {
	Module     string `json:"module"`
	Group      string `json:"group"`
	Type       int    `json:"type"`
	ErrorLimit int    `json:"error_limit"`
}

// возвращает триггер для tcp
func makeTaskSocketQueueTrigger(subscriptionItem *Event.SubscriptionItem) (TriggerFn, error) {

	// получаем экстру подписки
	var extra TaskSocketQueueTriggerSubscriberExtra

	if go_base_frame.Json.Unmarshal(subscriptionItem.Extra, &extra) != nil {
		return nil, errors.New("passed incorrect subscription extra")
	}

	// убеждаемся, что переданный модуль нам известен
	if _, err := conf.GetModuleSocketUrl(extra.Module); err != nil {
		return nil, err
	}

	// тут получателем мы считаем хранилище, а не конечного получателя
	return func(isolation *Isolation.Isolation, appEvent *Event.ApplicationEvent) error {

		var taskType AsyncTask.TaskType

		switch extra.Type {
		case 0, 1:
			taskType = AsyncTask.TaskTypeSingle
		case 2:
			taskType = AsyncTask.TaskTypePeriodic
		case 4:
			taskType = AsyncTask.TaskTypePeriodicNonSavable
		default:
			return fmt.Errorf("unknown task type %d", extra.Type)
		}

		var data json.RawMessage
		_ = go_base_frame.Json.Unmarshal(appEvent.EventData, &data)

		return AsyncTask.CreateAndPushToDiscrete(isolation, appEvent.EventType, taskType, time.Now().Unix(), data, extra.Module, extra.Group)
	}, nil
}
