package storage

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_company/api/includes/type/db/company_data"
	dbRatingMemberDayList "go_company/api/includes/type/db/company_data/rating_member_day_list"
	"go_company/api/includes/type/define"
	"sort"
	"sync"
)

// структура которая описывает все хранилище активных ивентов
type MainStorage struct {
	mu                             sync.RWMutex
	countGroupedByUserIdAndEventId map[int64]*countGroupedByUserId
	userListGroupedByEventId       map[int64][]int64
	countGroupedByEventId          map[int64]int
	allUserList                    map[int64]int
	lastUpdatedAt                  int64
	lastDay                        int
	lastYear                       int
}

type countGroupedByUserId struct {
	mu            sync.RWMutex
	countByUserId map[int64]int
}

func MakeMainStorage() *MainStorage {

	return &MainStorage{
		countGroupedByUserIdAndEventId: make(map[int64]*countGroupedByUserId),
		userListGroupedByEventId:       make(map[int64][]int64),
		countGroupedByEventId:          make(map[int64]int),
		allUserList:                    make(map[int64]int),
		lastUpdatedAt:                  0,
		mu:                             sync.RWMutex{},
	}
}

// Init функция для кэширования начальных данных
func (mainStore *MainStorage) Init(ctx context.Context, companyDataConn *company_data.DbConn, year int, day int) error {

	mainStore.initMainStore(year, day)

	userRatingList, err := dbRatingMemberDayList.GetAllForDay(ctx, companyDataConn, year, day)
	if err != nil {
		return fmt.Errorf("couldn't init company storage")
	}

	incToStore := initIncToStore()

	for _, v := range userRatingList {

		incToStore[define.GeneralCounterId][v.UserId] = v.Data.GeneralCount
		incToStore[define.ConversationMessageCounterId][v.UserId] = v.Data.ConversationMessageCount
		incToStore[define.ThreadMessageCounterId][v.UserId] = v.Data.ThreadMessageCount
		incToStore[define.CallCounterId][v.UserId] = v.Data.CallCount
		incToStore[define.FileCounterId][v.UserId] = v.Data.FileCount
		incToStore[define.VoiceCounterId][v.UserId] = v.Data.VoiceCount
		incToStore[define.ReactionCounterId][v.UserId] = v.Data.ReactionCount
		incToStore[define.RespectCounterId][v.UserId] = v.Data.RespectCount
		incToStore[define.ExactingnessCounterId][v.UserId] = v.Data.ExactingnessCount
	}
	mainStore.IncToMainStore(year, day, incToStore)

	return nil
}

// инициализируем главное хранилище
func (mainStore *MainStorage) initMainStore(year int, day int) {

	mainStore.mu.Lock()
	mainStore.lastYear = year
	mainStore.lastDay = day
	mainStore.countGroupedByUserIdAndEventId = make(map[int64]*countGroupedByUserId)
	mainStore.userListGroupedByEventId = make(map[int64][]int64)
	mainStore.countGroupedByEventId = make(map[int64]int)
	mainStore.allUserList = make(map[int64]int)
	mainStore.mu.Unlock()
}

// инициализируем incToStore
func initIncToStore() map[int64]map[int64]int {

	incToStore := make(map[int64]map[int64]int)
	incToStore[define.GeneralCounterId] = make(map[int64]int)
	incToStore[define.ConversationMessageCounterId] = make(map[int64]int)
	incToStore[define.ThreadMessageCounterId] = make(map[int64]int)
	incToStore[define.CallCounterId] = make(map[int64]int)
	incToStore[define.FileCounterId] = make(map[int64]int)
	incToStore[define.VoiceCounterId] = make(map[int64]int)
	incToStore[define.ReactionCounterId] = make(map[int64]int)
	incToStore[define.RespectCounterId] = make(map[int64]int)
	incToStore[define.ExactingnessCounterId] = make(map[int64]int)
	return incToStore
}

