package methods_rating

import (
	"context"
	pb "github.com/getCompassUtils/company_protobuf_schemes/go/company"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	structGeneral "go_company/api/includes/struct/general"
	structUser "go_company/api/includes/struct/user"
	"go_company/api/includes/type/db/company_data"
	"go_company/api/includes/type/db/company_data/rating_day_list"
	"go_company/api/includes/type/db/company_data/rating_member_day_list"
	"go_company/api/includes/type/db/company_data/rating_member_hour_list"
	"go_company/api/includes/type/define"
	GlobalIsolation "go_company/api/includes/type/global_isolation"
	"go_company/api/includes/type/rating"
	storageMain "go_company/api/includes/type/storage"
	"google.golang.org/grpc/status"
	"sort"
	"strings"
	"time"
)

// дозволенные ивенты для декремента
var allowEventIdForDecrement = map[int64]bool{
	define.RespectCounterId:      true,
	define.ExactingnessCounterId: true,
}

// инкрементим ивент для пользователя
func Inc(store *rating.Store, Event string, UserId int64, Inc int) error {

	// если не нашли такой ивент
	eventId, exist := define.EventCountAliasId[Event]
	if !exist || eventId == define.GeneralCounterId {
		return status.Error(400, "getter unknown event")
	}

	// добавляем запись в основное хранилище
	hour := HourStart()
	store.StorageInc(hour, UserId, eventId, Inc)

	return nil
}

// декрементим ивент для пользователя
func Dec(ctx context.Context, storage *storageMain.MainStorage, store *rating.Store, companyDataConn *company_data.DbConn,
	event string, userId int64, createdAt int64, value int) error {

	// проверяем, что такой тип ивента существует
	eventId, exist := define.EventCountAliasId[event]
	if !exist || eventId == define.GeneralCounterId {
		return status.Error(400, "getter unknown event")
	}

	// проверяем что тип ивента позволяет его декрементить
	if _, isAllow := allowEventIdForDecrement[eventId]; !isAllow {
		return status.Error(400, "not allow for decrement")
	}

	// пробуем декрементить данные в кэше
	isSuccessDec := store.StorageDec(userId, eventId, createdAt, value)

	// если удалось декрементнуть в кэше, то в таблицы не лезем
	if isSuccessDec {

		log.Info("do decrement event in ratingStore cache")
		return nil
	}

	// декрементим рейтинг в таблицах рейтинга
	err := DecInActiveTable(ctx, storage, companyDataConn, userId, eventId, createdAt, value)
	if err != nil {
		return status.Error(400, "could not decrement in table")
	}

	return nil
}

// получаем рейтинг
func Get(ctx context.Context, mainStorage *storageMain.MainStorage, companyDataConn *company_data.DbConn, event string,
	fromDateAt int, toDateAt int, topListOffset int, topListCount int) (structGeneral.Rating, *pb.RatingGetResponseStruct, error) {

	// получаем id эвента и выполняем проверки
	eventId, exist := define.EventCountAliasId[event]
	if !exist || fromDateAt < 0 || toDateAt < 0 || fromDateAt > toDateAt || topListOffset < 0 || topListCount < 1 {
		return structGeneral.Rating{}, &pb.RatingGetResponseStruct{}, status.Error(400, "getter bad params")
	}

	response, responseGrpc := initResponseForGet(mainStorage)

	// достаем из бд
	return getFromDbByInterval(ctx, companyDataConn, response, responseGrpc, fromDateAt, toDateAt, topListOffset, topListCount, eventId)
}

// инициируем ответ для запроса methods_rating.Get
func initResponseForGet(mainStorage *storageMain.MainStorage) (structGeneral.Rating, *pb.RatingGetResponseStruct) {

	// формируем структуру ответа
	response := structGeneral.Rating{
		UpdatedAt: mainStorage.GetLastUpdatedAt(),
		Count:     0,
		TopList:   make([]structGeneral.TopItem, 0),
		HasNext:   0,
	}

	responseGrpc := &pb.RatingGetResponseStruct{
		UpdatedAt: mainStorage.GetLastUpdatedAt(),
		Count:     0,
		TopList:   make([]*pb.TopItem, 0),
		HasNext:   0,
	}

	return response, responseGrpc
}

// получить рейтинг для пользователя
func GetByUserId(ctx context.Context, companyDataConn *company_data.DbConn, userRatingStore *rating.UserRatingByDaysStore, userId int64, year int,
	fromDateAt []int64, toDateAt []int64, isFromCache []int64) ([]structUser.Rating, *pb.RatingGetByUserIdListResponseStruct, error) {

	// создаем пустой массив рейтингов длиной в переданное количество периодов
	userRatingList := make([]structUser.Rating, len(fromDateAt))
	userRatingGrpcList := make([]*pb.RatingGetByUserIdResponseStruct, len(fromDateAt))

	for index := range fromDateAt {

		// получаем id эвента и выполняем проверки
		if year < 2000 || fromDateAt[index] < 0 || toDateAt[index] < 0 || userId < 1 || fromDateAt[index] > toDateAt[index] {
			return []structUser.Rating{}, nil, status.Error(400, "getter bad params")
		}

		// получаем тотал значение рейтинга за период для пользователя
		preparedUserRatingDayRow, userGeneralEventPosition, err := getPrepareUserRatingTotal(ctx, companyDataConn, userRatingStore, userId,
			int(fromDateAt[index]), int(toDateAt[index]), int(isFromCache[index]))
		if err != nil {
			return []structUser.Rating{}, nil, status.Error(500, "failed to get data from the database")
		}

		// формируем рейтинг пользователя для ответа
		userRatingList[index] = initUserRatingStruct(userId, year, preparedUserRatingDayRow, userGeneralEventPosition)

		// для grpc делаем тоже самое
		userRatingGrpcList[index] = initUserRatingStructGrpc(userId, year, preparedUserRatingDayRow, userGeneralEventPosition)
	}

	userRatingGrpc := initUserRatingListStructGrpc(userRatingGrpcList)
	return userRatingList, userRatingGrpc, nil
}

