package user_screen_time

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	Isolation "go_rating/api/includes/type/isolation"
	"go_rating/api/includes/type/rating/collecting/pivot"
	"go_rating/api/system"
	"sync"
	"time"
)

const slicePeriod = 2 * time.Hour // удаляем кэши старее этого значения

// агрегатор экранного времени
// собирает и держит в себе экранное время по 15 минут
type userscreentime struct {

	// экранное время разбито по 15 минуткам
	storedScreenTimeBy15Min map[int64]*UserScreenTimeListStruct
	mx                      sync.RWMutex // семафор для хранилища
}

// UserScreenTimeListStruct структура объекта
type UserScreenTimeListStruct struct {
	SpaceId            int64
	CacheAt            int64
	UserScreenTimeList []*UserScreenTimeStruct
}

// UserScreenTimeStruct структура объекта
type UserScreenTimeStruct struct {
	UserID        int64
	ScreenTime    int64
	LocalOnlineAt string
}

// makeUserScreenTime создает экземпляр userscreentime
func makeUserScreenTime(isolation *Isolation.Isolation) *userscreentime {

	mScreenTime := &userscreentime{
		storedScreenTimeBy15Min: map[int64]*UserScreenTimeListStruct{},
		mx:                      sync.RWMutex{},
	}

	go mScreenTime.routine(isolation)
	return mScreenTime
}

// основная рутина жизненного цикла экземпляра userscreentime
func (m *userscreentime) routine(isolation *Isolation.Isolation) {

	Isolation.Inc("userscreentime-life-routine")

	// добавляем экземпляр в коллектор и объявляем вызов unregister колбека
	unregister := pivot.LeaseScreentimeCollector(Isolation.Global()).RegisterSource("userscreentime:"+isolation.GetUniq(), m)
	defer func() {

		unregister()
		Isolation.Dec("userscreentime-life-routine")
	}()

	for {

		select {
		case <-time.After(time.Minute * 15):

			// удаляем все устаревшие записи
			m.purge(time.Now().Unix()-int64(slicePeriod.Seconds()), true)

		case <-isolation.GetContext().Done():

			return
		}
	}
}

// добавляет массив экранное время пользователя в хранилище
func (m *userscreentime) push(spaceId int64, userId int64, screenTime int64, localOnlineAt string) {

	if err := m.pushOne(spaceId, userId, screenTime, localOnlineAt); err != nil {
		log.Errorf("passed bad screen time %s", err.Error())
	}
}

// добавляет экранное время пользователя в хранилище, если еще не добавлено
func (m *userscreentime) pushOne(spaceId int64, userId int64, screenTime int64, localOnlineAt string) error {

	m.mx.Lock()
	defer m.mx.Unlock()

	min15Start := system.Min15Start()
	min15Cache, exist := m.storedScreenTimeBy15Min[min15Start]
	if !exist {

		min15Cache = &UserScreenTimeListStruct{
			SpaceId:            spaceId,
			CacheAt:            min15Start,
			UserScreenTimeList: make([]*UserScreenTimeStruct, 0),
		}
		m.storedScreenTimeBy15Min[min15Start] = min15Cache
	}

	// проверяем записали ли уже активность для этого пользователя за 15-ти минутку в пространстве
	for _, userScreenTime := range min15Cache.UserScreenTimeList {

		// если записали - ничего не делаем
		if userScreenTime.UserID == userId {
			return nil
		}
	}

	userScreenTime := &UserScreenTimeStruct{
		UserID:        userId,
		ScreenTime:    screenTime,
		LocalOnlineAt: localOnlineAt,
	}
	min15Cache.UserScreenTimeList = append(min15Cache.UserScreenTimeList, userScreenTime)

	return nil
}

// CollectScreenTime возвращает все 15-ти минутки
// после сбора данных очищает хранилище
func (m *userscreentime) CollectScreenTime() []*pivot.UserScreenTimeListStruct {

	var output []*pivot.UserScreenTimeListStruct

	m.mx.Lock()
	defer m.mx.Unlock()

	// проходим по всем 15-ти минуткам
	screenTimeSendTill := int64(0)
	for min15Start, storedScreenTime := range m.storedScreenTimeBy15Min {

		// текущий кэш не трогаем
		if min15Start >= system.Min15Start() {
			continue
		}

		// сохраняем до какой максимальной 15-ти минутки почистили
		if min15Start > screenTimeSendTill {
			screenTimeSendTill = min15Start
		}

		userScreenTimeList := make([]*pivot.UserScreenTimeStruct, 0)

		// пробегаемся по всем 15-ти минуткам
		for _, item := range storedScreenTime.UserScreenTimeList {

			userScreenTimeList = append(userScreenTimeList, &pivot.UserScreenTimeStruct{
				UserID:        item.UserID,
				ScreenTime:    item.ScreenTime,
				LocalOnlineAt: item.LocalOnlineAt,
			})
		}

		screenTimeList := &pivot.UserScreenTimeListStruct{
			SpaceId:            storedScreenTime.SpaceId,
			CacheAt:            storedScreenTime.CacheAt,
			UserScreenTimeList: userScreenTimeList,
		}

		output = append(output, screenTimeList)
	}

	// запускаем очистку хранилища если что-то очистили
	if screenTimeSendTill > 0 {

		m.purge(screenTimeSendTill, false)
		log.Infof("screen time was collected till %d", screenTimeSendTill)
	}

	return output
}

// выполняет очистку записей
func (m *userscreentime) purge(screenTimeSendTill int64, needBlock bool) {

	// спорное решение, но позволяет держать код чуть чище в других местах
	// при сборе данных нужно почистить хранилище, не снимая блокировки в основном хранилище
	if needBlock {

		m.mx.Lock()
		defer m.mx.Unlock()
	}

	for min15Start := range m.storedScreenTimeBy15Min {

		if min15Start <= screenTimeSendTill {

			delete(m.storedScreenTimeBy15Min, min15Start)
			log.Infof("screen time for %v was purged", min15Start)
		}
	}
}

// --------------------------------------------
// region методы пакета, работающие с изоляцией
// --------------------------------------------

// Push добавляет экранное время в агрегатор
func Push(isolation *Isolation.Isolation, userId int64, screenTime int64, localOnlineAt string) error {

	mScreenTime := leaseUserScreenTime(isolation)
	if mScreenTime == nil {
		return fmt.Errorf("isolation doesn't have userscreentime instance")
	}

	mScreenTime.push(isolation.GetCompanyId(), userId, screenTime, localOnlineAt)
	return nil
}
