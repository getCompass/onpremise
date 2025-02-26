package usercache

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"sync"
)

// инициализируем кэш с последними использованиями пользователей
var lastUserUsedStore sync.Map

// получаем последнее использование пользователя из кэша
func getLastUserUsed(userId int64) int64 {

	value, exist := lastUserUsedStore.Load(userId)
	if !exist {
		return 0
	}
	return value.(int64)
}

// обновляем последнее использование пользователя
func setLastUserUsed(userId int64) {

	timeAt := functions.GetCurrentTimeStamp()
	lastUserUsedStore.Store(userId, timeAt)
}

// сбрасывает кэш недавно использованных пользователей
func resetLastUsed() {

	lastUserUsedStore = sync.Map{}
}
