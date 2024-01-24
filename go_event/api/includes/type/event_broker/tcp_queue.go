package EventBroker

import (
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
	"go_event/api/includes/type/async_trap"
	DiscreteDelivery "go_event/api/includes/type/discrete_delivery"
	Event "go_event/api/includes/type/event"
	Isolation "go_event/api/includes/type/isolation"
	"go_event/api/includes/type/socket"
	socketAuthKey "go_event/api/includes/type/socket/auth"
	"sync"
	"time"
)

type eventDeliveryResult int

const (
	eventDeliveryResultSuccess      eventDeliveryResult = 1
	eventDeliveryResultNotProcessed eventDeliveryResult = 2
	eventDeliveryResultRequeued     eventDeliveryResult = 3
	eventDeliveryResultError        eventDeliveryResult = 4
)

const attentionRequestTimeLimit = 500 * time.Millisecond // время обработки, после которого доставка считается долгой
const parcelErrorLimit = 3                               // лимит ошибок для одной доставки
const attentionProcessTimeLimit = 80 * time.Millisecond  // время обработки события, после которого оно считается медленным
const bufferLength = 1000                                // максимальный размер буфера бля посылок
const bufferLengthWarning = 700                          // размер буфера бля посылок, при котором будут генерироваться уведомления

// имя метода, на который будут отправляться задачи
const eventProcessListMethodName = "event.processEventList"

// ----------------------------
// хранилище менеджеров посылок
// ----------------------------

// тип — хранилище для доставщиков по подписчикам
type discreteControllerStore struct {
	store                map[string]*parcelController
	mx                   sync.RWMutex
	makeParcelController func(module, group string) *parcelController
}

// создает новое хранилище контроллеров доставки
func makeDiscreteControllerStore(isolation *Isolation.Isolation) *discreteControllerStore {

	store := &discreteControllerStore{store: map[string]*parcelController{}}

	// запускаем всякие штуки для изоляции
	store.makeParcelController = func(module, group string) *parcelController {

		// формируем ключ для контроллера
		key := fmt.Sprintf("%s:%s", module, group)

		// пытаемся получить существующий контроллер
		if controller := store.getParcelController(module, group); controller != nil {
			return controller
		}

		// как часто доставляем задачи
		frequency := conf.GetEventConf().TaskDiscreteCourierDelay

		// создаем новое хранилище
		controller := createParcelController(
			isolation, key, module, conf.GetEventConf().EventDiscreteCourierCount,
			frequency, conf.GetEventConf().PerDeliveryLimit,
		)

		// сохраняем данные
		store.mx.Lock()
		store.store[key] = controller
		store.mx.Unlock()

		return controller
	}

	go func() {

		Isolation.Inc("event-discrete-controller-store")
		defer Isolation.Dec("event-discrete-controller-store")

		// ждем завершения контекста изоляции и завершаем рутину
		<-isolation.GetContext().Done()
		store.invalidate()
	}()

	return store
}

// возвращает контроллер для модуля и соответствующей группы
func (store *discreteControllerStore) getParcelController(module, group string) *parcelController {

	// формируем ключ для контроллера
	key := fmt.Sprintf("%s:%s", module, group)

	store.mx.RLock()
	defer store.mx.RUnlock()

	controller, isExists := store.store[key]
	if !isExists {
		return nil
	}

	return controller
}

// инвалидирует всех контроллеры доставки в хранилище
// после инвалидации посылки больше не будут отправляться
func (store *discreteControllerStore) invalidate() {

	store.mx.Lock()
	defer store.mx.Unlock()

	for key, pController := range store.store {

		pController.invalidate()
		delete(store.store, key)
	}
}

// ------------------
// контроллер посылок
// ------------------

// parcelControllerRecipient место доставки для курьера
type parcelControllerRecipient struct {
	module string
	method string
}

// тип — запрос курьера к подписчику
type parcelControllerRequest struct {
	EventList []*Event.ApplicationEvent `json:"event_list"` // список событий на обработку
	Method    string                    `json:"method"`     // метод, которым события должны быть обработаны
	IsGlobal  bool                      `json:"is_global"`  // флаг глобальности для логики обработчика
	CompanyId int64                     `json:"company_id"` // ид компании, в которой должны быть обработаны данные
}

// тип — запрос курьера к подписчику
type parcelControllerResponse struct {
	EventProcessResultList map[string]eventProcessResult `json:"event_process_result_list"`
}

// результат обработки задачи
type eventProcessResult struct {
	ProcessedIn  int64               `json:"processed_in"`
	Status       eventDeliveryResult `json:"status"`
	Message      string              `json:"message"`
	RequeueDelay int                 `json:"requeue_delay"`
}

