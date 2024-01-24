package AsyncTask

import (
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_event/api/conf"
	CompanySystem "go_event/api/includes/type/database/company_system"
	DiscreteDelivery "go_event/api/includes/type/discrete_delivery"
	Isolation "go_event/api/includes/type/isolation"
	"go_event/api/includes/type/socket"
	socketAuthKey "go_event/api/includes/type/socket/auth"
	"sync"
)

const bufferLength = 1000       // максимальный размер буфера для посылок
const bufferLengthWarning = 700 // размер буфера для посылок, при котором будут генерироваться уведомления

// имя метода, на который будут отправляться задачи
const taskProcessListMethodName = "task.processList"

// ----------------------------
// хранилище менеджеров посылок
// ----------------------------

// изолированный тип, экземпляр лежит в изоляции
// хранилище для контроллеров доставки, создавать через makeDiscreteControllerStore
type discreteControllerStore struct {
	store                map[string]*parcelController
	mx                   sync.RWMutex
	makeParcelController func(taskChan chan *AsyncTask, key, module string) *parcelController
}

// создает новое хранилище контроллеров доставки
func makeDiscreteControllerStore(isolation *Isolation.Isolation) *discreteControllerStore {

	store := &discreteControllerStore{
		store: map[string]*parcelController{},
	}

	// запускаем всякие штуки для изоляции
	store.makeParcelController = func(taskChan chan *AsyncTask, key, module string) *parcelController {

		// пытаемся получить существующий контроллер
		if controller := store.getParcelController(key); controller != nil {
			return controller
		}

		// как часто доставляем задачи
		frequency := conf.GetEventConf().TaskDiscreteCourierDelay

		// создаем новое хранилище
		controller := createParcelController(
			isolation, taskChan, key, module, conf.GetEventConf().TaskDiscreteCourierCount,
			frequency, conf.GetEventConf().PerDeliveryLimit,
		)

		// сохраняем данные
		store.mx.Lock()
		store.store[key] = controller
		store.mx.Unlock()

		return controller
	}

	go func() {

		Isolation.Inc("task-discrete-controller-store")
		defer Isolation.Dec("task-discrete-controller-store")

		// ждем завершения контекста изоляции и завершаем рутину
		<-isolation.GetContext().Done()
		store.invalidate()
	}()

	return store
}

// возвращает контроллер для модуля и соответствующей группы
func (store *discreteControllerStore) getParcelController(key string) *parcelController {

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

	store.mx.RLock()
	defer store.mx.RUnlock()

	for key := range store.store {
		delete(store.store, key)
	}
}

// -----------------
// менеджер посылок
// -----------------

// получатель для задач, которому контроллер будет доставлять посылки
type parcelControllerRecipient struct {
	module string
	method string
}

// тип — запрос на доставку задач
type parcelControllerRequest struct {
	TaskList  []*CompanySystem.AsyncTaskRecord `json:"task_list"`
	Method    string                           `json:"method"`
	CompanyId int64                            `json:"company_id"`
	IsGlobal  bool                             `json:"is_global"`
}

// тип — результат доставки задач
type parcelControllerResponse struct {
	TaskProcessResultList map[string]taskProcessResult `json:"task_process_result_list"`
}

// результат обработки задачи
type taskProcessResult struct {
	ProcessedIn int64             `json:"processed_in"`
	ResultState taskResponseState `json:"result_state"`
	Message     string            `json:"message"`
	NeedWorkAt  int64             `json:"need_work_at"`
}

// контроллер для доставки посылок
type parcelController struct {
	taskQueue   chan *DiscreteDelivery.Parcel
	description string
	recipient   *parcelControllerRecipient // кому доставляем задачу

	taskChan chan *AsyncTask
	sendFn   func(parcelList []*DiscreteDelivery.Parcel)
}

