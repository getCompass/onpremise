package CompanySystem

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	Database "go_event/api/includes/type/database"
)

// AsyncTaskRecord структура для чтения из таблицы
type AsyncTaskRecord struct {
	asyncTaskId int64           // идентификатор для задачи
	IsFailed    int             `json:"is_failed,omitempty" sqlname:"is_failed"`       // статус задачи
	TaskType    int             `json:"type,omitempty" sqlname:"task_type"`            // тип задачи
	NeedWorkAt  int64           `json:"need_work_at,omitempty" sqlname:"need_work_at"` // когда задачу нужно взять в работу
	ErrorCount  int             `json:"error_count,omitempty" sqlname:"error_count"`   // количество ошибок
	CreatedAt   int64           `json:"created_at,omitempty" sqlname:"created_at"`     // дата создания задачи
	UpdatedAt   int64           `json:"updated_at,omitempty" sqlname:"updated_at"`     // дата обновления задачи
	UniqueKey   string          `json:"unique_key,omitempty" sqlname:"unique_key"`     // уникальный ключ задачи
	Module      string          `json:"module,omitempty" sqlname:"module"`             // целевой модуль для задачи
	Group       string          `json:"group,omitempty" sqlname:"group"`               // целевой модуль для задачи
	Name        string          `json:"name,omitempty" sqlname:"name"`                 // читаемое название задачи
	Data        json.RawMessage `json:"data,omitempty" sqlname:"data"`                 // данные задачи
}

// IsStored возвращает флаг — сохранена задача или нет
func (r *AsyncTaskRecord) IsStored() bool {
	return r.asyncTaskId != 0
}

// структура хендлера таблицы асинхронных задач
type asyncTaskTable struct {
	tableName string
	fieldList string
}

// AsyncTaskTable основной хендлер таблицы асинхронных задач
var AsyncTaskTable = asyncTaskTable{
	tableName: "async_task",
	fieldList: "`async_task_id`, `is_failed`, `task_type`, `need_work_at`, `error_count`, `created_at`, `updated_at`, `unique_key`, `module`, `group`, `name`, `data`",
}

// GetAll загружает задачи из базы данных
func (t *asyncTaskTable) GetAll(ctx context.Context, connection *Database.Connection, limit, offset int) ([]*AsyncTaskRecord, error) {

	// EXPLAIN INDEX get_by_need_work
	query := fmt.Sprintf("SELECT %s FROM `%s` WHERE `is_failed` = ? LIMIT ? OFFSET ?", t.fieldList, t.tableName)

	fl := make([]interface{}, 0)

	fl = append(fl, 0, limit, offset)

	rows, err := connection.GetAll(ctx, query, fl...)
	if err != nil {
		return nil, err
	}

	return scanManyToAsyncTaskRecord(rows)
}

// Insert вставляет одну запись в репозиторий задач
func (t *asyncTaskTable) Insert(ctx context.Context, connection *Database.Connection, task *AsyncTaskRecord) (*AsyncTaskRecord, error) {

	if task.asyncTaskId != 0 {
		return task, fmt.Errorf("task %s already stored", task.UniqueKey)
	}

	result, err := connection.InsertIgnore(ctx, t.tableName, task)
	if err != nil {
		return task, err
	}

	asyncTaskId, err := result.LastInsertId()
	if err != nil {
		return task, err
	}

	task.asyncTaskId = asyncTaskId
	return task, nil
}

// Update обновляет запись
func (t *asyncTaskTable) Update(ctx context.Context, connection *Database.Connection, task *AsyncTaskRecord) error {

	if task.asyncTaskId == 0 {
		return fmt.Errorf("task %s has zero id", task.UniqueKey)
	}

	// EXPLAIN INDEX PRIMARY
	query := fmt.Sprintf("UPDATE `%s` SET ?? WHERE `async_task_id` = ? LIMIT ?", t.tableName)
	return connection.Update(ctx, query, task, task.asyncTaskId, 1)
}

// Delete удаляет одну запись
func (t *asyncTaskTable) Delete(ctx context.Context, connection *Database.Connection, task *AsyncTaskRecord) error {

	if task.asyncTaskId == 0 {
		return fmt.Errorf("task %s has zero id", task.UniqueKey)
	}

	// EXPLAIN INDEX PRIMARY
	query := fmt.Sprintf("DELETE FROM `%s` WHERE `async_task_id` = ? LIMIT ?", t.tableName)
	return connection.Delete(ctx, query, task.asyncTaskId, 1)
}

// конвертирует данные в понятную структуру
func scanManyToAsyncTaskRecord(rows *sql.Rows) (result []*AsyncTaskRecord, err error) {

	for rows.Next() {

		r := &AsyncTaskRecord{}
		result = append(result, r)

		err = rows.Scan(&r.asyncTaskId, &r.IsFailed, &r.TaskType, &r.NeedWorkAt, &r.ErrorCount, &r.CreatedAt, &r.UpdatedAt, &r.UniqueKey, &r.Module, &r.Group, &r.Name, &r.Data)
		if err != nil {
			return result, err
		}
	}

	return result, err
}
