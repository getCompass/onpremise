package DiscreteDelivery

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	Isolation "go_event/api/includes/type/isolation"
	"time"
)

// тип функции доставщика
type sendParcelsFn func(parcelList []*Parcel)

// DiscreteStash тип дискретное хранилище, по сути буфер, где копятся посылки и передаются курьеру
type DiscreteStash struct {
	uniq string // уникальное имя хранилища

	needSyncResponse     bool // нужно ли дожидаться ответа callback перед следующей отправкой
	needSendOnFullBuffer bool // нужно ли слать посылки, если буфер посылок полный

	// описание распределения нагрузки
	frequency        int       // время между тиками задачи, если 0, то задачи будут отданы сразу после завершения предыдущей пачки
	perDeliveryLimit int       // сколько задач передавать в работу за тик
	parcelBuffer     []*Parcel // список посылок, которые уедут в ближайшей доставке
	nextDelivery     time.Time // время следующей доставки
	isActive         bool      // флаг активности хранилища, если false, то рутины разгребут буфер и закончат работу
	lifeChan         chan bool // канал активности зхранилища

	callback sendParcelsFn // вызов для отправки посылок
}

const minDelay = 50              // минимально возможное время между доставками задач в миллисекундах
const maxCountPerDelivery = 1000 // максимально минимально возможное число задач для передачи за раз

// InitDiscreteStash инициализирует хранилище и запускает его жизненный цикл
func InitDiscreteStash(uniq string, parcelChannel chan *Parcel, callback sendParcelsFn, frequency int, perDeliveryLimit int, needSyncResponse bool, needSendOnFullBuffer bool) *DiscreteStash {

	stash := DiscreteStash{
		uniq:                 uniq,
		frequency:            frequency,
		perDeliveryLimit:     perDeliveryLimit,
		isActive:             true,
		parcelBuffer:         []*Parcel{},
		nextDelivery:         time.Now().Add(3 * time.Second),
		needSendOnFullBuffer: needSendOnFullBuffer,
		needSyncResponse:     needSyncResponse,
		callback:             callback,
	}

	// проверяем минимальное время доставки
	if stash.frequency <= minDelay {

		stash.frequency = minDelay
		log.Warning("passed incorrect send delay, set it to default")
	}

	// проверяем лимит на количество посылок за раз
	if stash.perDeliveryLimit > maxCountPerDelivery {

		stash.perDeliveryLimit = maxCountPerDelivery
		log.Info("passed incorrect count per send, set it to default")
	}

	// получаем канал очереди
	parcelProvider := parcelChannel

	// запускаем рутины жизни хранилища
	go stash.bufferRoutine(parcelProvider)

	log.Infof("%s: new stash started", stash.uniq)
	return &stash
}

// рутина буферизации
// @long имя не по стандарту, поскольку функция используется как переменная
func (stash *DiscreteStash) bufferRoutine(parcelProvider chan *Parcel) {

	var parcel *Parcel
	var isChanOpen bool

	Isolation.Inc("discrete-stash-routines")
	defer Isolation.Dec("discrete-stash-routines")

	for {

		select {

		// отправляем по таймеру
		case <-time.After(stash.nextDelivery.Sub(time.Now())):

			// говорим хранилищу, что пора работать
			stash.deliver()

		// получаем посылку из канала
		case parcel, isChanOpen = <-parcelProvider:

			// если вдруг канал закрылся, то нужно остановить чтение
			// и отправить накопленные данные, чтобы он не пропали
			if !isChanOpen {

				stash.Close("provider chan was closed")
				stash.deliver()

				break
			}

			// добавляем задачу и смотрим, возможно ли еще добавить или буфер уже полный
			// если возможно, то уходим на новую итерацию, ничего не отсылая
			if stash.buffer(parcel) {
				break
			}

			// если буфер полный, то проверяем, нужно ли отправить при наполнении
			if !stash.needSendOnFullBuffer {

				// дожидаемся при необходимости
				time.Sleep(stash.nextDelivery.Sub(time.Now()))
			}

			// говорим хранилищу, что пора работать
			stash.deliver()
		}

		// если хранилище нужно закрыть, то выходим из рутины
		// или если канал закрылся, то тоже останавливаемся
		if stash.shouldClose(parcelProvider) {
			break
		}
	}

	log.Infof("%s stash buffer routine stopped", stash.uniq)
}

// рутина рассылки
func (stash *DiscreteStash) deliver() {

	// если есть задачи, то выполняем их рассылку
	if len(stash.parcelBuffer) != 0 {

		// фиксируем время отправки курьера
		stash.send()
	}

	// устанавливаем время ожидания и говорим, что хранилище свободно
	stash.nextDelivery = time.Now().Add(time.Duration(stash.frequency) * time.Millisecond)
}

// добавляет посылку в хранилище
// возвращает логическое значение в зависимости от того можно ли туда еще запушить задачу или нет
func (stash *DiscreteStash) buffer(parcel *Parcel) bool {

	// добавляем задачу в буфер
	stash.parcelBuffer = append(stash.parcelBuffer, parcel)

	// возвращаем статус возможности взять еще задачу в буфер
	return len(stash.parcelBuffer) < stash.perDeliveryLimit
}

// выполняет доставку элементов
func (stash *DiscreteStash) send() {

	// опустошаем массив
	toDeliverList := stash.parcelBuffer
	stash.parcelBuffer = []*Parcel{}

	if stash.needSyncResponse {
		stash.callback(toDeliverList)
	} else {
		go stash.callback(toDeliverList)
	}
}

// проверяет, нужно ли хранилищу закрыться
func (stash *DiscreteStash) shouldClose(parcelProvider chan *Parcel) bool {

	return !stash.isActive && len(stash.parcelBuffer) == 0 && len(parcelProvider) == 0
}

// Close объявляет начало завершения жизненного цикла хранилища
func (stash *DiscreteStash) Close(reason string) {

	log.Infof("%s stash closed: %s", stash.uniq, reason)
	stash.isActive = false
}