// тип — контроллер посылок событий
type parcelController struct {
	eventQueue        chan *DiscreteDelivery.Parcel
	isQueueAvailable  bool
	description       string
	discreteStashList []*DiscreteDelivery.DiscreteStash
	recipient         *parcelControllerRecipient

	// функция отправки данных, которую нужно пробросить в дискретное хранилище
	sendFn func(parcelList []*DiscreteDelivery.Parcel)
}

// создает нового менеджера посылок
func createParcelController(isolation *Isolation.Isolation, key, module string, threadCount, frequency, perDeliverLimit int) *parcelController {

	pController := &parcelController{
		eventQueue:       make(chan *DiscreteDelivery.Parcel, bufferLength),
		isQueueAvailable: true,
		description:      fmt.Sprintf("epc-%s", key),
		recipient: &parcelControllerRecipient{
			module: module,
			method: eventProcessListMethodName,
		},
	}

	// создаем доставщика и функцию доставки с использованием изоляции
	// все ради изолирования изоляций
	pController.sendFn = func(parcelList []*DiscreteDelivery.Parcel) {
		send(isolation, pController, parcelList)
	}

	// добавляем доставщиков, которые будут выполнять доставку задач до целевых модулей/сервисов
	// доставщики ждут, пока дискретное хранилище передаст им пачку задач, и идут с ними в нужный модуль/сервис
	for i := 0; i < threadCount; i++ {

		// цепляем хранилища к контроллеру,
		// хранилища остановятся, если канал закроется или если контекст станет невалидным
		DiscreteDelivery.InitDiscreteStash(key, pController.eventQueue, pController.sendFn, frequency, perDeliverLimit, false, true)
	}

	Isolation.Inc("event-parcel-controller")
	go func() {

		<-isolation.GetContext().Done()
		Isolation.Dec("event-parcel-controller")
	}()

	return pController
}

// функция доставки посылок
func send(isolation *Isolation.Isolation, controller *parcelController, parcelList []*DiscreteDelivery.Parcel) {

	// увеличиваем число доставок для всех посылок
	for _, parcel := range parcelList {
		parcel.OnSent()
	}

	// делаем запрос к получателю
	response, err := sendRequest(isolation, controller, parcelList)

	if err != nil {

		// если запрос закончился ошибкой, отмечаем все события как ошибочные
		for _, parcel := range parcelList {

			parcel.OnError()
			onDeliveryDone(isolation, controller, parcel, makeFailedResponse(err.Error()))
		}

		return
	}

	// перебираем все отправленные посылки и сопостовляем ответы
	for _, parcel := range parcelList {

		// проверяем, что нужная посылка есть в ответе
		if _, isExist := response.EventProcessResultList[parcel.GetUuid()]; !isExist {

			// задачи нет в ответе по какой-то причине
			parcel.OnError()
			onDeliveryDone(isolation, controller, parcel, makeFailedResponse("there is no response for event"))

			continue
		}

		// получаем значение из ответа
		tmp := response.EventProcessResultList[parcel.GetUuid()]

		// обрабатываем посылку как отправленную
		onDeliveryDone(isolation, controller, parcel, &tmp)
	}
}

// выполняет tcp запрос
func sendRequest(isolation *Isolation.Isolation, controller *parcelController, parcelList []*DiscreteDelivery.Parcel) (*parcelControllerResponse, error) {

	// готовим данные для запроса
	requestBytes, err := convertParcelsToRequestData(isolation, controller, parcelList)
	signature := socketAuthKey.GetSignature(conf.GetConfig().SocketKeyMe, requestBytes)

	// получаем урл модуля в который уйдет событие
	url, err := conf.GetModuleSocketUrl(controller.recipient.module)
	if err != nil {
		return nil, fmt.Errorf("unknown module %s", controller.recipient.module)
	}

	startedAt := time.Now()

	// выполняем запрос
	response, err := socket.DoCall(url, controller.recipient.method, requestBytes, signature, 0, isolation.GetCompanyId())

	// уведомляем о долгом исполнении запроса
	if time.Now().Sub(startedAt) > attentionRequestTimeLimit {
		controller.say(fmt.Sprintf("subscriber took to much time to process parcels: %dms", time.Now().Sub(startedAt)/time.Millisecond), log.Error)
	}

	// произошла ошибка запроса
	if err != nil {
		return nil, err
	} else if response.Status == "error" {
		return nil, errors.New(response.Message)
	}

	encoded, _ := go_base_frame.Json.Marshal(response.Response)
	decodedResponse := parcelControllerResponse{}
	err = go_base_frame.Json.Unmarshal(encoded, &decodedResponse)

	if err != nil {
		return nil, errors.New(fmt.Sprintf("%s: response has incorrect format", controller.description))
	}

	return &decodedResponse, err
}

