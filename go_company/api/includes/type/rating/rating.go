package rating

import (
	dbRatingMemberDayList "go_company/api/includes/type/db/company_data/rating_member_day_list"
	"go_company/api/includes/type/define"
	"go_company/api/includes/type/rating_utils"
	"sync"
)

type UserRatingByDaysStoreItem struct {
	UserTotalRatingByUserId map[int64]*dbRatingMemberDayList.UserRatingDayRow
	SortUserList            []int64
}

type UserRatingByDaysStore struct {
	Mu    *sync.RWMutex // мьютекс
	Store map[string]*UserRatingByDaysStoreItem
}

// создаем clear UserRatingByDaysStore
func MakeUserRatingByDaysStore() *UserRatingByDaysStore {

	return &UserRatingByDaysStore{
		Store: make(map[string]*UserRatingByDaysStoreItem),
		Mu:    &sync.RWMutex{},
	}
}

// тип — хранилище всех слушателей для всех событий
type Store struct {
	mu     *sync.RWMutex                     // мьютекс
	store  map[int64]map[int64]map[int64]int // хранилище список слушателей
	IsInit bool
}

// создаем clear store
func MakeStore() *Store {

	return &Store{
		store:  make(map[int64]map[int64]map[int64]int),
		IsInit: false,
		mu:     &sync.RWMutex{},
	}
}

// декрементим в хранилище
func (rStore *Store) StorageDec(userId int64, eventId int64, createdAt int64, value int) bool {

	hour := rating_utils.HourStartByTimeAt(createdAt)

	rStore.mu.Lock()

	// после выполнения функции разлочиваем хранилище
	defer rStore.mu.Unlock()

	// если хранилища больше нет
	if _, isExist := rStore.store[hour]; !isExist {
		return false
	}

	// если отсутствуют данные по типу
	if _, isExist := rStore.store[hour][eventId]; !isExist {
		return false
	}

	if rStore.store[hour][eventId][userId] > 0 {
		rStore.store[hour][eventId][userId] -= value
	}

	// если ивент из тех, для которых не нужно менять General
	if _, isExist := define.EventIdListForSkipGeneral[eventId]; isExist {
		return true
	}

	// декрементим если значение больше нуля
	if rStore.store[hour][define.GeneralCounterId][userId] > 0 {
		rStore.store[hour][define.GeneralCounterId][userId] -= value
	}

	return true
}

// добавляем в хранилище
func (rStore *Store) StorageInc(hour int64, userId int64, eventId int64, value int) {

	rStore.mu.Lock()

	// после выполнения функции разлочиваем хранилище
	defer rStore.mu.Unlock()

	if _, isExist := rStore.store[hour]; !isExist {
		rStore.store[hour] = make(map[int64]map[int64]int)
	}

	if _, isExist := rStore.store[hour][eventId]; !isExist {
		rStore.store[hour][eventId] = make(map[int64]int)
	}
	rStore.store[hour][eventId][userId] += value

	// если ивент из тех, для которых не нужно инкрементить General
	if _, isExist := define.EventIdListForSkipGeneral[eventId]; isExist {
		return
	}

	if _, isExist := rStore.store[hour][define.GeneralCounterId]; !isExist {
		rStore.store[hour][define.GeneralCounterId] = make(map[int64]int)
	}

	rStore.store[hour][define.GeneralCounterId][userId] += value
}

// устанавливаем кэш в хранилище рейтинга
func (rStore *Store) GetAndClearStore() map[int64]map[int64]map[int64]int {

	rStore.mu.Lock()

	cache := rStore.store
	rStore.store = make(map[int64]map[int64]map[int64]int)
	rStore.mu.Unlock()
	return cache
}

// чистим кэш
func (rStore *Store) ClearStore() {

	rStore.mu.Lock()
	rStore.store = make(map[int64]map[int64]map[int64]int)
	rStore.mu.Unlock()
}