// получаем тотал значение рейтинга за период для пользователя
func getPrepareUserRatingTotal(ctx context.Context, companyDataConn *company_data.DbConn, userRatingStore *rating.UserRatingByDaysStore, userId int64,
	fromDateAt int, toDateAt int, isFromCache int) (*dbRatingMemberDayList.UserRatingDayRow, int, error) {

	// получаем ключ для кэша
	key := getKeyForGetByUserIdCache(fromDateAt, toDateAt)
	userTotalRatingByUserId := make(map[int64]*dbRatingMemberDayList.UserRatingDayRow)
	var sortUserList []int64

	// нужно ли доставить данные из кэша
	if isFromCache == 1 {

		// пробуем достать данные из кэша
		userTotalRatingByUserId, sortUserList = getUserListDataForGetByUserIdFromCache(userRatingStore, key)
	}

	// если список рейтинга за период и список пользователей пусты
	if (len(userTotalRatingByUserId) < 1 && len(sortUserList) < 1) || (userTotalRatingByUserId == nil && sortUserList == nil) {

		// достаем записи из базы
		var err error
		userTotalRatingByUserId, sortUserList, err = getUserListDataForGetByUserIdFromDb(ctx, companyDataConn, fromDateAt, toDateAt)
		if err != nil {

			log.Errorf(err.Error())
			return nil, 0, status.Error(500, "failed to get data from the database")
		}

		if userTotalRatingByUserId == nil && sortUserList == nil {
			return nil, 0, nil
		}

		userRatingStore.Mu.Lock()

		// кэшируем собранные данные
		syncData := &rating.UserRatingByDaysStoreItem{
			UserTotalRatingByUserId: userTotalRatingByUserId,
			SortUserList:            sortUserList,
		}
		userRatingStore.Store[key] = syncData
		userRatingStore.Mu.Unlock()
	}

	// подготавливаем рейтинг для нашего пользователя
	// здесь мы должны получить general-позицию пользователя
	userGeneralEventPosition := prepareUserRating(userId, sortUserList)
	return userTotalRatingByUserId[userId], userGeneralEventPosition, nil
}

// получаем ключ кэша вида fromDateAt_toDateAt для метода getByUserId
func getKeyForGetByUserIdCache(fromDateAt int, toDateAt int) string {

	return strings.Join([]string{functions.IntToString(fromDateAt), functions.IntToString(toDateAt)}, "_")
}

// достаем данные по пользователям для метода getByUserId из кэша
func getUserListDataForGetByUserIdFromCache(userRatingStore *rating.UserRatingByDaysStore, key string) (map[int64]*dbRatingMemberDayList.UserRatingDayRow, []int64) {

	userRatingStore.Mu.Lock()
	defer userRatingStore.Mu.Unlock()

	// пробуем получить данные из кэша
	data, isCacheExist := userRatingStore.Store[key]

	// если данные в кэше имеются
	if !isCacheExist {
		return nil, nil
	}

	return data.UserTotalRatingByUserId, data.SortUserList
}

// достаем данные по пользователям для метода getByUserId из базы
func getUserListDataForGetByUserIdFromDb(ctx context.Context, companyDataConn *company_data.DbConn,
	fromDateAt int, toDateAt int) (map[int64]*dbRatingMemberDayList.UserRatingDayRow, []int64, error) {

	// достаем все записи дней рейтинга из базы за данный промежуток
	userRatingDayListByUsers, allUserList, err := dbRatingMemberDayList.GetAllForDayWithOffsetByInterval(ctx, companyDataConn, fromDateAt, toDateAt)
	if err != nil {

		log.Errorf(err.Error())
		return nil, nil, status.Error(500, "failed to get data from the database")
	}

	if userRatingDayListByUsers == nil {
		return nil, nil, nil
	}

	// получаем общий рейтинг для пользователей
	userTotalRatingByUserId := getUserTotalRatingByUserId(userRatingDayListByUsers)

	// получаем отсортированный список пользователей по general-значениям, чтобы получить general-позицию
	sortUserList := getSortedUsersForGetByUserId(allUserList, userTotalRatingByUserId, define.GeneralCounterId)
	return userTotalRatingByUserId, sortUserList, nil
}

