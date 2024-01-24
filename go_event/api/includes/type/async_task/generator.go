package AsyncTask

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	CompanyConfig "go_event/api/includes/type/company_config"
	Generator "go_event/api/includes/type/generator"
	Isolation "go_event/api/includes/type/isolation"
	"sync"
	"time"
)

var taskGeneratorStore = struct {
	generatorList map[string]func(isolation *Isolation.Isolation)
	mx            sync.RWMutex
}{
	generatorList: map[string]func(isolation *Isolation.Isolation){},
	mx:            sync.RWMutex{},
}

// промежуточная структура задачи,
// из которой потом сгенерируется полноценная задача
type rawGeneratedTask struct {
	taskName string
	taskType TaskType
	module   string
	group    string
	data     json.RawMessage
}

// MakeTaskGenerator создает новый генератор задач
// генераторы существуют глобально, поэтому в изоляции компаний не записываются
func MakeTaskGenerator(taskName string, taskType TaskType, period int, data json.RawMessage, module string, group string) {

	taskGeneratorStore.mx.Lock()
	defer taskGeneratorStore.mx.Unlock()

	if _, exists := taskGeneratorStore.generatorList[taskName]; exists {
		return
	}

	// создаем генератор
	generator := Generator.MakeGenerator(makeGeneratorName(taskName), period, func() (interface{}, error) {

		return rawGeneratedTask{taskName, taskType, module, group, data}, nil
	})

	// запоминаем функцию, которой будем инициализировать новые изоляции
	taskGeneratorStore.generatorList[taskName] = func(isolation *Isolation.Isolation) {
		go isolationRoutine(isolation, generator)
	}

	// пробегаемся по всем изоляциям и запускаем в них рутины генераторов
	CompanyConfig.IterateOverActive(taskGeneratorStore.generatorList[taskName])
	_ = Generator.StartGenerator(generator)
}

// StopTaskGenerator останавливает генератор задач
func StopTaskGenerator(taskName string) {

	taskGeneratorStore.mx.Lock()
	defer taskGeneratorStore.mx.Unlock()

	if _, exists := taskGeneratorStore.generatorList[taskName]; !exists {
		return
	}

	// удаляем и останавливаем
	delete(taskGeneratorStore.generatorList, taskName)
	Generator.StopGenerator(makeGeneratorName(taskName))
}

// рутина, которая должна тикать в изоляции
func isolationRoutine(isolation *Isolation.Isolation, generator *Generator.Generator) {

	var data interface{}
	var ok bool
	var key = isolation.GetUniq()
	var ch = generator.AttachReceiver(key)

	Isolation.Inc("task-generator-isolation")
	defer Isolation.Dec("task-generator-isolation")

	for {

		select {

		// генератор отдал команду на тик
		case data, ok = <-ch.Channel():

			// если канал закрылся
			if !ok {
				return
			}

			ch.Release()
			rawGen := data.(*rawGeneratedTask)
			task := makeRawAsyncTask(rawGen.taskName, rawGen.taskType, time.Now().Unix(), rawGen.module, rawGen.group, rawGen.data)

			if err := PushToDiscrete(isolation, task); err != nil {
				log.Errorf("can't push task %s in isolation %s: %s", rawGen.taskName, isolation.GetUniq(), err.Error())
			}
		case <-isolation.GetContext().Done():

			generator.DetachReceiver(key)
			return
		}
	}
}

// определяет имя для генератора на основе имени задачи
func makeGeneratorName(taskName string) string {

	return fmt.Sprintf("task_generator:%s", taskName)
}

// прогоняет изоляцию через известные генераторы
func attachIsolationToGenerators(isolation *Isolation.Isolation) {

	taskGeneratorStore.mx.RLock()
	defer taskGeneratorStore.mx.RUnlock()

	for _, fn := range taskGeneratorStore.generatorList {
		fn(isolation)
	}
}
