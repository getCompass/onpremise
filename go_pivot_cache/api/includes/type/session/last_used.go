package session

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"sync"
)

// инициализируем кэш с последними использованиями сессий
var lastSessionUsedStore sync.Map

// получаем последнее использование сессии по session_uniq
func getLastSessionUsed(sessionUniq string) int64 {

	value, exist := lastSessionUsedStore.Load(sessionUniq)
	if !exist {
		return 0
	}
	return value.(int64)
}

// обновляем полсденее использование сессии по session_uniq
func setLastSessionUsed(sessionUniq string) {

	timeAt := functions.GetCurrentTimeStamp()
	lastSessionUsedStore.Store(sessionUniq, timeAt)
}

// сбрасывает кэш недавно использованных сессий
func resetLastUsed() {

	lastSessionUsedStore = sync.Map{}
}