// получаем общий рейтинг за дни для пользователей
// @long - большой цикл
func getUserTotalRatingByUserId(dbUserRatingForDays map[int64][]*dbRatingMemberDayList.UserRatingDayRow) map[int64]*dbRatingMemberDayList.UserRatingDayRow {

	// собираем общий рейтинг по пользователю
	UserRatingDayRowByUser := make(map[int64]*dbRatingMemberDayList.UserRatingDayRow)
	for userId, userRatingDayList := range dbUserRatingForDays {

		// проходимся по всем собранным дням
		for _, userRatingDayRow := range userRatingDayList {

			// если рейтинг для пользователя и отсутствует в списке, то добавляем
			UserRatingDayRow, isExist := UserRatingDayRowByUser[userId]
			if !isExist {
				UserRatingDayRow = &dbRatingMemberDayList.UserRatingDayRow{}
			}

			// устанавливаем флаг был ли заблочен пользователь
			UserRatingDayRow.IsDisabledAlias = userRatingDayRow.IsDisabledAlias || UserRatingDayRow.IsDisabledAlias

			// складываем значения ивентов для пользователя за полученные дни
			UserRatingDayRow.Data.GeneralCount += userRatingDayRow.Data.GeneralCount
			UserRatingDayRow.Data.ConversationMessageCount += userRatingDayRow.Data.ConversationMessageCount
			UserRatingDayRow.Data.ThreadMessageCount += userRatingDayRow.Data.ThreadMessageCount
			UserRatingDayRow.Data.FileCount += userRatingDayRow.Data.FileCount
			UserRatingDayRow.Data.ReactionCount += userRatingDayRow.Data.ReactionCount
			UserRatingDayRow.Data.VoiceCount += userRatingDayRow.Data.VoiceCount
			UserRatingDayRow.Data.CallCount += userRatingDayRow.Data.CallCount
			UserRatingDayRow.Data.RespectCount += userRatingDayRow.Data.RespectCount
			UserRatingDayRow.Data.ExactingnessCount += userRatingDayRow.Data.ExactingnessCount

			// получаем время последнего обновления
			if UserRatingDayRow.UpdatedAt < userRatingDayRow.UpdatedAt {
				UserRatingDayRow.UpdatedAt = userRatingDayRow.UpdatedAt
			}

			// устанавливаем запись обратно в список к остальным
			UserRatingDayRowByUser[userId] = UserRatingDayRow
		}
	}
	return UserRatingDayRowByUser
}

// формируем рейтинг пользователя для ответа
func initUserRatingStruct(userId int64, year int, UserRatingDayRow *dbRatingMemberDayList.UserRatingDayRow, generalPosition int) structUser.Rating {

	// если это пустая запись - формируем пустой ответ с userId. Если этого не сделаем - userId будет равен нулю
	if UserRatingDayRow == nil {

		return structUser.Rating{
			UserId:          userId,
			GeneralPosition: generalPosition,
			Year:            year,
		}
	}

	return structUser.Rating{
		UserId:          userId,
		GeneralPosition: generalPosition,
		Year:            year,
		EventCountList: map[string]int{
			define.EventIdAliasCount[define.ConversationMessageCounterId]: UserRatingDayRow.Data.ConversationMessageCount,
			define.EventIdAliasCount[define.ThreadMessageCounterId]:       UserRatingDayRow.Data.ThreadMessageCount,
			define.EventIdAliasCount[define.ReactionCounterId]:            UserRatingDayRow.Data.ReactionCount,
			define.EventIdAliasCount[define.FileCounterId]:                UserRatingDayRow.Data.FileCount,
			define.EventIdAliasCount[define.CallCounterId]:                UserRatingDayRow.Data.CallCount,
			define.EventIdAliasCount[define.VoiceCounterId]:               UserRatingDayRow.Data.VoiceCount,
			define.EventIdAliasCount[define.RespectCounterId]:             UserRatingDayRow.Data.RespectCount,
			define.EventIdAliasCount[define.ExactingnessCounterId]:        UserRatingDayRow.Data.ExactingnessCount,
		},
		GeneralCount: UserRatingDayRow.Data.GeneralCount,
		UpdatedAt:    UserRatingDayRow.UpdatedAt,
	}
}

// формируем рейтинг пользователя для ответа
func initUserRatingStructGrpc(userId int64, year int, UserRatingDayRow *dbRatingMemberDayList.UserRatingDayRow, generalPosition int) *pb.RatingGetByUserIdResponseStruct {

	// если это пустая запись - формируем пустой ответ с userId. Если этого не сделаем - userId будет равен нулю
	if UserRatingDayRow == nil {

		return &pb.RatingGetByUserIdResponseStruct{
			UserId:          userId,
			GeneralPosition: int64(generalPosition),
			Year:            int64(year),
		}
	}

	return &pb.RatingGetByUserIdResponseStruct{
		UserId:          userId,
		GeneralPosition: int64(generalPosition),
		Year:            int64(year),
		EventCountList: map[string]int64{
			define.EventIdAliasCount[define.ConversationMessageCounterId]: int64(UserRatingDayRow.Data.ConversationMessageCount),
			define.EventIdAliasCount[define.ThreadMessageCounterId]:       int64(UserRatingDayRow.Data.ThreadMessageCount),
			define.EventIdAliasCount[define.ReactionCounterId]:            int64(UserRatingDayRow.Data.ReactionCount),
			define.EventIdAliasCount[define.FileCounterId]:                int64(UserRatingDayRow.Data.FileCount),
			define.EventIdAliasCount[define.CallCounterId]:                int64(UserRatingDayRow.Data.CallCount),
			define.EventIdAliasCount[define.VoiceCounterId]:               int64(UserRatingDayRow.Data.VoiceCount),
			define.EventIdAliasCount[define.RespectCounterId]:             int64(UserRatingDayRow.Data.RespectCount),
			define.EventIdAliasCount[define.ExactingnessCounterId]:        int64(UserRatingDayRow.Data.ExactingnessCount),
		},
		GeneralCount: int64(UserRatingDayRow.Data.GeneralCount),
		UpdatedAt:    UserRatingDayRow.UpdatedAt,
	}
}

