package session

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pivot_cache/api/includes/type/db/pivot_user"
	"sync"
)

// инициализируем кэш с последними использованиями сессий
var lastSessionUsedStore sync.Map

const (
	needUpdateLastOnlineExpireTime = 60   // время протухания, когда нет необходимости обновлять last_online_at
	forUpdateLimit                 = 1000 // лимит сколько сессий обновляем за раз
)

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

// обновляем last_online_at для списка сессий
// @long
func updateLastOnlineAt() {

	updateSessionUniqList := getListFromCache()

	for shardId, uniqListByTableId := range updateSessionUniqList {

		for tableId, sessionUniqList := range uniqListByTableId {

			cutBegin := 0
			limit := forUpdateLimit
			if limit > len(sessionUniqList) {
				limit = len(sessionUniqList)
			}
			for {

				cutEnd := cutBegin + limit
				if cutEnd > len(sessionUniqList) {
					cutEnd = len(sessionUniqList)
				}

				sessionUniqListChunk := sessionUniqList[cutBegin:cutEnd]

				// обновляем last_online_at сессии
				err := pivot_user.UpdateLastOnlineAt(shardId, tableId, sessionUniqListChunk)
				if err != nil {
					log.Errorf(err.Error())
				}

				if len(sessionUniqListChunk) < forUpdateLimit {
					break
				}

				cutBegin = cutBegin + forUpdateLimit
			}
		}
	}
}

// получаем список сессий из кэша
// @long
func getListFromCache() map[string]map[string][]string {

	updateSessionUniqList := make(map[string]map[string][]string, 0)

	lastSessionUsedStore.Range(func(key, value interface{}) bool {

		lastUsedAt := value.(int64)
		sessionUniq := key.(string)

		// если last_used_at давно не обновлялся - то пропускаем
		if lastUsedAt < (functions.GetCurrentTimeStamp() - needUpdateLastOnlineExpireTime) {
			return true
		}

		sessionItem, exist := mainSessionStore.cache[sessionUniq]
		if !exist {
			return true
		}

		shardId := sessionItem.shardID
		tableId := sessionItem.tableID

		// собираем сессии по шарду
		if _, isExist := updateSessionUniqList[shardId]; !isExist {
			updateSessionUniqList[shardId] = make(map[string][]string)
		}
		if _, isExist := updateSessionUniqList[shardId][tableId]; !isExist {
			updateSessionUniqList[shardId][tableId] = []string{}
		}
		updateSessionUniqList[shardId][tableId] = append(
			updateSessionUniqList[shardId][tableId], sessionUniq,
		)

		return true
	})

	return updateSessionUniqList
}
