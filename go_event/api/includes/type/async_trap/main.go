package AsyncTrap

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	Isolation "go_event/api/includes/type/isolation"
	"strconv"
	"sync"
	"time"
)

// входные данные для установки ловушки на событие
type asyncTrap struct {
	uniqueKey    string            // уникальный ключ ловушки
	module       string            // кто обработал событие
	createdAfter int64             // фильтр по времени для события
	expectedKey  string            // тип обработанного события
	filterList   map[string]string // дополнительные фильтры для данных события

	trappedEventCount int
}

// MakeAsyncTrap создает новую ловушку обработанных ключей
func MakeAsyncTrap(uniqueKey string, subscriber string, createdAfter int64, expectedKey string, filterList map[string]string) *asyncTrap {
	return &asyncTrap{uniqueKey: uniqueKey, module: subscriber, createdAfter: createdAfter, expectedKey: expectedKey, filterList: filterList}
}

// выполняет анализ и захват события по необходимости
func (eTrap *asyncTrap) process(module, key string, timestamp int64, bytes []byte) {

	// проверяем базовые данные события и ловушки
	if eTrap.module != module || eTrap.expectedKey != key || eTrap.createdAfter > timestamp {
		return
	}

	// если есть фильтры для полей события
	if len(eTrap.filterList) > 0 {

		// конвертируем данные сообщения в строковый набор
		var prepared map[string]interface{}

		log.Infof(string(bytes))
		if err := json.Unmarshal(bytes, &prepared); err != nil {
			return
		}

		compareTo := eTrap.compareData(prepared, map[string]string{})

		// бежим по всем нужным фильтрам
		for filterKey, filterValue := range eTrap.filterList {

			// проверяем, что есть такой ключ в событии
			if eventValue, isExists := compareTo[filterKey]; !isExists || eventValue != filterValue {
				return
			}
		}
	}

	// если фильтров нет, то считаем событие отловленным
	eTrap.trappedEventCount++
}

// выполняет сравнение данных по фильтрам
func (eTrap *asyncTrap) compareData(prepared map[string]interface{}, compareTo map[string]string) map[string]string {

	for k, v := range prepared {

		switch t := v.(type) {
		case int:
			compareTo[k] = strconv.Itoa(t)
		case int32:
			compareTo[k] = strconv.Itoa(int(t))
		case int64:
			compareTo[k] = strconv.Itoa(int(t))
		case float64:
			compareTo[k] = strconv.Itoa(int(t))
		case string:
			compareTo[k] = v.(string)
		case map[string]interface{}:
			compareTo = eTrap.compareData(v.(map[string]interface{}), compareTo)
		default:
			// ничего не делаем, игнорируем все остальные типы
		}
	}

	return compareTo
}

// ожидает, пока сработает ловушка
// возвращает ложь, если события не было
func (eTrap *asyncTrap) waitTillTrapped(timeout int) bool {

	// считаем дату таймаута
	waitUntil := time.Now().Add(time.Duration(timeout) * time.Second)

	for {

		// проверяем число событий
		if eTrap.trappedEventCount > 0 {
			return true
		}

		time.Sleep(100 * time.Millisecond)

		// если уже таймаут, то заканчиваем и говорим, что не нашли
		if time.Now().After(waitUntil) {
			return false
		}
	}
}

type eventHunter struct {
	trapList map[string]*asyncTrap
	mx       sync.RWMutex
}

// Process передает обработку события всем ловушкам
func Process(isolation *Isolation.Isolation, module, key string, createdAt int64, data []byte) {

	// получаем ловушки из изоляции
	eHunter := leaseEventHunter(isolation)
	if eHunter == nil {
		return
	}

	eHunter.mx.RLock()
	defer eHunter.mx.RUnlock()

	// ловим событие всеми ловушками
	for _, trap := range eHunter.trapList {
		go trap.process(module, key, createdAt, data)
	}
}

// SetTrap устанавливает ловушку
// в ловушку попадают только штуки, обработанные в изоляции
func SetTrap(isolation *Isolation.Isolation, trap *asyncTrap, timeout int) error {

	// получаем ловушки из изоляции
	eHunter := leaseEventHunter(isolation)
	if eHunter == nil {
		return fmt.Errorf("there is no event hunter for isolation %s", isolation.GetUniq())
	}

	eHunter.mx.Lock()
	eHunter.trapList[trap.uniqueKey] = trap
	eHunter.mx.Unlock()

	time.AfterFunc(time.Duration(timeout)*time.Second, func() {

		eHunter.mx.Lock()
		delete(eHunter.trapList, trap.uniqueKey)
		eHunter.mx.Unlock()
	})

	return nil
}

// WaitTillTrapped ожидает чего-то, что может попасть в ловушку
func WaitTillTrapped(isolation *Isolation.Isolation, uniqueKey string, timeout int) bool {

	// получаем ловушки из изоляции
	eHunter := leaseEventHunter(isolation)
	if eHunter == nil {
		return false
	}

	var trap *asyncTrap
	var isExists bool

	eHunter.mx.RLock()
	trap, isExists = eHunter.trapList[uniqueKey]
	eHunter.mx.RUnlock()

	if !isExists {
		return false
	}

	return trap.waitTillTrapped(timeout)
}