func initUserRatingListStructGrpc(userRating []*pb.RatingGetByUserIdResponseStruct) *pb.RatingGetByUserIdListResponseStruct {

	return &pb.RatingGetByUserIdListResponseStruct{
		UserRatingList: userRating,
	}
}

// получаем количество определенного ивента за интервал
func GetEventCountByInterval(ctx context.Context, companyDataConn *company_data.DbConn, event string, year int, fromDateAt int,
	toDateAt int) ([]structGeneral.EventCount, []*pb.EventCount, error) {

	// получаем id эвента и выполняем проверки
	eventId, exist := define.EventCountAliasId[event]
	if !exist || year < 2020 || fromDateAt < 0 || toDateAt < 0 || fromDateAt > toDateAt {
		return nil, nil, status.Error(400, "getter bad params")
	}

	_, err := company_data.GetDbName()
	if err != nil {
		return nil, nil, status.Error(400, "getter bad params")
	}

	// получаем все дни из базы за данный период
	ratingDayList, err := dbRatingDayList.GetByInterval(ctx, companyDataConn, fromDateAt, toDateAt)
	if err != nil {
		return nil, nil, status.Error(500, "failed to get data from the database")
	}

	// формируем ответ
	eventCountList := make([]structGeneral.EventCount, 0)
	eventCountListGrpc := make([]*pb.EventCount, 0)
	for _, ratingDay := range ratingDayList {

		eventCountList, eventCountListGrpc = appendToEventCountList(year, eventId, ratingDay, eventCountList, eventCountListGrpc)
	}

	return eventCountList, eventCountListGrpc, nil
}

// добавляем рейтинг ивента за интервал к ответу
func appendToEventCountList(year int, eventId int64, ratingDay *dbRatingDayList.RatingDayRow, eventCountList []structGeneral.EventCount, eventCountListGrpc []*pb.EventCount) ([]structGeneral.EventCount, []*pb.EventCount) {

	eventCountList = append(eventCountList, structGeneral.EventCount{
		Year:  year,
		Day:   ratingDay.Day,
		Count: getEventByIdFromRatingRow(eventId, ratingDay),
	})

	eventCountListGrpc = append(eventCountListGrpc, &pb.EventCount{
		Year:  int64(year),
		Day:   int64(ratingDay.Day),
		Count: int64(getEventByIdFromRatingRow(eventId, ratingDay)),
	})

	return eventCountList, eventCountListGrpc
}

// получаем количество ивентов за интервал
func GetGeneralEventCountByInterval(ctx context.Context, companyDataConn *company_data.DbConn, year int, fromDateAt int,
	toDateAt int) ([]structGeneral.EventCount, []*pb.EventCount, error) {

	// выполняем проверки
	if year < 2000 || fromDateAt < 0 || toDateAt < 0 || fromDateAt > toDateAt {
		return nil, nil, status.Error(400, "getter bad params")
	}
	_, err := company_data.GetDbName()
	if err != nil {
		return nil, nil, status.Error(400, "getter bad params")
	}

	// получаем все дни за выбранный период
	ratingDayList, err := dbRatingDayList.GetAllByIntervalWithoutData(ctx, companyDataConn, fromDateAt, toDateAt)
	if err != nil {
		return nil, nil, status.Error(500, "failed to get data from the database")
	}

	// формируем пустой массив который потом заполняем
	eventCountList := make([]structGeneral.EventCount, 0)
	eventCountListGrpc := make([]*pb.EventCount, 0)

	for _, v := range ratingDayList {
		eventCountList, eventCountListGrpc = appendToGeneralEventCountList(year, v, eventCountList, eventCountListGrpc)
	}

	return eventCountList, eventCountListGrpc, nil
}

// добавляем рейтинг ивента за интервал к ответу
func appendToGeneralEventCountList(year int, ratingDayRowWithoutData *dbRatingDayList.RatingDayRowWithoutData, eventCountList []structGeneral.EventCount, eventCountListGrpc []*pb.EventCount) ([]structGeneral.EventCount, []*pb.EventCount) {

	eventCountList = append(eventCountList, structGeneral.EventCount{
		Year:  year,
		Day:   ratingDayRowWithoutData.Day,
		Count: ratingDayRowWithoutData.GeneralCount,
	})

	eventCountListGrpc = append(eventCountListGrpc, &pb.EventCount{
		Year:  int64(year),
		Day:   int64(ratingDayRowWithoutData.Day),
		Count: int64(ratingDayRowWithoutData.GeneralCount),
	})

	return eventCountList, eventCountListGrpc
}

