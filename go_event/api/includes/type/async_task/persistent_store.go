package AsyncTask

import (
	"errors"
	Database "go_event/api/includes/type/database"
	CompanySystem "go_event/api/includes/type/database/company_system"
	Isolation "go_event/api/includes/type/isolation"
)

const rowLimit = 1000 // число строк для одного запроса

// сохраняет задачу в базу
func createTask(isolation *Isolation.Isolation, task *AsyncTask) error {

	if task.record.IsStored() {
		return nil
	}

	conn := Database.LeaseSystemConnection(isolation)
	if conn == nil {
		return errors.New("there is no connection to database")
	}

	record, err := CompanySystem.AsyncTaskTable.Insert(isolation.GetContext(), conn, task.record)
	if err != nil {
		return err
	}

	task.record = record
	return nil
}

// обновляет данные задачи
func updateTask(isolation *Isolation.Isolation, task *AsyncTask) error {

	if !task.record.IsStored() {
		return nil
	}

	conn := Database.LeaseSystemConnection(isolation)
	if conn == nil {
		return errors.New("there is no connection to database")
	}

	return CompanySystem.AsyncTaskTable.Update(isolation.GetContext(), conn, task.record)
}

// завершает задачу
func completeTask(isolation *Isolation.Isolation, task *AsyncTask) error {

	if !task.record.IsStored() {
		return nil
	}

	conn := Database.LeaseSystemConnection(isolation)
	if conn == nil {
		return errors.New("there is no connection to database")
	}

	return CompanySystem.AsyncTaskTable.Delete(isolation.GetContext(), conn, task.record)
}

// загружает задачи из базы
// @long внутри большая структура
func loadTasks(isolation *Isolation.Isolation) error {

	conn := Database.LeaseSystemConnection(isolation)
	if conn == nil {
		return errors.New("there is no connection to database")
	}

	offset := 0

	for {

		// получаем задачи из базы
		recordList, err := CompanySystem.AsyncTaskTable.GetAll(isolation.GetContext(), conn, rowLimit, offset)
		if err != nil {
			return err
		}

		for _, record := range recordList {
			_ = PushToDiscrete(isolation, makeRawAsyncTaskFromRecord(record))
		}

		if len(recordList) < rowLimit {
			break
		}

		offset = offset + rowLimit
	}

	return nil
}
