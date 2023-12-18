package methods_rating

import (
	"context"
	"database/sql"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/includes/type/db/company_data"
	"go_company/api/includes/type/db/company_data/rating_day_list"
	"go_company/api/includes/type/db/company_data/rating_member_day_list"
	"go_company/api/includes/type/db/company_data/rating_member_hour_list"
	"go_company/api/includes/type/define"
	GlobalIsolation "go_company/api/includes/type/global_isolation"
	"go_company/api/includes/type/noticebot"
	"go_company/api/includes/type/rating"
	"go_company/api/includes/type/rating_utils"
	storageMain "go_company/api/includes/type/storage"
	"time"
)

// дампим анилитику в базу
func DumpToActiveTable(ctx context.Context, storage *storageMain.MainStorage, ratingStore *rating.Store, companyDataConn *company_data.DbConn,
	globalIsolation *GlobalIsolation.GlobalIsolation, year int, day int) error {

	cache := ratingStore.GetAndClearStore()

	// если в кэше нет данных то обновляем время и прекращаем выполнение
	if len(cache) < 1 {

		storage.UpdateLastUpdatedAt()
		return nil
	}

	// если хранилище не было инициализировано
	if !ratingStore.IsInit {

		_ = storage.Init(ctx, companyDataConn, year, day)
		ratingStore.IsInit = true
	}

	// получаем список часов для дампа
	hourList := getHourListForDump()

	// дампим аналитику в базу
	for _, hour := range hourList {

		err := dumpToActiveTable(ctx, storage, companyDataConn, globalIsolation, year, day, hour, cache)
		if err != nil {
			return err
		}
	}

	return nil
}

// получаем список часов для дампа
func getHourListForDump() []int64 {

	// получаем время начала часа
	hour := HourStart()

	var hourList []int64
	hourList = append(hourList, hour)

	// если первые минуты, то пробуем добавить данные в таблицу также и за прошлый час
	currentMinute := time.Now().Minute()
	if currentMinute <= define.ObserverIntervalMinutes {

		// получаем время за прошлый час
		prevHour := hour - int64(define.HOUR1)

		// добавляем к списку
		hourList = append([]int64{prevHour}, hourList...)
	}

	return hourList
}

// дампим анилитику в базу
func dumpToActiveTable(ctx context.Context, storage *storageMain.MainStorage, companyDataConn *company_data.DbConn, globalIsolation *GlobalIsolation.GlobalIsolation,
	year int, day int, hour int64, cache map[int64]map[int64]map[int64]int) error {

	// если кэш пуст
	if len(cache[hour]) < 1 {
		return nil
	}

	// сливаем инфу в основной кэш
	changedUserIdList := storage.IncToMainStore(year, day, cache[hour])

	// инициализируем дату
	countGroupedByEventId := storage.GetCountGroupedByEventId()
	err := addToDb(ctx, storage, companyDataConn, year, day, countGroupedByEventId, changedUserIdList)
	if err != nil {

		log.Errorf(err.Error())
		return err
	}

	// пишем в почасовую таблицу в базе
	err = addToEventHour(ctx, companyDataConn, globalIsolation, hour, cache[hour])
	if err != nil {

		log.Errorf(err.Error())
		return err
	}

	return nil
}

// переливаем все данные в бд
func addToDb(ctx context.Context, storage *storageMain.MainStorage, companyDataConn *company_data.DbConn, year int,
	day int, countGroupedByEventId map[int64]int, changedUserIdList map[int64]int) error {

	data := initRatingDayData(countGroupedByEventId)

	transaction, err := companyDataConn.BeginTransaction()
	if err != nil {

		log.Errorf(err.Error())
		return err
	}

	// обновляем основную таблицу
	err = dbRatingDayList.InsertOrUpdate(ctx, transaction, year, day, countGroupedByEventId[define.GeneralCounterId], data)
	if err != nil {

		log.Errorf(err.Error())
		return transaction.Rollback()
	}

	dataByUserId, err := updateUserList(ctx, storage, transaction, year, day, changedUserIdList)
	if err != nil {
		return transaction.Rollback()
	}
	if len(dataByUserId) > 0 {

		err = dbRatingMemberDayList.InsertArray(ctx, transaction, year, day, dataByUserId)
		if err != nil {

			log.Errorf(err.Error())
			return transaction.Rollback()
		}
	}
	company_data.CommitTransaction(transaction)
	return nil
}

