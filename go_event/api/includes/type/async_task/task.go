package AsyncTask

import (
	"encoding/json"
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	CompanySystem "go_event/api/includes/type/database/company_system"
	"math"
	"sync/atomic"
	"time"
)

// TaskType вспомогательный тип — тип задаче
type TaskType int

const (
	TaskTypeSingle             TaskType = 1 // обычный таск, разовый
	TaskTypePeriodic           TaskType = 2 // периодический таск, не имеет лимита на ошибки, может быть только уникальным
	TaskTypePeriodicNonSavable TaskType = 4 // периодический таск, не имеет лимита на ошибки, не может быть сохранен
)

// вспомогательный тип — состояние задачи
type taskState int

const (
	taskStateAwait  taskState = 1 // ожидающий таск
	taskStateInWork taskState = 2 // таск в работе
)

// вспомогательный тип — статус ответа
type taskResponseState int

const (
	taskResponseStateDone                taskResponseState = 1 // задача завершена, можно удалять
	taskResponseStateExpectNextIteration taskResponseState = 2 // часть задачи выполнена, но сама задача еще требует работы
	taskResponseStateNotWorked           taskResponseState = 3 // задача не была взята в работу по какой-либо причине
	taskResponseStateError               taskResponseState = 4 // в процессе исполнения задачи возникла ошибка
)

const attentionProcessTimeLimit = 80 * time.Millisecond // время обработки задачи, после которого оно считается медленным
const errorLimit = 3                                    // допустимое число ошибок

// AsyncTask тип — задача приложения
type AsyncTask struct {

	// большую часть полей наследуем от базы данных так проще работать,
	// они пишутся и сохраняются в базу данных
	// ------------------------------------------------------
	record *CompanySystem.AsyncTaskRecord

	// нехранимые значения, косвенно влияют на хранение задач
	// ------------------------------------------------------
	isCompleted int // завершилась ли задача, если завершилась, то при сохранении ее нужно положить в историю
	isQueued    int // нужно ли сохранять задачу

	// рантайм значения, не сохраняются
	// --------------------------------
	errorLimit     int
	lastWorkAt     int64 // когда последний раз задача бралась в работу
	iterationCount int   // сколько итерация для задачи было

	state int32
	timer *time.Timer

	onUpdateFn   func(task *AsyncTask) error // функция обновления данных задачи
	onCompleteFn func(task *AsyncTask) error // функция обновления данных задачи
}

// функция загушка
func emptyTaskFunc(_ *AsyncTask) error {

	return nil
}

// формирует новую запись задачи и новую задачу
func makeRawAsyncTask(taskName string, taskType TaskType, needWork int64, module, group string, data json.RawMessage) *AsyncTask {

	return makeRawAsyncTaskFromRecord(&CompanySystem.AsyncTaskRecord{
		IsFailed:   0,
		TaskType:   int(taskType),
		NeedWorkAt: needWork,
		ErrorCount: 0,
		CreatedAt:  time.Now().Unix(),
		UpdatedAt:  0,
		UniqueKey:  makeTaskUniqueKey(taskName, taskType),
		Module:     module,
		Group:      group,
		Data:       data,
		Name:       taskName,
	})
}

// формирует новую задачу из ранее созданной записи задачи
func makeRawAsyncTaskFromRecord(record *CompanySystem.AsyncTaskRecord) *AsyncTask {

	task := &AsyncTask{
		record:         record,
		isCompleted:    0,
		isQueued:       0,
		lastWorkAt:     0,
		iterationCount: 0,
		state:          int32(taskStateAwait),

		onCompleteFn: emptyTaskFunc,
		onUpdateFn:   emptyTaskFunc,
	}

	return task
}

// создает уникальный ключ для задачи
// периодические задачи имеют уникальный ключ, остальные генерируемый
func makeTaskUniqueKey(taskName string, taskType TaskType) string {

	switch taskType {
	case TaskTypePeriodic, TaskTypePeriodicNonSavable:
		return taskName
	}

	taskName = fmt.Sprintf("%s:%s", taskName, functions.GenerateUuid())

	if len(taskName) > 63 {
		taskName = taskName[:64]
	}

	return taskName
}

// вызывается при попадании задачи в очередь исполнения
func (task *AsyncTask) onQueued(onCreateFn func(task *AsyncTask) error, onUpdateFn func(task *AsyncTask) error, onCompleteFn func(task *AsyncTask) error) error {

	if task.isQueued == 1 {
		return errors.New("task already queued")
	}

	// отмечаем, что уже в очереди
	task.isQueued = 1

	if !task.isSavable() {
		return nil
	}

	// если сохраняемый, то добавляем функции сохранения
	task.onUpdateFn = onUpdateFn
	task.onCompleteFn = onCompleteFn

	return onCreateFn(task)
}

