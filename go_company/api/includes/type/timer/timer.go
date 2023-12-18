package timer

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"sync"
)

// структура для данных задачи в кэше
type TaskItemStruct struct {
	RequestName string
	RequestData json.RawMessage
	Timeout     int64
	Deadline    int64
	TaskList    []string
	UserId      int64
}

type Store struct {
	mu       *sync.Mutex
	taskList map[string]*TaskItemStruct
}

// список задач и времени дедлайна, когда задача должна исполниться
// (в секундах, добавляется к текущему времении при получении первого запроса)
var workMethodDeadlineList = map[string]int64{
	"update_badge": 10,
}

// создаем clear store
func MakeStore() *Store {

	return &Store{
		taskList: make(map[string]*TaskItemStruct),
		mu:       &sync.Mutex{},
	}
}

// SetTimeout устанавливаем задачу с отложенным временем выполнения
func (eTimer *Store) SetTimeout(requestName string, requestKey string, requestData json.RawMessage, taskList []string, isAdd int, userId int64, timeout int) error {

	// ничего не делаем, если там не объявлены задачи
	if eTimer == nil {
		return nil
	}

	eTimer.mu.Lock()
	defer eTimer.mu.Unlock()

	// пробуем достать из кэша точно такой же запрос
	taskData, isExist := eTimer.taskList[requestKey]

	// если в кэше нет такой задачи
	if !isExist {

		if isAdd != 1 {

			taskList = []string{}
		}

		// то добавляем задачу с отложенным выполнением
		taskData = &TaskItemStruct{
			RequestName: requestName,
			RequestData: requestData,
			TaskList:    taskList,
			UserId:      userId,
			Timeout:     functions.GetCurrentTimeStamp() + int64(timeout),
			Deadline:    functions.GetCurrentTimeStamp() + workMethodDeadlineList[requestName],
		}
		eTimer.taskList[requestKey] = taskData
		log.Infof("В %d добавили задачу %v чтобы выполнить через %d", functions.GetCurrentTimeStamp(), requestName, timeout)
		return nil
	}

	// иначе сбрасываем время исполнения задачи, устанавливая новый
	log.Infof("В %d обновляем задачу %v чтобы выполнить через %d", functions.GetCurrentTimeStamp(), requestName, timeout)

	for _, v := range taskList {

		if key := functions.GetKeyInStringSlice(v, taskData.TaskList); key != -1 {

			if isAdd != 1 {

				taskData.TaskList[key] = taskData.TaskList[len(taskData.TaskList)-1]
				taskData.TaskList = taskData.TaskList[:len(taskData.TaskList)-1]
			}
			continue
		}

		if isAdd == 1 {
			taskData.TaskList = append(taskData.TaskList, v)
		}
	}
	taskData.Timeout = functions.GetCurrentTimeStamp() + int64(timeout)
	eTimer.taskList[requestKey] = taskData

	return nil
}

// получаем все задачи из кэша
func (eTimer *Store) GetNeedWorkTask() map[string]*TaskItemStruct {

	needWorkList := make(map[string]*TaskItemStruct)
	eTimer.mu.Lock()

	for key, taskData := range eTimer.taskList {

		// если время для исполнения еще не пришло и дедлайн для задачи еще не наступил, то пропускаем
		if functions.GetCurrentTimeStamp() < taskData.Timeout && functions.GetCurrentTimeStamp() < taskData.Deadline {

			log.Infof("Время для задачи %v еще не пришло", taskData.RequestName)
			continue
		}
		needWorkList[key] = taskData
		delete(eTimer.taskList, key)
	}

	eTimer.mu.Unlock()
	return needWorkList
}

// получить таск
func (eTimer *Store) GetTask(requestKey string) *TaskItemStruct {

	eTimer.mu.Lock()
	defer eTimer.mu.Unlock()

	// пробуем достать из кэша точно такой же запрос
	taskData, isExist := eTimer.taskList[requestKey]

	// если в кэше нет такой задачи
	if !isExist {
		return nil
	}
	delete(eTimer.taskList, requestKey)

	return taskData
}

// получаем все задачи из кэша
func (eTimer *Store) ClearStore() {

	eTimer.mu.Lock()
	eTimer.taskList = make(map[string]*TaskItemStruct)
	eTimer.mu.Unlock()
}
