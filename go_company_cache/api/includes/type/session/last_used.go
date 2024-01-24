package session

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
)

// получаем последнее использование сессии по session_uniq
func (store *Storage) getLastSessionUsed(sessionUniq string) int64 {

	store.lastSessionUsedStore.mu.Lock()
	defer store.lastSessionUsedStore.mu.Unlock()

	value, exist := store.lastSessionUsedStore.cache[sessionUniq]
	if !exist {
		return 0
	}
	return value
}

// обновляем полсденее использование сессии по session_uniq
func (store *Storage) setLastSessionUsed(sessionUniq string) {

	store.lastSessionUsedStore.mu.Lock()
	defer store.lastSessionUsedStore.mu.Unlock()

	timeAt := functions.GetCurrentTimeStamp()
	store.lastSessionUsedStore.cache[sessionUniq] = timeAt
}