// вызывается, когда задача из хранилища передается в работу
func (task *AsyncTask) onSent() {

	// следующая итерация задачи через секунду
	task.record.NeedWorkAt = time.Now().Unix() + 1
	task.lastWorkAt = time.Now().Unix()

	log.Infof("task %s in work", task.record.UniqueKey)

	// указываем, что задача в работе
	atomic.StoreInt32(&task.state, int32(taskStateInWork))

	// ставим таймер на смену статуса для задачи
	// как только он завершится, то переводим задачу снова в ожидание
	task.timer = time.AfterFunc(30*time.Second, func() {
		atomic.StoreInt32(&task.state, int32(taskStateAwait))
	})
}

// вызывается, когда задача из хранилища передается в работу
// @long switch
func (task *AsyncTask) onProcessed(result *taskProcessResult) {

	// делаем через дефер, чтобы функции были покороче, и постобработка вызывалась безусловно
	defer task.afterProcessed(result)

	// сбрасываем таймер состояния, чтобы он не перевел задачу
	task.timer.Stop()

	switch result.ResultState {
	case taskResponseStateDone:

		// если задача была выполнена, то отмечаем ее как завершенную
		if err := task.onDone(); err != nil {
			log.Error(err.Error())
		}

		return
	case taskResponseStateError:

		// если задача вернулась с ошибкой, то увеличиваем количество ошибок
		// с каждой ошибкой увеличиваем время, которое задача будет ожидать до следующей отправки
		task.record.ErrorCount = task.record.ErrorCount + 1
		task.record.NeedWorkAt = task.record.NeedWorkAt + int64(math.Max(float64(task.record.ErrorCount), 10.0)*1)

		nextAttemptAt := time.Unix(task.record.NeedWorkAt, 0).Format(time.UnixDate)
		log.Error(fmt.Sprintf("task %s process ends with error %s, next attempt at %s", task.record.UniqueKey, result.Message, nextAttemptAt))

		break
	case taskResponseStateExpectNextIteration, taskResponseStateNotWorked:

		// ничего не делаем, если задача не бралась в работу
		// или ожидает следующей итерации
		log.Warning(fmt.Sprintf("task %s requeued on status %d", task.record.UniqueKey, result.ResultState))
		break
	default:

		// если вдруг задача закончилась непонятным статусом, то завершаем ее
		log.Error(fmt.Sprintf("task %s process ends with unknown state %d", task.record.UniqueKey, result.ResultState))
		task.isCompleted = 1
	}

	// сохраняем задачу
	if err := task.onUpdate(); err != nil {
		log.Error(err.Error())
	}
}

// вызывается после того, как обработка результата завершилась
func (task *AsyncTask) afterProcessed(result *taskProcessResult) {

	// указываем, когда задачу нужно будет взять в работу в следующий раз
	if task.record.NeedWorkAt < result.NeedWorkAt {
		task.record.NeedWorkAt = result.NeedWorkAt
	}

	// проверяем на ошибки, если их много, то отмечаем проваленным
	if task.isErrorLimitExceeded() {

		// если сфэйлился, то пишем об этом в бд
		task.record.IsFailed = 1
		_ = task.onUpdate()
	}

	// переводим задачу в состояние ожидания
	atomic.StoreInt32(&task.state, int32(taskStateAwait))

	// фиксируем долгое исполнение
	if time.Duration(result.ProcessedIn)*time.Millisecond > attentionProcessTimeLimit {
		log.Warningf("task %s took too much time to process %dms", task.record.UniqueKey, result.ProcessedIn)
	}
}

// вызывается при завершении задачи
func (task *AsyncTask) onDone() error {

	task.isCompleted = 1
	return task.onCompleteFn(task)
}

// вызывается при обновлении задачи
func (task *AsyncTask) onUpdate() error {

	return task.onUpdateFn(task)
}

// проверяет, можно ли брать задачу в работу
// если эта функция вернут ложь, то задача будет считаться неактуальной и будет удалена
func (task *AsyncTask) canBeWorked() bool {

	if task.isSavable() && !task.record.IsStored() {

		log.Errorf("%s task is not stored", task.record.UniqueKey)
		return false
	}

	if task.isCompleted == 1 {

		log.Infof("%s task done", task.record.UniqueKey)
		return false
	}

	if task.isErrorLimitExceeded() {

		log.Errorf("%s can't be worked, error limit %d exceeded", task.record.UniqueKey, errorLimit)
		return false
	}

	if task.record.IsFailed == 1 {

		log.Errorf("%s task failed", task.record.UniqueKey)
		return false
	}

	return true
}

// проверяет задачу на лимит ошибок
func (task *AsyncTask) isErrorLimitExceeded() bool {

	return TaskType(task.record.TaskType) == TaskTypeSingle && task.record.ErrorCount >= errorLimit
}

// проверяем, нужно ли брать задачу в работу в указанное время
func (task *AsyncTask) needBeWorked(currentTime int64) bool {

	// проверяем, что с задачей нужно работать и не брали ее в работу недавно
	return task.record.NeedWorkAt <= currentTime && atomic.LoadInt32(&task.state) == int32(taskStateAwait)
}

// возвращает, нужно ли сохранять задачу в бд
func (task *AsyncTask) isSavable() bool {

	return task.record.TaskType != int(TaskTypePeriodicNonSavable)
}