// обновляем user_list
func updateUserList(ctx context.Context, storage *storageMain.MainStorage, transaction *sql.Tx, year int, day int,
	changedUserIdList map[int64]int) (map[int64]interface{}, error) {

	dataByUserId := make(map[int64]interface{})

	// обновляем для каждого пользователя
	for userId := range changedUserIdList {

		// получаем количество ивентов, сгруппированных по ивенту
		countGroupedByEventId := storage.GetCountByUserIdGroupedByEventId(userId)

		// инициализируем новую data
		data := initUserRatingDayData(countGroupedByEventId)

		// вставляем в базу
		count, err := dbRatingMemberDayList.UpdateOne(ctx, transaction, year, day, userId, data)

		if err != nil {
			return nil, err
		}

		if count < 1 {
			dataByUserId[userId] = data
		}
	}
	return dataByUserId, nil
}

// инициализируем новую data для dbRatingMemberDayList
func initUserRatingDayData(countGroupedByEventId map[int64]int) dbRatingMemberDayList.Data {

	return dbRatingMemberDayList.InitData(
		countGroupedByEventId[define.GeneralCounterId],
		countGroupedByEventId[define.ConversationMessageCounterId],
		countGroupedByEventId[define.ThreadMessageCounterId],
		countGroupedByEventId[define.FileCounterId],
		countGroupedByEventId[define.ReactionCounterId],
		countGroupedByEventId[define.VoiceCounterId],
		countGroupedByEventId[define.CallCounterId],
		countGroupedByEventId[define.RespectCounterId],
		countGroupedByEventId[define.ExactingnessCounterId])
}

// пишем статистику в базу по часам
func addToEventHour(ctx context.Context, companyDataConn *company_data.DbConn, isolation *GlobalIsolation.GlobalIsolation,
	hour int64, incToStore map[int64]map[int64]int) error {

	// создаем хранилище под эвенты
	eventStorage := getEventStorage(incToStore)

	// обновляем для каждого пользователя
	for userId, v1 := range eventStorage {

		// формируем дату
		data := initDataForEventHoursList(v1)

		// получаем запись из базы если есть
		eventHoursRow, isExist, err := dbEventHoursList.GetOne(ctx, companyDataConn, hour, userId)
		if err != nil {

			_ = sendMessageToChatIfError(isolation)
			return err
		}

		// если записи нет в базе, создаем
		if !isExist {

			err := insertEventHour(companyDataConn, hour, userId, data)
			if err != nil {
				return err
			}
			continue
		}

		// складываем значения из базы и значения из кеша
		data = incData(data, eventHoursRow)

		// обновляем запись
		err = updateEventHour(ctx, companyDataConn, hour, userId, data)
		if err != nil {
			return err
		}
	}

	return nil
}

// формируем хранилище эвентов по пользователям
func getEventStorage(incToStore map[int64]map[int64]int) map[int64]map[int64]int {

	// создаем хранилище под эвенты
	eventStorage := make(map[int64]map[int64]int)

	// идем по всем ивентам которые надо добавить
	for eventId, v1 := range incToStore {

		// добавляем для каждого пользователя его эвенты
		for userId, value := range v1 {

			if _, isExist := eventStorage[userId]; !isExist {
				eventStorage[userId] = make(map[int64]int)
			}

			eventStorage[userId][eventId] = value
		}
	}

	return eventStorage
}

// создаем новую запись
func insertEventHour(companyDataConn *company_data.DbConn, hour int64, userId int64, data dbEventHoursList.Data) error {

	// начинаем транзацию
	transaction, err := companyDataConn.BeginTransaction()
	if err != nil {
		return err
	}

	// вставляем в базу
	err = dbEventHoursList.InsertOrUpdate(transaction, hour, userId, data)
	if err != nil {
		return transaction.Rollback()
	}

	company_data.CommitTransaction(transaction)

	return nil
}

// обновляем поле data записи
func incData(data dbEventHoursList.Data, eventHoursRow *dbEventHoursList.UserEventHourRow) dbEventHoursList.Data {

	data.GeneralCount += eventHoursRow.Data.GeneralCount
	data.ConversationMessageCount += eventHoursRow.Data.ConversationMessageCount
	data.ThreadMessageCount += eventHoursRow.Data.ThreadMessageCount
	data.FileCount += eventHoursRow.Data.FileCount
	data.ReactionCount += eventHoursRow.Data.ReactionCount
	data.VoiceCount += eventHoursRow.Data.VoiceCount
	data.CallCount += eventHoursRow.Data.CallCount
	data.RespectCount += eventHoursRow.Data.RespectCount
	data.ExactingnessCount += eventHoursRow.Data.ExactingnessCount

	return data
}

