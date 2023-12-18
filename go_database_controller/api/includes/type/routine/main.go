package routine

import (
	"context"
	"fmt"
	"go_database_controller/api/includes/type/logger"
	"strings"
	"sync"
	"time"
)

const StatusAwait = 10 // рутина еще в работе
const StatusDone = 20  // рутина успешно завершилась
const StatusError = 30 // рутина завершилась с ошибкой

// время жизни рутины
const routineTtl = 30 * time.Minute

// разделитель для ключа рутины
const keySeparator = "_"

// тип — результат работы рутины
type Status struct {
	Message string
	Status  int32
}

// тип – рутина, хранил в себе только канал со статусом
// сам статус в ней не хранится, его можно получить через функцию routine.getStatus
type routine struct {
	ch        chan *Status
	log       *logger.Log
	expiresAt time.Time
}

// список зарегистрированных хранилищ в пакетах
var packageRoutineStore = map[string]*store{}

// возвращает текущий статус рутины
// если рутина не закончилась за время контекста, то возвращает статус pending
func (r *routine) getStatus(ctx context.Context) *Status {

	var result *Status

	select {
	case <-ctx.Done():

		// если время ожидания истекло
		return MakeRoutineStatus(StatusAwait, "routine is in progress")

	case result = <-r.ch:

		// если результат пришел в процесс
		return result
	}
}

// MakeRoutineStatus создает новый результат исполнения рутины
func MakeRoutineStatus(status int32, message string) *Status {

	return &Status{
		Message: message,
		Status:  status,
	}
}

// тип — хранилище рутин
type store struct {
	name  string
	Store map[string]*routine
	mx    sync.Mutex
}

// MakeStore создает новое хранилище рутин
func MakeStore(packageName string) *store {

	// если такого пакета еще не зарегистрировано - добавляем
	if storeItem, isExist := packageRoutineStore[packageName]; isExist {
		return storeItem
	}

	storeItem := store{
		Store: map[string]*routine{},
		name:  packageName,
	}

	packageRoutineStore[packageName] = &storeItem

	// запускаем рутину жизненного цикла
	go storeItem.lifeRoutine()

	return &storeItem
}

// рутина жизни хранилища, подчищает устаревшие рутины
func (rStore *store) lifeRoutine() {

	for {

		rStore.mx.Lock()

		for key, routineItem := range rStore.Store {

			if routineItem.expiresAt.Before(time.Now()) {
				delete(rStore.Store, key)
			}
		}

		rStore.mx.Unlock()
		time.Sleep(5 * time.Minute)
	}
}

// добавляет новый канал ожидания рутины
func (rStore *store) Push(routineKey string, routineChan chan *Status, log *logger.Log) string {

	routineKey = fmt.Sprintf("%s%s%s", rStore.name, keySeparator, routineKey)

	rStore.mx.Lock()
	defer rStore.mx.Unlock()

	rStore.Store[routineKey] = &routine{
		ch:        routineChan,
		expiresAt: time.Now().Add(routineTtl),
		log:       log,
	}

	return routineKey
}

// GetRoutineStatus ожидает завершения рутины по указанному ключу
// если рутина не успевает выполниться до завершения контекста, то отдает статус «ожидает»
func (rStore *store) GetRoutineStatus(ctx context.Context, routineKey string) *Status {

	rStore.mx.Lock()
	routineItem, ok := rStore.Store[routineKey]

	// без defer снимаем, нет смысла держать ее
	rStore.mx.Unlock()

	if !ok {

		// если вдруг рутина не существует
		return MakeRoutineStatus(StatusError, "routine does not exist")
	}

	return routineItem.getStatus(ctx)
}

// ожидает завершения рутины по указанному ключу
// если рутина не успевает выполниться до завершения контекста, то отдает статус «ожидает»
func (rStore *store) GetRoutineLog(routineKey string) string {

	rStore.mx.Lock()
	routineItem, ok := rStore.Store[routineKey]

	// без defer снимаем, нет смысла держать ее
	rStore.mx.Unlock()

	if !ok {

		// если вдруг рутина не существует
		return ""
	}

	return routineItem.log.ReadLog()
}

// ожидает завершения рутины по указанному ключу
// если рутина не успевает выполниться до завершения контекста, то отдает статус «ожидает»
func GetRoutineLog(routineKey string) string {

	rStore, err := getStoreByRoutineKey(routineKey)

	if err != nil {

		return ""
	}

	return rStore.GetRoutineLog(routineKey)
}

// ожидает завершения рутины по указанному ключу
// если рутина не успевает выполниться до завершения контекста, то отдает статус «ожидает»
func GetRoutineStatus(ctx context.Context, routineKey string) *Status {

	rStore, err := getStoreByRoutineKey(routineKey)

	if err != nil {
		return MakeRoutineStatus(StatusError, fmt.Sprintf("passed incorrect key: %s", err.Error()))
	}

	return rStore.GetRoutineStatus(ctx, routineKey)
}

// получит хранилище из ключа рутины
func getStoreByRoutineKey(routineKey string) (*store, error) {

	ex := strings.Split(routineKey, keySeparator)

	if len(ex) != 2 {
		return nil, fmt.Errorf("incorrect routineKey %s", routineKey)
	}

	if _, found := packageRoutineStore[ex[0]]; !found {
		return nil, fmt.Errorf("store doesnt exist %s", ex[0])
	}

	return packageRoutineStore[ex[0]], nil
}
