package timer_methods

import (
	"encoding/json"
	Isolation "go_company/api/includes/type/isolation"
	"google.golang.org/grpc/status"
	"sync"
)

// структура для данных задачи в кэше
type taskItemStruct struct {
	mu          *sync.Mutex
	RequestName string
	RequestData json.RawMessage
	Timeout     int64
	Deadline    int64
	TaskList    []string
	UserId      int64
}

type Store struct {
	taskList  map[string]*taskItemStruct
	taskStore sync.Map
}

// SetTimeout устанавливаем задачу с отложенным временем выполнения
func SetTimeout(isolation *Isolation.Isolation, requestName string, requestKey string, requestData json.RawMessage, taskList []string, isAdd int, userId int64, timeout int) error {

	if len(requestName) < 1 {
		return status.Error(401, "passed bad request_name")
	}

	if len(requestKey) < 1 {
		return status.Error(401, "passed bad request_key")
	}

	// получаем задачи из изоляции
	err := isolation.TimerStore.SetTimeout(requestName, requestKey, requestData, taskList, isAdd, userId, timeout)

	return err
}