// обновляем запись
func updateEventHour(ctx context.Context, companyDataConn *company_data.DbConn, hour int64, userId int64, data dbEventHoursList.Data) error {

	transaction, err := companyDataConn.BeginTransaction()
	if err != nil {
		return err
	}

	// обновляем запись в базу
	err = dbEventHoursList.UpdateOne(ctx, transaction, hour, userId, data)
	if err != nil {
		return transaction.Rollback()
	}

	company_data.CommitTransaction(transaction)

	return nil
}

// отсылаем сообщение в чат
func sendMessageToChatIfError(isolation *GlobalIsolation.GlobalIsolation) error {

	err := noticebot.SendGroup(isolation)
	if err != nil {
		return err
	}

	return nil
}

// декрементим в таблицах рейтинга
// @long - много действий с использованием транзакции
func DecInActiveTable(ctx context.Context, storage *storageMain.MainStorage, companyDataConn *company_data.DbConn, userId int64, eventId int64,
	createdAt int64, value int) error {

	// получаем год и день для декремента
	year, day, hour := getTimeDataForDec(createdAt)

	// декрементим в хранилище
	storage.Dec(userId, eventId, value)

	// получаем необходимые данные хранилища
	countGroupedByEventId := storage.GetCountGroupedByEventId()
	countGroupedByUserIdAndEventId := storage.GetCountByUserIdGroupedByEventId(userId)
	changedUserIdList := storage.GetAllUserList()

	// обновляем дату в таблице rating_day_list
	data := initRatingDayData(countGroupedByEventId)

	transaction, err := companyDataConn.BeginTransaction()
	if err != nil {

		log.Errorf(err.Error())
		return err
	}

	// обновляем таблицу rating_day_list
	err = dbRatingDayList.InsertOrUpdate(ctx, transaction, year, day, countGroupedByEventId[define.GeneralCounterId], data)
	if err != nil {

		log.Errorf(err.Error())
		return transaction.Rollback()
	}

	_, err = updateUserList(ctx, storage, transaction, year, day, changedUserIdList)
	if err != nil {
		return transaction.Rollback()
	}

	dataJson := initDataForEventHoursList(countGroupedByUserIdAndEventId)

	err = dbEventHoursList.UpdateOne(ctx, transaction, hour, userId, dataJson)
	if err != nil {
		return transaction.Rollback()
	}

	company_data.CommitTransaction(transaction)

	return nil
}

// получаем время для декремента
func getTimeDataForDec(createdAt int64) (int, int, int64) {

	timeObj := time.Unix(createdAt, 0)
	hour := rating_utils.HourStartByTimeAt(createdAt)
	year := timeObj.Year()
	day := functions.GetDaysCountByYear(year)

	return year, day, hour
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// функция для получения текущего часа
func HourStart() int64 {

	timeObj := time.Now()

	year := timeObj.Year()
	month := timeObj.Month()
	day := timeObj.Day()
	hour := timeObj.Hour()
	location := timeObj.Location()

	hourStart := time.Date(year, month, day, hour, 0, 0, 0, location).Unix()

	return hourStart
}

// формируем дату для dbEventHoursList
func initDataForEventHoursList(countByEventId map[int64]int) dbEventHoursList.Data {

	return dbEventHoursList.InitData(
		countByEventId[define.GeneralCounterId],
		countByEventId[define.ConversationMessageCounterId],
		countByEventId[define.ThreadMessageCounterId],
		countByEventId[define.FileCounterId],
		countByEventId[define.ReactionCounterId],
		countByEventId[define.VoiceCounterId],
		countByEventId[define.CallCounterId],
		countByEventId[define.RespectCounterId],
		countByEventId[define.ExactingnessCounterId])
}

// инициализируем data для dbRatingDayList
func initRatingDayData(countGroupedByEventId map[int64]int) dbRatingDayList.Data {

	return dbRatingDayList.InitData(
		countGroupedByEventId[define.ConversationMessageCounterId],
		countGroupedByEventId[define.ThreadMessageCounterId],
		countGroupedByEventId[define.FileCounterId],
		countGroupedByEventId[define.ReactionCounterId],
		countGroupedByEventId[define.VoiceCounterId],
		countGroupedByEventId[define.CallCounterId],
		countGroupedByEventId[define.RespectCounterId],
		countGroupedByEventId[define.ExactingnessCounterId])
}
