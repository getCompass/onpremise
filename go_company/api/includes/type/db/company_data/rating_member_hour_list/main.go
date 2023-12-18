package dbEventHoursList

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_company/api/includes/type/db/company_data"
)

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей рейтинг по пользователям по часам
// -------------------------------------------------------

const tableName = "rating_member_hour_list"

// структура описывающая таблицу
type UserEventHourRow struct {
	UserId    int64
	UpdatedAt int64
	CreatedAt int64
	Data      Data
}

// метод, для получения одной записи
func GetOne(ctx context.Context, dbConn *company_data.DbConn, hourStart int64, userId int64) (*UserEventHourRow, bool, error) {

	row := &UserEventHourRow{}
	var dataJson []byte

	// получаем одну запись, по времени начала часа и user_id пользователя
	query := fmt.Sprintf("SELECT user_id, hour_start, updated_at, data FROM `%s` WHERE `hour_start` = ? AND `user_id` = ? LIMIT %d", tableName, 1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := dbConn.Conn.QueryRowContext(queryCtx, query, hourStart, userId).Scan(&row.UserId, &row.CreatedAt, &row.UpdatedAt, &dataJson)
	if err != nil {

		if err == sql.ErrNoRows {
			return nil, false, nil
		}

		return nil, false, err
	}

	// вытаскиваем data из базы
	data := Data{}
	err = go_base_frame.Json.Unmarshal(dataJson, &data)
	if err != nil {
		return nil, false, err
	}

	row.Data = data

	return row, true, nil
}

// метод, создания новой записи
func InsertOrUpdate(transactionItem *sql.Tx, hourStart int64, userId int64, data interface{}) error {

	// запаковываем reactionList в JSON
	dataJson, err := go_base_frame.Json.Marshal(data)
	if err != nil {
		return fmt.Errorf("[event_hour_list.go] Не удалось запаковать в JSON: %+v\r\nОшибка: %v", data, err)
	}

	// формируем массив вставки
	insert := make(map[string]interface{})
	insert["user_id"] = userId
	insert["hour_start"] = hourStart
	insert["updated_at"] = functions.GetCurrentTimeStamp()
	insert["data"] = dataJson

	query, values := mysql.FormatInsertOrUpdate(tableName, insert)
	_, err = transactionItem.Exec(query, values...)
	return err
}

// метод, для обновления записи
func UpdateOne(ctx context.Context, transactionItem *sql.Tx, hourStart int64, userId int64, data interface{}) error {

	// запаковываем reactionList в JSON
	dataJson, err := go_base_frame.Json.Marshal(data)
	if err != nil {
		return fmt.Errorf("[event_hour_list.go] Не удалось запаковать в JSON: %+v\r\nОшибка: %v", data, err)
	}

	// совершаем запрос
	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("UPDATE `%s` SET "+
		"`updated_at` = ?, "+
		"`data` = ? "+
		" WHERE `hour_start` = ? AND `user_id` = ? LIMIT %d", tableName, 1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err = transactionItem.ExecContext(queryCtx, query, functions.GetCurrentTimeStamp(), dataJson, hourStart, userId)

	return err
}

// выставляем поле is_disabled_alias
func SetIsDisabledStatus(ctx context.Context, dbConn *company_data.DbConn, userId int64, isDisabled int) error {

	// совершаем запрос
	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("UPDATE `%s` SET `updated_at` = ?,`is_disabled_alias` = ? WHERE `user_id` = ?", tableName)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err := dbConn.Conn.ExecContext(queryCtx, query, functions.GetCurrentTimeStamp(), isDisabled, userId)
	if err != nil {
		return err
	}

	return nil
}

// получаем значения поля is_disabled_alias
func GetUserStatus(ctx context.Context, dbConn *company_data.DbConn, userId int64) (int, bool, error) {

	var isDisabledAlias int

	// совершаем запрос
	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("SELECT is_disabled_alias FROM `%s` WHERE `user_id` = ? LIMIT 1", tableName)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := dbConn.Conn.QueryRowContext(queryCtx, query, userId).Scan(&isDisabledAlias)
	if err != nil {

		if err == sql.ErrNoRows {
			return 0, false, nil
		}

		return 0, false, err
	}

	return isDisabledAlias, true, nil
}
