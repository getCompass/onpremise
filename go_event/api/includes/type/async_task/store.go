package AsyncTask

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	AsyncTrap "go_event/api/includes/type/async_trap"
	Isolation "go_event/api/includes/type/isolation"
	"sync"
	"sync/atomic"
	"time"
)

const delayBetweenEmptyIterations = 100 // задержка между итерациями в неактивных компаниях

// репозиторий с задачами
// сохраняет задачи и определяет, когда они должны браться в работу
type taskRepository struct {
	uniq  string   // уникальный идентификатор хранилища
	count int32    // количество задач
	store sync.Map // список задач

	module string // к какому модулю привязано хранилище
	group  string // группа задачи для хранилища

	// канал, который должен слушать обработчик задач
	// репозиторий просто пушит сюда данные
	taskCh chan *AsyncTask

	createTaskFn   func(task *AsyncTask) error
	updateTaskFn   func(task *AsyncTask) error
	completeTaskFn func(task *AsyncTask) error
}

// возвращает репозиторий задач с указанным ключом
func makeTaskRepository(isolation *Isolation.Isolation, trStore *taskRepositoryStore, module, group string) *taskRepository {

	// генерируем уникальный ключ для репозитория задач
	uniq := fmt.Sprintf("%s:%s", module, group)

	trStore.mx.Lock()
	defer trStore.mx.Unlock()

	tRepository := &taskRepository{
		uniq:   uniq,
		count:  0,
		store:  sync.Map{},
		module: module,
		group:  group,
		taskCh: make(chan *AsyncTask, 100),
	}

	tRepository.createTaskFn = func(task *AsyncTask) error {
		return taskCreateClosureFn(isolation, task)
	}

	tRepository.updateTaskFn = func(task *AsyncTask) error {
		return taskUpdateClosureFn(isolation, task)
	}

	tRepository.completeTaskFn = func(task *AsyncTask) error {
		return taskDoneClosureFn(isolation, task)
	}

	go tRepository.providerRoutine(isolation.GetContext())
	trStore.store[uniq] = tRepository

	return tRepository
}

// функция сохранения данных задачи
func taskCreateClosureFn(isolation *Isolation.Isolation, task *AsyncTask) error {

	return createTask(isolation, task)
}

// функция сохранения данных задачи
func taskUpdateClosureFn(isolation *Isolation.Isolation, task *AsyncTask) error {

	return updateTask(isolation, task)
}

// задача завершения таска
func taskDoneClosureFn(isolation *Isolation.Isolation, task *AsyncTask) error {

	// оповещаем ловушки о завершенности задачи
	go AsyncTrap.Process(isolation, task.record.Module, task.record.Name, task.record.CreatedAt, task.record.Data)
	return completeTask(isolation, task)
}

// сохраняет задачу в хранилище
// важный момент, прежде чем пушить задачу, убедись, что кто-то разгребает очередь
func (tRepository *taskRepository) push(task *AsyncTask) error {

	// вдруг такая задача уже существует
	if _, ok := tRepository.store.Load(task.record.UniqueKey); ok {

		log.Infof("%s: task %s already stored", tRepository.uniq, task.record.UniqueKey)
		return nil
	}

	// пробрасываем функции-зависимости в задачу; возможно тут стоит пробрасывать само хранилище,
	// но оно не инициализируется как отдельная структура, поэтому отдаем через функции
	if err := task.onQueued(tRepository.createTaskFn, tRepository.updateTaskFn, tRepository.completeTaskFn); err != nil {
		return err
	}

	tRepository.store.LoadOrStore(task.record.UniqueKey, task)
	atomic.AddInt32(&tRepository.count, 1)

	log.Infof("%s: task %s stored", tRepository.uniq, task.record.UniqueKey)
	return nil
}

// удаляет задачу из хранилища
func (tRepository *taskRepository) pop(task *AsyncTask) {

	tRepository.store.Delete(task.record.UniqueKey)
	atomic.AddInt32(&tRepository.count, -1)
}

// наполняет указанный канал задачами
// @long
func (tRepository *taskRepository) providerRoutine(ctx context.Context) {

	// счетчик для увеличения времени между ожиданиями
	iteration := 1

	Isolation.Inc("task-store-routines")
	defer func() {

		Isolation.Dec("task-store-routines")
		close(tRepository.taskCh)
	}()

	for {

		// проверяем контекст, возможно он уже все
		if ctx.Err() != nil {
			break
		}

		// если есть задачи и есть возможность пушить их в очередь, пушим
		if atomic.LoadInt32(&tRepository.count) == 0 || cap(tRepository.taskCh) == len(tRepository.taskCh) {

			// если нет, то ждем
			iteration++
			time.Sleep(tRepository.calculateSleepDuration(iteration))

			continue
		}

		// фиксируем текущее время
		currentTime := time.Now().Unix()
		pushedCount := 0
		iteration = 1

		// закидываем столько задач, сколько сможем
		tRepository.store.Range(func(key interface{}, value interface{}) bool {

			task, ok := value.(*AsyncTask)

			// если задачу нельзя брать в работу, удаляем ее
			if !ok || !task.canBeWorked() {

				tRepository.pop(task)
				return true
			}

			// если задачу еще не время брать в работу
			if !task.needBeWorked(currentTime) {
				return true
			}

			// говорим таску, что он отправлен в работу из хранилища
			task.onSent()

			pushedCount++
			tRepository.taskCh <- task

			// закидываем столько элементов, сколько вмещает канал
			return cap(tRepository.taskCh) > len(tRepository.taskCh)
		})

		if pushedCount == 0 {

			// если нет, то ждем
			time.Sleep(tRepository.calculateSleepDuration(iteration))
		}
	}
}

// считает время ожидания
func (tRepository *taskRepository) calculateSleepDuration(iteration int) time.Duration {

	if iteration > 50 {
		iteration = 60
	}

	if tRepository.group == "index" {
		iteration = 1
	}

	return time.Duration(delayBetweenEmptyIterations*iteration) * time.Millisecond
}

// список всех известных хранилищ
type taskRepositoryStore struct {
	mx                 sync.RWMutex
	store              map[string]*taskRepository
	makeTaskRepository func(module, group string) *taskRepository
}

// создает новое хранилище для репозиториев задач
func makeTaskRepositoryStore(isolation *Isolation.Isolation) *taskRepositoryStore {

	trStore := &taskRepositoryStore{
		mx:    sync.RWMutex{},
		store: map[string]*taskRepository{},
	}

	trStore.makeTaskRepository = func(module, group string) *taskRepository {
		return makeTaskRepository(isolation, trStore, module, group)
	}

	return trStore
}

// пытается получить репозиторий задач
func (trStore *taskRepositoryStore) getTaskRepository(module, group string) *taskRepository {

	trStore.mx.RLock()
	defer trStore.mx.RUnlock()

	// генерируем уникальный ключ для репозитория задач
	uniq := fmt.Sprintf("%s:%s", module, group)

	// пытаемся получить из хранилища
	if tRepository, exists := trStore.store[uniq]; exists {
		return tRepository
	}

	return nil
}
