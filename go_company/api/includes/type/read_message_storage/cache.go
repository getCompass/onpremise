package readParticipantStorage

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_company/api/includes/type/structures"
	"sync"
)

// -------------------------------------------------------
// данный файл пакета reactions содержит всю логику хранения задач
// на изменение реакций диалогов и тредов
// -------------------------------------------------------

// структура переменной с хранилищем
type ReadMessageQueueStruct struct {
	mu    sync.Mutex
	cache map[string]structures.ReadMessageCacheItem // ключ string - "{$user_id}:{$entity_map}"
}

// создаем store с просмотревшими
func MakeReadMessageStore() *ReadMessageQueueStruct {

	return &ReadMessageQueueStruct{
		cache: make(map[string]structures.ReadMessageCacheItem),
	}
}

// добавить в кеш задачу
func (store *ReadMessageQueueStruct) Add(entityType string, entityMap string, entityMetaId int64, entityKey string, userId int64, messageMap string, messageKey string, entityMessageIndex int64, messageCreatedAt int64, readAt int64, tableShard int, dbShard int, hideReadParticipantList []int64) {

	store.mu.Lock()
	defer store.mu.Unlock()

	key := functions.Int64ToString(userId) + "||" + entityMap

	// если в кеше уже есть запись об индексе сообщения выше - не добавляем такую задачу
	if item, exist := store.cache[key]; exist {

		if item.EntityMessageIndex >= entityMessageIndex {
			return
		}
	}

	store.cache[key] = structures.ReadMessageCacheItem{
		EntityMap:               entityMap,
		EntityType:              entityType,
		EntityMetaId:            entityMetaId,
		EntityKey:               entityKey,
		MessageMap:              messageMap,
		MessageKey:              messageKey,
		EntityMessageIndex:      entityMessageIndex,
		UserId:                  userId,
		ReadAt:                  readAt,
		MessageCreatedAt:        messageCreatedAt,
		TableShard:              tableShard,
		DbShard:                 dbShard,
		HideReadParticipantList: hideReadParticipantList,
	}
}

// GetAndClearCache получаем все записи и очищаем из update кэша
func (store *ReadMessageQueueStruct) GetAndClearCache() map[string]structures.ReadMessageCacheItem {

	store.mu.Lock()
	cache := store.cache
	store.cache = make(map[string]structures.ReadMessageCacheItem)
	store.mu.Unlock()

	return cache
}