// конвертирует посылки в ожидаемый формат
func convertParcelsToRequestData(isolation *Isolation.Isolation, controller *parcelController, parcelList []*DiscreteDelivery.Parcel) ([]byte, error) {

	var eventList []*Event.ApplicationEvent

	for _, parcel := range parcelList {
		eventList = append(eventList, parcel.GetContent().(*Event.ApplicationEvent))
	}

	// готовим сообщение
	request := parcelControllerRequest{
		Method:    controller.recipient.method,
		EventList: eventList,
		IsGlobal:  isolation.IsGlobal(),
		CompanyId: isolation.GetCompanyId(),
	}

	// кодируем сообщение для шины
	return go_base_frame.Json.Marshal(request)
}

// формирует ответ, если сервер вернул неподходящие данные
func makeFailedResponse(message string) *eventProcessResult {

	return &eventProcessResult{
		ProcessedIn:  0,
		Status:       eventDeliveryResultError,
		Message:      message,
		RequeueDelay: 0,
	}
}

// функция, обрабатывающая результат доставки данных получателям
func onDeliveryDone(isolation *Isolation.Isolation, pController *parcelController, parcel *DiscreteDelivery.Parcel, response *eventProcessResult) {

	// декодим ивент из данных
	event, ok := parcel.GetContent().(*Event.ApplicationEvent)

	if !ok {

		log.Error("got incorrect parcel type, can't process it")
		return
	}

	// логируем долгое исполнение
	pController.logLongProcessTime(event, response)

	// если посылка был снова поставлена в очередь, то пушим ее туда с указанной задержкой
	if response.Status == eventDeliveryResultNotProcessed || response.Status == eventDeliveryResultRequeued {

		log.Error(fmt.Sprintf("requeue event %s, reason %s", event.EventType, response.Message))
		go pController.queueParcel(parcel, response.RequeueDelay)
	}

	// если возникла ошибка, то пушим с небольшой фиксированной задержкой
	if response.Status == eventDeliveryResultError {

		// указываем, что доставка была с ошибкой
		parcel.OnError()

		if parcel.GetErrorCount() < parcelErrorLimit {

			// снова пушим событие в очередь
			log.Error(fmt.Sprintf("requeue event %s, reason last delivery was not successful", event.EventType))
			go pController.queueParcel(parcel, 1000*parcel.GetErrorCount())
		} else {

			// дропаем событие
			log.Error(fmt.Sprintf("event %s was dropped due high number of failed attemts", event.EventType))
		}
	}

	// кидаем в ловушку
	// может это не тут нужно делать
	if response.Status == eventDeliveryResultSuccess {
		go AsyncTrap.Process(isolation, pController.recipient.module, event.EventType, event.CreatedAt/1000000, event.EventData)
	}

	// остальные статусы игнорируем, они нам не особо интересны
}

// возвращает описание доставщика
func (pController *parcelController) say(str string, fn func(string)) {

	fn(fmt.Sprintf("%s: %s", pController.description, str))
}

// пушит событие на доставку
func (pController *parcelController) queue(appEvent *Event.ApplicationEvent) error {

	pController.queueParcel(DiscreteDelivery.InitParcel(appEvent.Uuid, appEvent), 0)
	return nil
}

// добавляет посылку в очередь доставки
func (pController *parcelController) queueParcel(parcel *DiscreteDelivery.Parcel, delay int) {

	// ждем, только если передали задержку
	if delay > 0 {
		time.After(time.Duration(delay) * time.Millisecond)
	}

	if len(pController.eventQueue) >= bufferLength {

		pController.say(fmt.Sprintf("event queueParcel exceed limit: %d, event dropped", len(pController.eventQueue)), log.Error)
		return
	} else if len(pController.eventQueue) >= bufferLengthWarning {
		pController.say(fmt.Sprintf("event queueParcel is too long: %d", len(pController.eventQueue)), log.Error)
	}

	// если очередь уже недоступна (инвалидировали изоляцию) - ничего туда не пишем
	if !pController.isQueueAvailable {

		log.Errorf("[PARCEL CONTROLLER] %s: queue is no longer available", pController.description)
		return
	}

	// пушим посылку в канал
	pController.eventQueue <- parcel
}

// логируем долгое исполнение
func (pController *parcelController) logLongProcessTime(event *Event.ApplicationEvent, response *eventProcessResult) {

	if time.Duration(response.ProcessedIn)*time.Millisecond <= attentionProcessTimeLimit {
		return
	}

	pController.say(fmt.Sprintf("event %s took too much time to process %dms", event.EventType, response.ProcessedIn), log.Error)
}

// инвалидирует контроллер доставок
func (pController *parcelController) invalidate() {

	pController.isQueueAvailable = false
	close(pController.eventQueue)
}