// добавляем данные в кэш
// @long сюда смотреть даже страшно
func (mainStore *MainStorage) IncToMainStore(year int, day int, incToStore map[int64]map[int64]int) map[int64]int {

	userListGroupedByEventId, countGroupedByUserIdAndEventId, countGroupedByEventId, allUserList := mainStore.GetTempStorage(year, day)

	// идем по всем ивентам которые надо добавить
	for eventId, v1 := range incToStore {

		// получаем количество значений и пользователей для ивента
		countGroupedByUserIdObj, userList := mainStore.getUserListAndCountGroupedByUserId(eventId, countGroupedByUserIdAndEventId, userListGroupedByEventId)

		count := countGroupedByEventId[eventId]

		countGroupedByUserIdObj.mu.Lock()

		// добавляем для каждого пользователя
		for userId, value := range v1 {

			countGroupedByUserIdObj.countByUserId[userId] += value
			count += value
			if !isUserIdInSlice(userId, userList) {
				userList = append(userList, userId)
			}
			if _, isExist := allUserList[userId]; !isExist {
				allUserList[userId] = 1
			}
		}
		countGroupedByUserIdObj.mu.Unlock()

		// сортируем
		userList = countGroupedByUserIdObj.sortUserList(userList)

		mainStore.mu.Lock()
		userListGroupedByEventId[eventId] = userList
		countGroupedByUserIdAndEventId[eventId] = countGroupedByUserIdObj
		countGroupedByEventId[eventId] = count
		mainStore.mu.Unlock()
	}

	// добавляем все в главное хранилище
	mainStore.addFromTempToMainStorage(userListGroupedByEventId, countGroupedByUserIdAndEventId, countGroupedByEventId, allUserList, day, year)

	return allUserList
}

// получаем количество значений и пользователей для ивента
func (mainStore *MainStorage) getUserListAndCountGroupedByUserId(
	eventId int64,
	countGroupedByUserIdAndEventId map[int64]*countGroupedByUserId,
	userListGroupedByEventId map[int64][]int64,
) (*countGroupedByUserId, []int64) {

	mainStore.mu.RLock()
	defer mainStore.mu.RUnlock()

	// получаем значения для счетчиков события по пользователям
	countGroupedByUserIdObj, isExist := countGroupedByUserIdAndEventId[eventId]
	if !isExist {
		countGroupedByUserIdObj = &countGroupedByUserId{countByUserId: make(map[int64]int)}
	}

	// получаем список пользователей
	userList, isExist := userListGroupedByEventId[eventId]
	if !isExist {
		userList = make([]int64, 0)
	}

	return countGroupedByUserIdObj, userList
}

// декрементим значения в кэше
func (mainStore *MainStorage) Dec(userId int64, eventId int64, value int) {

	mainStore.mu.Lock()

	// анлочим после выполнения функции
	defer mainStore.mu.Unlock()

	if _, isExist := mainStore.countGroupedByUserIdAndEventId[eventId]; !isExist {
		return
	}
	mainStore.countGroupedByUserIdAndEventId[eventId].mu.Lock()

	// декрементим значения
	if mainStore.countGroupedByUserIdAndEventId[eventId].countByUserId[userId] > 0 {
		mainStore.countGroupedByUserIdAndEventId[eventId].countByUserId[userId] -= value
	}
	mainStore.countGroupedByUserIdAndEventId[eventId].mu.Unlock()

	if mainStore.countGroupedByEventId[eventId] > 0 {
		mainStore.countGroupedByEventId[eventId] -= value
	}

	// если ивент из тех, при котором не трогаем General-значения
	if _, isExist := define.EventIdListForSkipGeneral[eventId]; isExist {
		return
	}

	mainStore.countGroupedByUserIdAndEventId[eventId].mu.Lock()

	if mainStore.countGroupedByUserIdAndEventId[define.GeneralCounterId].countByUserId[userId] > 0 {
		mainStore.countGroupedByUserIdAndEventId[define.GeneralCounterId].countByUserId[userId] -= value
	}
	mainStore.countGroupedByUserIdAndEventId[eventId].mu.Unlock()

	if mainStore.countGroupedByEventId[define.GeneralCounterId] > 0 {
		mainStore.countGroupedByEventId[define.GeneralCounterId] -= value
	}
}

