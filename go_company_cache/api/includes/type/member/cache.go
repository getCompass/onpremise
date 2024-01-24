package member

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/includes/type/db/company_data"
	"sync"
)

// основное хранилище
type Storage struct {
	mainStorage *mainStorage
	subStorage  *subStorage
}

type mainStorage struct {
	mu    sync.RWMutex
	cache map[int64]*memberStruct
}

// структура объекта
type memberStruct struct {
	userId     int64
	dateCached int64
	memberRow  *company_data.MemberRow

	// проброс ошибки из БД
	err error
}

func MakeStore() *Storage {

	mainStore := mainStorage{
		mu:    sync.RWMutex{},
		cache: make(map[int64]*memberStruct, 0),
	}
	subStore := subStorage{
		mu:    sync.RWMutex{},
		cache: make(map[int64]chan bool),
	}
	return &Storage{
		mainStorage: &mainStore,
		subStorage:  &subStore,
	}
}

// -------------------------------------------------------
// session interface methods
// -------------------------------------------------------

// получаем из кеша
func (store *mainStorage) getMemberItemFromCache(userId int64) (*memberStruct, bool) {

	// получаем из cache
	store.mu.RLock()
	memberItem, isExist := store.cache[userId]
	store.mu.RUnlock()

	return memberItem, isExist
}

// сохраняем в кэш
func (store *mainStorage) doCacheMemberItem(userId int64, memberRow *company_data.MemberRow) {

	// получаем объект из хранилища
	memberItem := &memberStruct{}

	memberItem.dateCached = functions.GetCurrentTimeStamp()
	memberItem.userId = memberRow.UserId
	memberItem.memberRow = memberRow

	store.mu.Lock()
	_, exist := store.cache[userId]
	if !exist {
		store.cache[userId] = memberItem
	}
	store.mu.Unlock()
}

// удаляем кеш по userId
func (store *mainStorage) deleteFromStore(userId int64) {

	store.mu.Lock()
	defer store.mu.Unlock()

	// удаляем из хранилища
	delete(store.cache, userId)
}

// удаляем кеш по userId
func (store *mainStorage) deleteListFromStore(userIdList []int64) {

	store.mu.Lock()
	defer store.mu.Unlock()

	for _, userId := range userIdList {

		// удаляем из хранилища
		delete(store.cache, userId)
	}
}

// получаем пользователя по userId
func (store *mainStorage) get(userId int64) (*memberStruct, bool) {

	store.mu.RLock()
	defer store.mu.RUnlock()

	// получаем из хранилища
	item, exist := store.cache[userId]

	// если не существует
	if !exist {

		log.Error(fmt.Sprintf("не найден userId в хранилище: %d", userId))
		return nil, false
	}

	return item, true
}

// очистить кэш с пользователями
func (store *mainStorage) clear() {

	store.mu.Lock()
	store.cache = make(map[int64]*memberStruct, 0)
	store.mu.Unlock()
}
