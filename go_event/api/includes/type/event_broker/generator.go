package EventBroker

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	CompanyConfig "go_event/api/includes/type/company_config"
	Event "go_event/api/includes/type/event"
	Generator "go_event/api/includes/type/generator"
	Isolation "go_event/api/includes/type/isolation"
	"sync"
	"time"
)

var eventGeneratorStore = struct {
	generatorList map[string]func(isolation *Isolation.Isolation)
	mx            sync.Mutex
}{
	generatorList: map[string]func(isolation *Isolation.Isolation){},
	mx:            sync.Mutex{},
}

// MakeEventGenerator создает новый генератор событий
// генераторы существуют глобально, поэтому в изоляции компаний не записываются
func MakeEventGenerator(eventType string, period int, data json.RawMessage, onlyForGlobal bool) {

	eventGeneratorStore.mx.Lock()
	defer eventGeneratorStore.mx.Unlock()

	if _, exists := eventGeneratorStore.generatorList[eventType]; exists {
		return
	}

	// создаем генератор
	generator := Generator.MakeGenerator(makeGeneratorName(eventType), period, func() (interface{}, error) {

		event := &Event.ApplicationEvent{
			EventType:        eventType,
			SourceType:       "event_generator",
			SourceIdentifier: "go_event",
			CreatedAt:        time.Now().Unix(),
			EventData:        data,
			Version:          0,
			DataVersion:      0,
			Uuid:             functions.GenerateUuid(),
		}

		return event, nil
	})

	// запоминаем функцию, которой будем инициализировать новые изоляции
	eventGeneratorStore.generatorList[eventType] = func(isolation *Isolation.Isolation) {
		go isolationRoutine(isolation, generator)
	}

	if onlyForGlobal {

		// запускаем рутину генератора в глобальной изоляции
		eventGeneratorStore.generatorList[eventType](Isolation.Global())
	} else {

		// пробегаемся по всем изоляциям и запускаем в них рутины генераторов
		CompanyConfig.IterateOverActive(eventGeneratorStore.generatorList[eventType])
	}

	_ = Generator.StartGenerator(generator)
}

// StopEventGenerator останавливает генератор событий
func StopEventGenerator(eventType string) {

	eventGeneratorStore.mx.Lock()
	defer eventGeneratorStore.mx.Unlock()

	if _, exists := eventGeneratorStore.generatorList[eventType]; !exists {
		return
	}

	// удаляем и останавливаем
	delete(eventGeneratorStore.generatorList, eventType)
	Generator.StopGenerator(makeGeneratorName(eventType))
}

// рутина, которая должна тикать в изоляции
func isolationRoutine(isolation *Isolation.Isolation, generator *Generator.Generator) {

	var data interface{}
	var ok bool
	var key = isolation.GetUniq()
	var ch = generator.AttachReceiver(key)

	Isolation.Inc("event-generator-isolation")
	defer Isolation.Dec("event-generator-isolation")

	for {

		select {

		// генератор отдал команду на тик
		case data, ok = <-ch.Channel():

			// если канал закрылся
			if !ok {
				return
			}

			ch.Release()
			broadcast(isolation, data.(*Event.ApplicationEvent))
		case <-isolation.GetContext().Done():

			generator.DetachReceiver(key)
			return
		}
	}
}

// определяет имя для генератора на основе названия события
func makeGeneratorName(eventName string) string {

	return fmt.Sprintf("task_generator:%s", eventName)
}

// прогоняет изоляцию через известные генераторы
func attachIsolationToGenerators(isolation *Isolation.Isolation) {

	eventGeneratorStore.mx.Lock()
	defer eventGeneratorStore.mx.Unlock()

	for _, fn := range eventGeneratorStore.generatorList {
		fn(isolation)
	}
}