// получить слепок хранилища
func (mainStore *MainStorage) GetTempStorage(year int, day int) (map[int64][]int64, map[int64]*countGroupedByUserId, map[int64]int, map[int64]int) {

	countGroupedByUserIdAndEventId := make(map[int64]*countGroupedByUserId)
	userListGroupedByEventId := make(map[int64][]int64)
	countGroupedByEventId := make(map[int64]int)
	allUserList := make(map[int64]int)

	// достаем данные из кэша во временный
	mainStore.mu.RLock()

	// если только у нас совпали даты мы берем существующий кэш
	if mainStore.lastYear == year && mainStore.lastDay == day {

		countGroupedByUserIdAndEventId = mainStore.countGroupedByUserIdAndEventId
		userListGroupedByEventId = mainStore.userListGroupedByEventId
		countGroupedByEventId = mainStore.countGroupedByEventId
		allUserList = mainStore.allUserList
	}
	mainStore.mu.RUnlock()
	return userListGroupedByEventId, countGroupedByUserIdAndEventId, countGroupedByEventId, allUserList
}

// проверяем, что в слайсе есть userId
func isUserIdInSlice(a int64, list []int64) bool {

	for _, b := range list {

		if b == a {
			return true
		}
	}
	return false
}

// сортируем хранилище
func (countGroupedByUserId *countGroupedByUserId) sortUserList(userList []int64) []int64 {

	countGroupedByUserId.mu.RLock()

	// сортируем всех пользователей по значению
	sort.SliceStable(userList, func(i, j int) bool {
		return countGroupedByUserId.countByUserId[userList[i]] >
			countGroupedByUserId.countByUserId[userList[j]]
	})

	countGroupedByUserId.mu.RUnlock()

	return userList
}

// добавить из временного в основное
func (mainStore *MainStorage) addFromTempToMainStorage(
	userListGroupedByEventId map[int64][]int64,
	countGroupedByUserIdAndEventId map[int64]*countGroupedByUserId,
	countGroupedByEventId map[int64]int,
	allUserList map[int64]int,
	day int,
	year int,
) {

	// добавляем все в главное хранилище
	mainStore.mu.Lock()
	mainStore.userListGroupedByEventId = userListGroupedByEventId
	mainStore.countGroupedByUserIdAndEventId = countGroupedByUserIdAndEventId
	mainStore.countGroupedByEventId = countGroupedByEventId
	mainStore.allUserList = allUserList
	mainStore.lastUpdatedAt = functions.GetCurrentTimeStamp()
	mainStore.lastDay = day
	mainStore.lastYear = year
	mainStore.mu.Unlock()
}

// GetCountGroupedByEventId получаем количество ивентов сгрупированных по ивенту
func (mainStore *MainStorage) GetCountGroupedByEventId() map[int64]int {

	mainStore.mu.RLock()
	countGroupedByEventId := mainStore.countGroupedByEventId
	mainStore.mu.RUnlock()

	return countGroupedByEventId
}

// GetAllUserList получаем пользователей
func (mainStore *MainStorage) GetAllUserList() map[int64]int {

	mainStore.mu.RLock()
	allUserList := mainStore.allUserList
	mainStore.mu.RUnlock()

	return allUserList
}

// GetCountByUserIdGroupedByEventId получаем количество ивентов пользователя сгрупированных по ивенту
func (mainStore *MainStorage) GetCountByUserIdGroupedByEventId(userId int64) map[int64]int {

	countGroupedByEventId := make(map[int64]int)

	mainStore.mu.RLock()
	defer mainStore.mu.RUnlock()
	countGroupedByUserIdAndEventId := mainStore.countGroupedByUserIdAndEventId

	for eventId := range countGroupedByUserIdAndEventId {

		countByEventId, isExist := countGroupedByUserIdAndEventId[eventId]
		if !isExist {

			countGroupedByEventId[eventId] = 0
			continue
		}

		countByEventId.mu.RLock()
		count, isExist := countByEventId.countByUserId[userId]
		countByEventId.mu.RUnlock()

		if !isExist {

			countGroupedByEventId[eventId] = 0
			continue
		}
		countGroupedByEventId[eventId] = count
	}

	return countGroupedByEventId
}

// GetLastUpdatedAt получаем время полсднего обновления
func (mainStore *MainStorage) GetLastUpdatedAt() int64 {

	mainStore.mu.RLock()
	lastUpdatedAt := mainStore.lastUpdatedAt
	mainStore.mu.RUnlock()

	return lastUpdatedAt
}

// UpdateLastUpdatedAt обновляем время последнего обновления
func (mainStore *MainStorage) UpdateLastUpdatedAt() {

	mainStore.mu.Lock()
	mainStore.lastUpdatedAt = functions.GetCurrentTimeStamp()
	mainStore.mu.Unlock()
}
