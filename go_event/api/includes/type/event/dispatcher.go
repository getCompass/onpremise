package Event

/** Пакет системных событий **/
/** В этом файле описана логика пуша событий **/

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"go_event/api/conf"
	Isolation "go_event/api/includes/type/isolation"
	"go_event/api/system/sharding"
	"sync"
)

// Dispatch запушить системное событие в модуль событий
// в этой функции на основе изоляции можно как-то разрулить очереди для отправки события
// пока что все кидается тупо в одну, что по идее не проблема
func Dispatch(isolation *Isolation.Isolation, appEvent *ApplicationEvent) error {

	queue := fmt.Sprintf("%s_%d", conf.GetEventConf().EventService.Queue, isolation.GetCompanyId()%10)
	method := conf.GetEventConf().EventService.Method

	return dispatchViaQueue(isolation, appEvent, queue, method)
}

// тип - данные для пуша события
type dispatchData struct {
	Method    string      `json:"method"`     // метод, который должен обработать данные
	Event     interface{} `json:"event"`      // само событие
	CompanyId int64       `json:"company_id"` // ид компании, в котором произошло событие
	IsGlobal  bool        `json:"is_global"`  // флаг глобальности, определяет взаимодействие между модулями, а не компаниями
}

var queueLockList = map[string]*sync.Mutex{}
var queueLock = sync.Mutex{}

// запушить системное событие в произвольный модуль
// этой штукой, по-хорошему, пользоваться не стоит, все события пропускать через модуль событий
func dispatchViaQueue(isolation *Isolation.Isolation, appEvent *ApplicationEvent, queue string, method string) error {

	// формируем событие
	data := dispatchData{
		Method:    method,
		Event:     appEvent,
		CompanyId: isolation.GetCompanyId(),
		IsGlobal:  isolation.IsGlobal(),
	}

	dataBytes, err := go_base_frame.Json.Marshal(data)
	if err != nil {
		return err
	}

	queueLock.Lock()
	defer queueLock.Unlock()

	lockQueue(queue)
	sharding.Rabbit(conf.GetEventConf().EventService.RabbitKey).SendMessageToQueue(queue, dataBytes)
	unlockQueue(queue)

	return nil
}

// блокируем очередь, чтобы рэббит не очумел ненароком
func lockQueue(queue string) {

	if _, exists := queueLockList[queue]; !exists {
		queueLockList[queue] = &sync.Mutex{}
	}

	queueLockList[queue].Lock()
}

// снимаем блокировку
func unlockQueue(queue string) {

	queueLockList[queue].Unlock()
}
