package AsyncTask

import (
	"encoding/json"
	"errors"
	"fmt"
	Isolation "go_event/api/includes/type/isolation"
)

// CreateAndPushToDiscrete создает задачу и сразу пушит ее в дискретную очередь
func CreateAndPushToDiscrete(isolation *Isolation.Isolation, taskName string, taskType TaskType, needWorkAt int64, data json.RawMessage, module string, group string) error {

	return PushToDiscrete(isolation, makeRawAsyncTask(taskName, taskType, needWorkAt, module, group, data))
}

// PushToDiscrete добавляет задачу в работу
// пока что все доставляется дискретными доставщиками, иные виды доставки должны реализовать свою функцию
func PushToDiscrete(isolation *Isolation.Isolation, task *AsyncTask) error {

	tcStore := leaseTaskDiscreteControllerStore(isolation)
	if tcStore == nil {
		return errors.New("there is no task controller in isolation")
	}

	tStore := leaseTaskStoreList(isolation)
	if tcStore == nil {
		return errors.New("there is no task initFn in isolation")
	}

	// пытаемся получить существующий репозитория для задач
	// если не находим, то создаем новый репозиторий
	tRepository := tStore.getTaskRepository(task.record.Module, task.record.Group)
	if tRepository == nil {

		tRepository = tStore.makeTaskRepository(task.record.Module, task.record.Group)

		// создаем контроллер (или убеждаемся, что он существует) и кладем в него задачу
		// не кладем сразу в хранилище, поскольку нужно убедиться, что оно разгребается
		pController := tcStore.getParcelController(tRepository.uniq)
		if pController != nil {
			return fmt.Errorf("parcel controller %s:%s already exists", task.record.Module, task.record.Group)
		}

		pController = tcStore.makeParcelController(tRepository.taskCh, tRepository.uniq, task.record.Module)
	}

	// пушим задачу в репозиторий задач
	return tRepository.push(task)
}