// сохраняем данные из кэша в таблицу
func ForceSaveCache(ctx context.Context, storage *storageMain.MainStorage, store *rating.Store, companyDataConn *company_data.DbConn,
	isolation *GlobalIsolation.GlobalIsolation) {

	go func() {

		year := time.Now().Year()
		day := functions.GetDaysCountByYear(year)

		err := DumpToActiveTable(ctx, storage, store, companyDataConn, isolation, year, day)
		if err != nil {
			log.Errorf("%v", err)
		}
	}()
}

// помечаем пользователя забаненным (разбаненным в рейтинге)
func SetUserBlockInSystemStatus(ctx context.Context, companyDataConn *company_data.DbConn, UserId int64, Status int) error {

	go func() {

		// помечаем забаненным в почасовом рейтинге
		err := setUserDisabledStatusInEventHoursList(ctx, companyDataConn, UserId, Status)
		if err != nil {

			log.Errorf("Could not update the disabled status %v", err)
			return
		}

		// помечаем забаненным в дневном рейтинге
		err = setUserDisabledStatusInUserRatingDayList(ctx, companyDataConn, UserId, Status)
		if err != nil {

			log.Errorf("Could not update the disabled status %v", err)
			return
		}
	}()

	return nil
}

// узнаем, заблокирован ли пользователь
func GetUserStatus(ctx context.Context, companyDataConn *company_data.DbConn, userId int64) (int, error) {

	// смотрим, забанен ли в почасовом рейтинге
	statusHour, err := getUserStatusInHourRating(ctx, companyDataConn, userId)
	if err != nil {

		log.Errorf("Could not get the disabled status %v", err)
		return -1, err
	}

	// смотрим, забанен ли в дневном рейтинге
	statusDay, err := getUserStatusInDayRating(companyDataConn, userId)
	if err != nil {

		log.Errorf("Could not get disabled status %v", err)
		return -1, err
	}

	// сверяем полученные данные с двух таблиц
	// если статусы равны, то просто его отдаем
	if statusHour == statusDay {

		return statusHour, nil
	}

	// приоритет у незаблокированного, после у заблокированного, а потом у отсутствия записей
	if statusHour < statusDay {

		return statusHour, nil
	} else {
		return statusDay, nil
	}

}

// получаем список записей за день
func GetListByDay(ctx context.Context, companyDataConn *company_data.DbConn, year int, day int) ([]structGeneral.UserDayStats, []*pb.UserDayStats, error) {

	// формируем пустой массив который потом заполняем
	userDayStatsList := make([]structGeneral.UserDayStats, 0)
	userDayStatsListGrpc := make([]*pb.UserDayStats, 0)

	list, err := dbRatingMemberDayList.GetAllForDay(ctx, companyDataConn, year, day)
	if err != nil {
		return userDayStatsList, userDayStatsListGrpc, err
	}

	for _, v := range list {

		userDayStats, eventCountListGrpc := convertToUserDayStats(v)
		userDayStatsList = append(userDayStatsList, userDayStats)
		userDayStatsListGrpc = append(userDayStatsListGrpc, eventCountListGrpc)
	}

	return userDayStatsList, userDayStatsListGrpc, nil
}

// функция для конвертации записи из базы в структуры
func convertToUserDayStats(row *dbRatingMemberDayList.UserRatingDayRow) (structGeneral.UserDayStats, *pb.UserDayStats) {

	userDayStats := structGeneral.UserDayStats{
		UserId: row.UserId,
		Data:   row.Data,
	}

	userDayStatsGrpc := &pb.UserDayStats{
		UserId: row.UserId,
		Data:   dbRatingMemberDayList.ConvertToMap(row.Data),
	}

	return userDayStats, userDayStatsGrpc
}

// функция для очистки пользовательского кэша
func ClearUserRatingCache(userRatingStore *rating.UserRatingByDaysStore) {

	userRatingStore.Mu.Lock()
	defer userRatingStore.Mu.Unlock()
	userRatingStore.Store = make(map[string]*rating.UserRatingByDaysStoreItem)
}