// создает новый контроллер посылок
func createParcelController(isolation *Isolation.Isolation, taskChan chan *AsyncTask, key, module string, threadCount, frequency, perDeliverLimit int) *parcelController {

	pController := &parcelController{
		taskQueue:   make(chan *DiscreteDelivery.Parcel, bufferLength),
		description: fmt.Sprintf("dpc-%s", key),
		recipient: &parcelControllerRecipient{
			module: module,
			method: taskProcessListMethodName,
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
		DiscreteDelivery.InitDiscreteStash(key, pController.taskQueue, pController.sendFn, frequency, perDeliverLimit, false, true)
	}

	// запускаем жизненный цикл контролера
	// контроллер жив, пока открыт канал
	go pController.lifeRoutine(taskChan)
	return pController
}

// функция доставки посылок
func send(isolation *Isolation.Isolation, pController *parcelController, parcelList []*DiscreteDelivery.Parcel) {

	// увеличиваем число доставок для всех посылок
	for _, parcel := range parcelList {
		parcel.OnSent()
	}

	// делаем запрос к получателю
	response, err := sendRequest(isolation, pController, parcelList)

	if err != nil {

		// если запрос закончился ошибкой, отмечаем все события как ошибочные
		for _, parcel := range parcelList {

			parcel.OnError()
			onDeliveryDone(pController, parcel, makeFailedResponse(err.Error()))
		}

		return
	}

	// перебираем все отправленные посылки и сопоставляем ответы
	for _, parcel := range parcelList {

		// проверяем, что нужная посылка есть в ответе
		if _, isExist := response.TaskProcessResultList[parcel.GetUuid()]; !isExist {

			// задачи нет в ответе по какой-то причине
			parcel.OnError()
			onDeliveryDone(pController, parcel, makeFailedResponse("there is no response for event"))

			continue
		}

		// получаем значение из ответа
		tmp := response.TaskProcessResultList[parcel.GetUuid()]

		// обрабатываем посылку как отправленную
		onDeliveryDone(pController, parcel, &tmp)
	}
}

// выполняет tcp запрос
func sendRequest(isolation *Isolation.Isolation, pController *parcelController, parcelList []*DiscreteDelivery.Parcel) (*parcelControllerResponse, error) {

	// готовим данные для запроса
	requestBytes, err := convertParcelsToRequestData(isolation, pController, parcelList)
	signature := socketAuthKey.GetSignature(conf.GetConfig().SocketKeyMe, requestBytes)

	if err != nil {
		return nil, err
	}

	// получаем урл модуля в который уйдет задача
	url, err := conf.GetModuleSocketUrl(pController.recipient.module)
	if err != nil {
		return nil, fmt.Errorf("unknown module %s", pController.recipient.module)
	}

	// выполняем запрос
	response, err := socket.DoCall(url, pController.recipient.method, requestBytes, signature, 0, isolation.GetCompanyId())

	// произошла ошибка запроса
	if err != nil {
		return nil, err
	} else if response.Status == "error" {
		return nil, errors.New(response.Message)
	}

	// socket.DoCall возвращает map, что не очень удобно
	encoded, _ := go_base_frame.Json.Marshal(response.Response)
	decodedResponse := parcelControllerResponse{}
	err = go_base_frame.Json.Unmarshal(encoded, &decodedResponse)

	if err != nil {
		return nil, errors.New(fmt.Sprintf("%s: response has incorrect format", pController.description))
	}

	return &decodedResponse, err
}

// конвертирует посылки в ожидаемый формат
func convertParcelsToRequestData(isolation *Isolation.Isolation, pController *parcelController, parcelList []*DiscreteDelivery.Parcel) ([]byte, error) {

	var taskMap []*CompanySystem.AsyncTaskRecord

	for _, parcel := range parcelList {
		taskMap = append(taskMap, parcel.GetContent().(*AsyncTask).record)
	}

	// готовим сообщение
	request := parcelControllerRequest{
		Method:    pController.recipient.method,
		TaskList:  taskMap,
		CompanyId: isolation.GetCompanyId(),
		IsGlobal:  isolation.IsGlobal(),
	}

	// кодируем сообщение для шины
	return go_base_frame.Json.Marshal(request)
}

// функция, обрабатывающая результат доставки данных получателям
func onDeliveryDone(pController *parcelController, parcel *DiscreteDelivery.Parcel, response *taskProcessResult) {

	// декодим ивент из данных
	task, parcelOk := parcel.GetContent().(*AsyncTask)

	if !parcelOk {

		pController.say("got incorrect parcel type, can't process it", log.Error)
		return
	}

	// вызываем изменение состояния задачи
	task.onProcessed(response)
}

// формирует ответ, если сервер вернул неподходящие данные
func makeFailedResponse(message string) *taskProcessResult {

	return &taskProcessResult{
		ProcessedIn: 0,
		ResultState: taskResponseStateError,
		Message:     message,
		NeedWorkAt:  0,
	}
}

// жизненный цикл контроллера
func (pController *parcelController) lifeRoutine(taskChan chan *AsyncTask) {

	Isolation.Inc("task-parcel-controller-routines")
	defer Isolation.Dec("task-parcel-controller-routines")

	// слушаем канал с задачами
	for task := range taskChan {
		_ = pController.queue(task)
	}

	// как только канал с задачами закроется, рутина жизненного цикла остановится сама
	// поэтому нет нужно тут что-то отдельно инвалидировать
	close(pController.taskQueue)
}

// добавляет к сообщению описание и передает в указанную функцию
func (pController *parcelController) say(str string, fn func(string)) {

	fn(fmt.Sprintf("%s: %s", pController.description, str))
}

// регистрирует новую посылку в хранилище
func (pController *parcelController) queue(task *AsyncTask) error {

	// пакуем данные в посылку
	parcel := DiscreteDelivery.InitParcel(task.record.UniqueKey, task)

	// задачи сразу пушим в очередь
	go func() {

		if len(pController.taskQueue) >= bufferLength {

			pController.say(fmt.Sprintf("task queue length exceed limit: %d, task dropped", len(pController.taskQueue)), log.Error)
			return
		} else if len(pController.taskQueue) >= bufferLengthWarning {
			pController.say(fmt.Sprintf("task queue length is too long: %d", len(pController.taskQueue)), log.Error)
		}

		// пушим посылку в канал
		pController.taskQueue <- parcel
	}()

	return nil
}
