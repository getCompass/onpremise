package dbRatingMemberDayList

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_company/api/includes/type/db/company_data"
	"go_company/api/includes/type/rating_utils"
	"time"
)

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей рейтинг по пользователям за день
// -------------------------------------------------------

const tableName = "rating_member_day_list"

// структура описывающая таблицу
type UserRatingDayRow struct {
	UserId          int64
	Day             int
	IsDisabledAlias bool
	UpdatedAt       int64
	CreatedAt       int64
	Data            Data
}

// получаем все строки за день
func GetAllForDay(ctx context.Context, dbConn *company_data.DbConn, year int, day int) ([]*UserRatingDayRow, error) {

	dayStart := rating_utils.GetDayStartByYearAndDay(year, day)

	count := 0

	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("SELECT COUNT(*) as `count` FROM `%s` USE INDEX (`day_start`) WHERE `day_start` = ? LIMIT %d", tableName, 1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := dbConn.Conn.QueryRowContext(queryCtx, query, dayStart).Scan(&count)
	if err != nil {

		if err == sql.ErrNoRows {
			return nil, nil
		}

		log.Errorf(err.Error())
		return nil, err
	}

	// запрос проверен на EXPLAIN (INDEX=day)
	query = fmt.Sprintf("SELECT user_id, day_start, is_disabled_alias, created_at, updated_at, data FROM `%s` USE INDEX (`day_start`) WHERE `day_start` = ? LIMIT %d", tableName, count)

	queryCtx, cancel = context.WithTimeout(ctx, mysql.QueryTimeout)

	results, err := dbConn.Conn.QueryContext(queryCtx, query, dayStart)
	if results == nil {

		cancel()
		return nil, fmt.Errorf("no connection to database company_data")
	}
	defer func() { _ = results.Close(); defer cancel() }()

	if err != nil {

		log.Errorf(err.Error())
		return nil, err
	}

	userRatingDayList := make([]*UserRatingDayRow, 0, count)

	for results.Next() {

		var row UserRatingDayRow
		var dayStartTable int64
		var dataJson []byte

		err = results.Scan(&row.UserId, &dayStartTable, &row.IsDisabledAlias, &row.CreatedAt, &row.UpdatedAt, &dataJson)
		if err != nil {
			return nil, err
		}

		tm := time.Unix(dayStartTable, 0)
		row.Day = functions.GetDaysCountByTimestamp(dayStartTable, tm.Year())

		data := Data{}

		err := go_base_frame.Json.Unmarshal(dataJson, &data)
		if err != nil {
			return nil, err
		}
		row.Data = data
		userRatingDayList = append(userRatingDayList, &row)
	}

	return userRatingDayList, nil
}

// получаем строки за период дней
func GetAllForDayWithOffsetByInterval(ctx context.Context, dbConn *company_data.DbConn, fromDateAt int, toDateAt int) (map[int64][]*UserRatingDayRow, []int64, error) {

	// получаем все записи за период
	allList, err := getAllUserDayListByInterval(ctx, dbConn, fromDateAt, toDateAt)
	if err != nil {

		log.Errorf(err.Error())
		return nil, nil, err
	}

	if len(allList) < 1 {
		return nil, nil, nil
	}

	// формируем userRatingDayList за период дней
	userRatingDayList, userList, err := makeUserRatingDayList(allList)
	if err != nil {

		log.Errorf(err.Error())
		return nil, nil, err
	}

	return userRatingDayList, userList, nil
}

// получаем тотал значение рейтинга за период дней
func GetTotalForDayByInterval(ctx context.Context, dbConn *company_data.DbConn, fromDateAt int, toDateAt int) (map[int64]*UserRatingDayRow, []int64, error) {

	userRatingTotal, userList, err := getAllUserDayListForYear(ctx, dbConn, fromDateAt, toDateAt)
	if err != nil {
		return nil, nil, err
	}

	return userRatingTotal, userList, nil
}

// получаем все данные пользовательского рейтинга по дням за период
func getAllUserDayListForYear(ctx context.Context, dbConn *company_data.DbConn, fromDateAt int, toDateAt int) (map[int64]*UserRatingDayRow, []int64, error) {

	// получаем все записи за период
	allList, err := getAllUserDayListByInterval(ctx, dbConn, fromDateAt, toDateAt)
	if err != nil {

		log.Errorf(err.Error())
		return nil, nil, err
	}

	// формируем userRatingTotal за период дней
	userRatingTotal, userList, err := makeUserRatingTotal(allList)
	if err != nil {
		return nil, nil, err
	}
	return userRatingTotal, userList, nil
}

// получаем все записи за период из таблицы user_rating_day_list
func getAllUserDayListByInterval(ctx context.Context, dbConn *company_data.DbConn, fromDateAt int, toDateAt int) ([]*UserRatingDayRow, error) {

	// циклично достаем все записи за выбранный период
	maxCount := 1000
	offset := 0
	count := 0
	userRatingDayList := make([]*UserRatingDayRow, 0, maxCount)
	var err error

	for {

		userRatingDayList, count, err = getCountUserDayListByInterval(ctx, dbConn, userRatingDayList, fromDateAt, toDateAt, maxCount, offset)
		if err != nil {

			log.Errorf(err.Error())
			return nil, err
		}

		// если новых записей больше нет, то останавливаем цикл
		if count < maxCount {
			break
		}

		// увеличиваем смещение для пагинации
		offset += count
	}

	return userRatingDayList, nil
}

// получаем заданное количество записей из бд
func getCountUserDayListByInterval(ctx context.Context, dbConn *company_data.DbConn, userRatingDayList []*UserRatingDayRow, fromDateAt int, toDateAt int, maxCount int,
	offset int) ([]*UserRatingDayRow, int, error) {

	// выполняем запрос для получения записей
	query := fmt.Sprintf("SELECT user_id, day_start, is_disabled_alias, created_at, updated_at, data FROM `%s` FORCE INDEX (`day_start_is_disabled_alias`) WHERE `day_start` >= ? AND `day_start` <= ? ORDER BY `day_start`, `is_disabled_alias` ASC LIMIT %d OFFSET %d", tableName, maxCount, offset)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	results, err := dbConn.Conn.QueryContext(queryCtx, query, fromDateAt, toDateAt)

	if err != nil {
		return nil, 0, err
	}

	defer results.Close()

	count := 0
	for results.Next() {

		var row UserRatingDayRow
		var dayStart int64
		var dataJson []byte

		err = results.Scan(&row.UserId, &dayStart, &row.IsDisabledAlias, &row.CreatedAt, &row.UpdatedAt, &dataJson)
		if err != nil {

			log.Errorf("%v", err)
			return nil, 0, err
		}

		tm := time.Unix(dayStart, 0)
		row.Day = functions.GetDaysCountByTimestamp(dayStart, tm.Year())

		data := Data{}

		err = go_base_frame.Json.Unmarshal(dataJson, &data)
		if err != nil {
			return nil, 0, err
		}
		row.Data = data
		userRatingDayList = append(userRatingDayList, &row)
		count++
	}

	return userRatingDayList, count, nil
}

// формируем userRatingDayList
func makeUserRatingDayList(list []*UserRatingDayRow) (map[int64][]*UserRatingDayRow, []int64, error) {

	userRatingDayListByUser := make(map[int64][]*UserRatingDayRow)

	var userList = make(map[int64]bool, 0)
	for _, userRatingDayRow := range list {

		userId := userRatingDayRow.UserId

		// собираем список unique id пользователей
		// (потребуется в дальнейшем, чтобы отдавать пользователей в правильном порядке)
		userList[userId] = true

		userRatingDayListByUser[userId] = append(userRatingDayListByUser[userId], userRatingDayRow)
	}

	return userRatingDayListByUser, functions.GetInt64MapKeyList(userList), nil
}

// формируем userRatingTotal
// @long - большой список ивентов
func makeUserRatingTotal(list []*UserRatingDayRow) (map[int64]*UserRatingDayRow, []int64, error) {

	userRatingTotalByUser := make(map[int64]*UserRatingDayRow)

	var userList = make(map[int64]bool, 0)
	for _, userRatingDayRow := range list {

		userId := userRatingDayRow.UserId

		// собираем список unique id пользователей
		// (потребуется в дальнейшем, чтобы отдавать пользователей в правильном порядке)
		userList[userId] = true

		userRatingTotalRow, isExist := userRatingTotalByUser[userId]

		// если такой записи еще не встречалось в собранном списке userRatingTotalByUser
		if !isExist {
			userRatingTotalRow = &UserRatingDayRow{}
		}

		// устанавливаем флаг был ли заблочен пользователь
		userRatingTotalRow.IsDisabledAlias = userRatingDayRow.IsDisabledAlias || userRatingTotalRow.IsDisabledAlias

		// складываем значения ивентов для пользователя
		userRatingTotalRow.Data.GeneralCount += userRatingDayRow.Data.GeneralCount
		userRatingTotalRow.Data.ConversationMessageCount += userRatingDayRow.Data.ConversationMessageCount
		userRatingTotalRow.Data.ThreadMessageCount += userRatingDayRow.Data.ThreadMessageCount
		userRatingTotalRow.Data.FileCount += userRatingDayRow.Data.FileCount
		userRatingTotalRow.Data.ReactionCount += userRatingDayRow.Data.ReactionCount
		userRatingTotalRow.Data.VoiceCount += userRatingDayRow.Data.VoiceCount
		userRatingTotalRow.Data.CallCount += userRatingDayRow.Data.CallCount
		userRatingTotalRow.Data.RespectCount += userRatingDayRow.Data.RespectCount
		userRatingTotalRow.Data.ExactingnessCount += userRatingDayRow.Data.ExactingnessCount

		// получаем время последнего обновления
		if userRatingTotalRow.UpdatedAt < userRatingDayRow.UpdatedAt {
			userRatingTotalRow.UpdatedAt = userRatingDayRow.UpdatedAt
		}

		// устанавливаем запись обратно в список к остальным
		userRatingTotalByUser[userId] = userRatingTotalRow
	}

	return userRatingTotalByUser, functions.GetInt64MapKeyList(userList), nil
}

// обновить запись
func UpdateOne(ctx context.Context, transactionItem *sql.Tx, year int, day int, userId int64, data interface{}) (int64, error) {

	// запаковываем data в JSON
	dataJson, err := go_base_frame.Json.Marshal(data)
	if err != nil {
		return 0, fmt.Errorf("[rating_day_list.go] Не удалось запаковать в JSON: %+v\r\nОшибка: %v", data, err)
	}

	dayStart := rating_utils.GetDayStartByYearAndDay(year, day)

	// совершаем запрос
	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("UPDATE `%s` SET `updated_at` = ?, `data` = ? WHERE `day_start` = ? AND `user_id` = ? LIMIT %d", tableName, 1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	result, err := transactionItem.ExecContext(queryCtx, query, functions.GetCurrentTimeStamp(), dataJson, dayStart, userId)
	if err != nil {
		return 0, err
	}
	return result.RowsAffected()
}

// вставляем массив записей
func InsertArray(ctx context.Context, transactionItem *sql.Tx, year int, day int, dataByUserId map[int64]interface{}) error {

	var insertList [][]interface{}
	for userId, dataJson := range dataByUserId {

		dayStart := rating_utils.GetDayStartByYearAndDay(year, day)

		// запаковываем data в JSON
		dataJson, err := go_base_frame.Json.Marshal(dataJson)
		if err != nil {
			return fmt.Errorf("[rating_day_list.go] Не удалось запаковать в JSON: %+v\r\nОшибка: %v", dataByUserId[userId], err)
		}

		insertList = append(insertList, []interface{}{
			dayStart, userId, functions.GetCurrentTimeStamp(), dataJson, functions.GetCurrentTimeStamp(),
		})
	}

	query, values := mysql.InsertArray(tableName, []string{"day_start", "user_id", "created_at", "data", "updated_at"}, insertList)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err := transactionItem.ExecContext(queryCtx, query, values...)

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
	return err
}

// получаем значения поля is_disabled_alias
func GetIsDisabledStatus(dbConn *company_data.DbConn, userId int64) (int, bool, error) {

	var isDisabledAlias int

	// совершаем запрос
	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("SELECT is_disabled_alias FROM `%s` WHERE `user_id` = ? LIMIT 1", tableName)
	err := dbConn.Conn.QueryRow(query, userId).Scan(&isDisabledAlias)
	if err != nil {

		if err == sql.ErrNoRows {
			return 0, false, nil
		}
		return 0, false, err
	}

	return isDisabledAlias, true, nil
}