// изменяем статистику пользователя для рейтинга
func IncDayRatingEventCountForUser(ctx context.Context, mainStorage *storageMain.MainStorage, ratingStore *rating.Store, companyDataConn *company_data.DbConn,
	global *GlobalIsolation.GlobalIsolation, UserId int64, Year int, Day int, eventId int64, Inc int) {

	// сливаем записи из кэша в активную таблицу
	_ = DumpToActiveTable(ctx, mainStorage, ratingStore, companyDataConn, global, Year, Day)

	// инитим в хранилище новую базу
	_ = mainStorage.Init(ctx, companyDataConn, Year, Day)

	// получаем время начала часа
	hour := HourStart()
	ratingStore.StorageInc(hour, UserId, eventId, Inc)

	// сливаем записи из кэша в активную таблицу
	_ = DumpToActiveTable(ctx, mainStorage, ratingStore, companyDataConn, global, Year, Day)

	// ворачиваем сервис в норму
	year := time.Now().Year()
	day := functions.GetDaysCountByYear(year)
	_ = mainStorage.Init(ctx, companyDataConn, year, day)
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// получаем количество ивентов опредленного типа из ratingRow
// @long - много условий
func getEventByIdFromRatingRow(eventId int64, ratingRow *dbRatingDayList.RatingDayRow) int {

	if eventId == define.GeneralCounterId {
		return ratingRow.GeneralCount
	}
	if eventId == define.ConversationMessageCounterId {
		return ratingRow.Data.ConversationMessageCount
	}
	if eventId == define.ThreadMessageCounterId {
		return ratingRow.Data.ThreadMessageCount
	}
	if eventId == define.FileCounterId {
		return ratingRow.Data.FileCount
	}
	if eventId == define.VoiceCounterId {
		return ratingRow.Data.VoiceCount
	}
	if eventId == define.CallCounterId {
		return ratingRow.Data.CallCount
	}
	if eventId == define.ReactionCounterId {
		return ratingRow.Data.ReactionCount
	}
	if eventId == define.RespectCounterId {
		return ratingRow.Data.RespectCount
	}
	if eventId == define.ExactingnessCounterId {
		return ratingRow.Data.ExactingnessCount
	}
	return 0
}

// получаем место занимаемое в общем рейтинге значение по типу ивента
// @long - много условий
func getPositionAndCountByEventFromUserRatingRow(eventId int64, UserRatingDayRow *dbRatingMemberDayList.UserRatingDayRow, userRatingPosition structUser.RatingPosition) (int, int) {

	if eventId == define.GeneralCounterId {
		return userRatingPosition.GeneralPosition, UserRatingDayRow.Data.GeneralCount
	}
	if eventId == define.ConversationMessageCounterId {
		return userRatingPosition.ConversationMessagePosition, UserRatingDayRow.Data.ConversationMessageCount
	}
	if eventId == define.ThreadMessageCounterId {
		return userRatingPosition.ThreadMessagePosition, UserRatingDayRow.Data.ThreadMessageCount
	}
	if eventId == define.FileCounterId {
		return userRatingPosition.FilePosition, UserRatingDayRow.Data.FileCount
	}
	if eventId == define.VoiceCounterId {
		return userRatingPosition.VoicePosition, UserRatingDayRow.Data.VoiceCount
	}
	if eventId == define.CallCounterId {
		return userRatingPosition.CallPosition, UserRatingDayRow.Data.CallCount
	}
	if eventId == define.ReactionCounterId {
		return userRatingPosition.ReactionPosition, UserRatingDayRow.Data.ReactionCount
	}
	if eventId == define.RespectCounterId {
		return userRatingPosition.RespectPosition, UserRatingDayRow.Data.RespectCount
	}
	if eventId == define.ExactingnessCounterId {
		return userRatingPosition.ExactingnessPosition, UserRatingDayRow.Data.ExactingnessCount
	}
	return 0, 0
}

// получаем место занимаемое в общем рейтинге значение по типу ивента
// @long - много условий
func getCountByEventFromUserRatingRow(eventId int64, UserRatingDayRow *dbRatingMemberDayList.UserRatingDayRow) int {

	if eventId == define.GeneralCounterId {
		return UserRatingDayRow.Data.GeneralCount
	}
	if eventId == define.ConversationMessageCounterId {
		return UserRatingDayRow.Data.ConversationMessageCount
	}
	if eventId == define.ThreadMessageCounterId {
		return UserRatingDayRow.Data.ThreadMessageCount
	}
	if eventId == define.FileCounterId {
		return UserRatingDayRow.Data.FileCount
	}
	if eventId == define.VoiceCounterId {
		return UserRatingDayRow.Data.VoiceCount
	}
	if eventId == define.CallCounterId {
		return UserRatingDayRow.Data.CallCount
	}
	if eventId == define.ReactionCounterId {
		return UserRatingDayRow.Data.ReactionCount
	}
	if eventId == define.RespectCounterId {
		return UserRatingDayRow.Data.RespectCount
	}
	if eventId == define.ExactingnessCounterId {
		return UserRatingDayRow.Data.ExactingnessCount
	}
	return 0
}

// получаем ответ из базы за период
// @long - временно, пока не перейдем на grpc
func getFromDbByInterval(ctx context.Context, companyDataConn *company_data.DbConn, response structGeneral.Rating, responseGrpc *pb.RatingGetResponseStruct,
	fromDateAt int, toDateAt int, topListOffset int, topListCount int, eventId int64) (structGeneral.Rating, *pb.RatingGetResponseStruct, error) {

	// достаем все записи из базы за данный промежуток
	dbUserRatingForDays, userList, err := dbRatingMemberDayList.GetTotalForDayByInterval(ctx, companyDataConn, fromDateAt, toDateAt)
	if err != nil {

		log.Errorf(err.Error())
		return structGeneral.Rating{}, responseGrpc, status.Error(500, "failed to get data from the database")
	}

	// сортируем всех полученных пользователей
	allUserList := getSortedUsersForTopList(userList, dbUserRatingForDays, eventId)

	// подготавливаем пользователей для топ-листа
	userRatingTotalList, userRatingPosition := prepareUsersForTopList(allUserList, topListOffset, topListCount, eventId)

	// формируем сам топлист
	topList := make([]structGeneral.TopItem, 0)
	topListGrpc := make([]*pb.TopItem, 0)
	for _, v := range userRatingTotalList {

		position, count := getPositionAndCountByEventFromUserRatingRow(eventId, dbUserRatingForDays[v], userRatingPosition[v])

		var isDisabled int64 = 0
		if dbUserRatingForDays[v].IsDisabledAlias {
			isDisabled = 1
		}

		// добавляем к ответу в topList
		topList = append(topList, structGeneral.TopItem{UserId: v, Position: position, Count: count, IsDisabled: isDisabled})
		topListGrpc = append(topListGrpc, &pb.TopItem{UserId: v, Position: int64(position), Count: int64(count), IsDisabled: isDisabled})
	}

	// получаем данные для топа за дни
	ratingRowList, err := dbRatingDayList.GetByInterval(ctx, companyDataConn, fromDateAt, toDateAt)
	if err != nil {

		log.Errorf(err.Error())
		return structGeneral.Rating{}, responseGrpc, status.Error(500, "failed to get data from the database")
	}

	// получаем количество ивента за пероид
	for _, v := range ratingRowList {

		response.Count += getEventByIdFromRatingRow(eventId, v)
	}

	responseGrpc.Count = int64(response.Count)
	response.TopList = topList
	responseGrpc.TopList = topListGrpc

	// если имеются еще пользователи
	if len(topList) == topListCount {

		response.HasNext = 1
		responseGrpc.HasNext = 1
	}

	return response, responseGrpc, nil
}

// получаем отсортированный список пользователей для топ-листа
func getSortedUsersForTopList(userList []int64, dbUserRatingForDays map[int64]*dbRatingMemberDayList.UserRatingDayRow, eventId int64) []int64 {

	// получаем список пользователей (доступных и заблокированных) и суммированные значения нужного нам ивента
	var allowUserList []int64
	var disabledUserList []int64
	countGroupedByUserId := make(map[int64]int, len(dbUserRatingForDays))

	// проходимся по пользователям
	for _, userId := range userList {

		// если не нашли такого пользователя в списке, то пропускаем
		UserRatingDayRow, isExist := dbUserRatingForDays[userId]
		if !isExist {
			continue
		}

		// если значений ивента у пользователя не набрано - пропускаем
		eventCount := getCountByEventFromUserRatingRow(eventId, UserRatingDayRow)
		if eventCount == 0 {
			continue
		}

		// значения нужного нам ивента, сгруппированные по пользователю
		countGroupedByUserId[userId] = eventCount

		// если пользователь НЕ заблочен, то добавляем в список доступных; иначе - в список заблокированных
		if !UserRatingDayRow.IsDisabledAlias {
			allowUserList = append(allowUserList, userId)
		} else {
			disabledUserList = append(disabledUserList, userId)
		}
	}

	return sortUsersByCount(allowUserList, disabledUserList, countGroupedByUserId)
}

// получаем отсортированный список пользователей для getByUserId
func getSortedUsersForGetByUserId(userList []int64, dbUserRatingDayRow map[int64]*dbRatingMemberDayList.UserRatingDayRow, eventId int64) []int64 {

	// получаем список пользователей (доступных и заблокированных) и суммированные значения нужного нам ивента
	var allowUserList []int64
	var disabledUserList []int64
	countGroupedByUserId := make(map[int64]int)

	// проходимся по пользователям
	for _, userId := range userList {

		// если не нашли такого пользователя в списке, то пропускаем
		if _, isExist := dbUserRatingDayRow[userId]; !isExist {
			continue
		}

		UserRatingDayRow := dbUserRatingDayRow[userId]

		// получаем количество набранных значений для ивента
		eventCount := getCountByEventFromUserRatingRow(eventId, UserRatingDayRow)

		// устанавливаем значения нужного нам ивента, группируя по пользователю
		countGroupedByUserId[userId] = eventCount

		// если пользователь НЕ заблочен, то добавляем в список доступных; иначе - в список заблокированных
		if !UserRatingDayRow.IsDisabledAlias {
			allowUserList = append(allowUserList, userId)
		} else {
			disabledUserList = append(disabledUserList, userId)
		}
	}

	return sortUsersByCount(allowUserList, disabledUserList, countGroupedByUserId)
}

// получаем пользователей для топ-листа
func prepareUsersForTopList(allUserList []int64, topListOffset int, topListCount int, eventId int64) ([]int64, map[int64]structUser.RatingPosition) {

	// определяем значение для позиции пользователей и сколько пропускаем
	position := topListOffset + 1
	skipCount := 0

	var userRatingTotalList []int64
	userRatingPosition := make(map[int64]structUser.RatingPosition)

	for _, userId := range allUserList {

		// если нужно пропустить первых пользователей
		if skipCount < topListOffset {

			skipCount++
			continue
		}

		// если для топ-листа набралось достаточно пользователей
		if len(userRatingTotalList) == topListCount {
			break
		}

		ratingPosition, ok := userRatingPosition[userId]
		if !ok {
			ratingPosition = structUser.RatingPosition{}
		}

		// устанавливаем позицию пользователю
		setPositionByEventId(eventId, position, &ratingPosition)
		position = position + 1
		userRatingPosition[userId] = ratingPosition
		userRatingTotalList = append(userRatingTotalList, userId)
	}
	return userRatingTotalList, userRatingPosition
}

// устанавливаем позицию пользователю по ивенту
// @long - множество ивентов
func setPositionByEventId(eventId int64, position int, ratingPosition *structUser.RatingPosition) {

	// устанавливаем позицию пользователю по типу event
	if eventId == define.GeneralCounterId {
		ratingPosition.GeneralPosition = position
	}
	if eventId == define.ConversationMessageCounterId {
		ratingPosition.ConversationMessagePosition = position
	}
	if eventId == define.ThreadMessageCounterId {
		ratingPosition.ThreadMessagePosition = position
	}
	if eventId == define.ReactionCounterId {
		ratingPosition.ReactionPosition = position
	}
	if eventId == define.FileCounterId {
		ratingPosition.FilePosition = position
	}
	if eventId == define.CallCounterId {
		ratingPosition.CallPosition = position
	}
	if eventId == define.VoiceCounterId {
		ratingPosition.VoicePosition = position
	}
	if eventId == define.RespectCounterId {
		ratingPosition.RespectPosition = position
	}
	if eventId == define.ExactingnessCounterId {
		ratingPosition.ExactingnessPosition = position
	}
}

// помечаем забаненным в почасовом рейтинге
func setUserDisabledStatusInEventHoursList(ctx context.Context, companyDataConn *company_data.DbConn, userId int64, isDisabled int) error {

	// обновляем запись в базе
	err := dbEventHoursList.SetIsDisabledStatus(ctx, companyDataConn, userId, isDisabled)
	if err != nil {
		return err
	}

	return nil
}

// помечаем забаненным в рейтинге по дням
func setUserDisabledStatusInUserRatingDayList(ctx context.Context, companyDataConn *company_data.DbConn, userId int64, isDisabled int) error {

	// обновляем запись в базе
	err := dbRatingMemberDayList.SetIsDisabledStatus(ctx, companyDataConn, userId, isDisabled)
	if err != nil {
		return err
	}

	return nil
}

// смотрим, забанен ли в почасовом рейтинге
func getUserStatusInHourRating(ctx context.Context, companyDataConn *company_data.DbConn, userId int64) (int, error) {

	// получаем значениче из базы
	isDisabled, isExist, err := dbEventHoursList.GetUserStatus(ctx, companyDataConn, userId)
	if err != nil {
		return -1, err
	}

	if !isExist {
		return define.UserRatingNotExist, nil
	}
	switch isDisabled {

	case 0:
		return define.UserRatingNotBlocked, nil
	case 1:
		return define.UserRatingBlocked, nil
	default:
		return define.UserRatingNotExist, nil

	}
}

// смотрим, забанен ли в рейтинге по дням
func getUserStatusInDayRating(companyDataConn *company_data.DbConn, userId int64) (int, error) {

	// получаем значение из базы
	isDisabled, isExist, err := dbRatingMemberDayList.GetIsDisabledStatus(companyDataConn, userId)
	if err != nil {
		return -1, err
	}

	if !isExist {
		return define.UserRatingNotExist, nil
	}
	switch isDisabled {

	case 0:
		return define.UserRatingNotBlocked, nil
	case 1:
		return define.UserRatingBlocked, nil
	default:
		return define.UserRatingNotExist, nil

	}
}

// подготавливаем рейтинг пользователя
func prepareUserRating(needUserId int64, allUserList []int64) int {

	position := 0

	for _, userId := range allUserList {

		// пользователи у нас отсортированы по значениям, здесь мы определяем позицию
		position = position + 1

		// если не наш пользователь
		if userId != needUserId {
			continue
		}
		break
	}

	return position
}

// сортируем пользователей по набранному количеству ивентов
func sortUsersByCount(allowUserList []int64, disabledUserList []int64, countGroupedByUserId map[int64]int) []int64 {

	// сортируем доступных пользователей по user_id (по возрастанию)
	sort.SliceStable(allowUserList, func(i, j int) bool {
		return allowUserList[i] < allowUserList[j]
	})

	// сортируем заблокированных пользователей по user_id (по возрастанию)
	sort.SliceStable(disabledUserList, func(i, j int) bool {
		return disabledUserList[i] < disabledUserList[j]
	})

	outputList := make([]int64, len(allowUserList)+len(disabledUserList))

	// сортируем доступных пользователей по набранным значениям
	sort.SliceStable(allowUserList, func(i, j int) bool {
		return countGroupedByUserId[allowUserList[i]] > countGroupedByUserId[allowUserList[j]]
	})

	// сортируем заблоченных пользователей по набранным значениям
	sort.SliceStable(disabledUserList, func(i, j int) bool {
		return countGroupedByUserId[disabledUserList[i]] > countGroupedByUserId[disabledUserList[j]]
	})

	copy(outputList[:], allowUserList[:])
	copy(outputList[len(allowUserList):], disabledUserList[:])

	// возвращаем объединенный список доступных и заблокированных пользователей
	return outputList
}
