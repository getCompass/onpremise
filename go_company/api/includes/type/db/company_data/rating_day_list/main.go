package dbRatingDayList

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей рейтинг по дням
// -------------------------------------------------------

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_company/api/includes/type/db/company_data"
	"go_company/api/includes/type/rating_utils"
	"time"
)

const tableName = "rating_day_list"

// структура описывающая одну запись
type RatingDayRow struct {
	Day          int
	GeneralCount int
	CreatedAt    int64
	UpdatedAt    int64
	Data         Data
}

// структура описывающая таблицу без данных
type RatingDayRowWithoutData struct {
	Day          int
	GeneralCount int
	CreatedAt    int64
	UpdatedAt    int64
}

type insertRow struct {
	DayStart     int64           `sqlname:"day_start"`
	GeneralCount int             `sqlname:"general_count"`
	UpdatedAt    int64           `sqlname:"updated_at"`
	Data         json.RawMessage `sqlname:"data"`
}

// добавить или обновить запись
func InsertOrUpdate(ctx context.Context, transactionItem *sql.Tx, year int, day int, generalCount int, data interface{}) error {

	// запаковываем data в JSON
	dataJson, err := go_base_frame.Json.Marshal(data)
	if err != nil {
		return fmt.Errorf("[rating_day_list.go] Не удалось запаковать в JSON: %+v\r\nОшибка: %v", data, err)
	}

	dayStart := rating_utils.GetDayStartByYearAndDay(year, day)

	insert := insertRow{
		DayStart:     dayStart,
		GeneralCount: generalCount,
		UpdatedAt:    functions.GetCurrentTimeStamp(),
		Data:         dataJson,
	}

	query, values := mysql.FormatInsertOrUpdate(tableName, insert)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err = transactionItem.ExecContext(queryCtx, query, values...)
	return err
}

// получаем записи за интервал из базы
// @long - длинные запросы
func GetByInterval(ctx context.Context, dbConn *company_data.DbConn, fromDateAt int, toDateAt int) ([]*RatingDayRow, error) {

	// формируем параметры для запрашиваемого периода
	yearListParams := rating_utils.InitYearParamsList(fromDateAt, toDateAt)

	currentYear := time.Now().Year()
	allRowList := make([]*RatingDayRow, 0)
	for year, params := range yearListParams {

		// если данные есть
		if year <= currentYear {

			startDayStart := rating_utils.GetDayStartByYearAndDay(year, params["startDay"])
			endDayStart := rating_utils.GetDayStartByYearAndDay(year, params["endDay"])

			var err error
			allRowList, err = getYearByInterval(ctx, dbConn, allRowList, startDayStart, endDayStart)

			if err != nil {
				return nil, err
			}
		}
	}

	return allRowList, nil
}

// получаем данные без даты по году
func getYearByInterval(ctx context.Context, dbConn *company_data.DbConn, allRowList []*RatingDayRow, startDayStart int64, endDayStart int64) ([]*RatingDayRow, error) {

	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("SELECT `day_start`, `general_count`, `created_at`, `updated_at`, `data` FROM `%s` WHERE `day_start` >= ? AND `day_start` <= ? ORDER BY `day_start` ASC LIMIT %d", tableName, endDayStart-startDayStart+1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)

	results, err := dbConn.Conn.QueryContext(queryCtx, query, startDayStart, endDayStart)
	defer func() { _ = results.Close(); defer cancel() }()

	if err != nil {

		log.Errorf(err.Error())
		return nil, err
	}

	for results.Next() {

		var row RatingDayRow
		var dayStart int64
		var dataJson []byte

		err = results.Scan(&dayStart, &row.GeneralCount, &row.CreatedAt, &row.UpdatedAt, &dataJson)
		if err != nil {

			log.Errorf(err.Error())
			return nil, err
		}

		data := Data{}

		err := go_base_frame.Json.Unmarshal(dataJson, &data)
		if err != nil {
			return nil, err
		}
		row.Data = data

		tm := time.Unix(dayStart, 0)
		row.Day = functions.GetDaysCountByTimestamp(dayStart, tm.Year())

		allRowList = append(allRowList, &row)
	}

	return allRowList, nil
}

// получаем строки за несколько дней
// @long - проходимся по годам
func GetAllByIntervalWithoutData(ctx context.Context, conn *company_data.DbConn, fromDateAt int, toDateAt int) ([]*RatingDayRowWithoutData, error) {

	// формируем параметры для запрашиваемой периода
	yearListParams := rating_utils.InitYearParamsList(fromDateAt, toDateAt)

	currentYear := time.Now().Year()

	allRowList := make([]*RatingDayRowWithoutData, 0)
	for year, params := range yearListParams {

		// если данные есть
		if year <= currentYear {

			var err error
			allRowList, err = getYearByIntervalWithoutData(ctx, conn, allRowList, year, params["startDay"], params["endDay"])
			if err != nil {
				return nil, err
			}
		}
	}

	return allRowList, nil
}

// получаем данные без даты по году
func getYearByIntervalWithoutData(ctx context.Context, dbConn *company_data.DbConn, allRowList []*RatingDayRowWithoutData, year int, startDay int, endDay int) ([]*RatingDayRowWithoutData, error) {

	startDayStart := rating_utils.GetDayStartByYearAndDay(year, startDay)
	endDayStart := rating_utils.GetDayStartByYearAndDay(year, endDay)

	// запрос проверен на EXPLAIN (INDEX=PRIMARY)
	query := fmt.Sprintf("SELECT `day_start`, `general_count`, `created_at`, `updated_at` FROM `%s` WHERE `day_start` >= ? AND `day_start` <= ? LIMIT %d", tableName, endDay-startDay+1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)

	results, err := dbConn.Conn.QueryContext(queryCtx, query, startDayStart, endDayStart)
	defer func() { _ = results.Close(); defer cancel() }()

	if err != nil {
		return nil, err
	}

	for results.Next() {

		var row RatingDayRowWithoutData
		var dayStart int64

		err = results.Scan(&dayStart, &row.GeneralCount, &row.CreatedAt, &row.UpdatedAt)
		if err != nil {
			return nil, err
		}

		tm := time.Unix(dayStart, 0)
		row.Day = functions.GetDaysCountByTimestamp(dayStart, tm.Year())

		allRowList = append(allRowList, &row)
	}

	return allRowList, nil
}
