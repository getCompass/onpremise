package reactionStorage

import (
	"context"
	dbCloudMessageBlockReactionList "go_company/api/includes/type/db/company/message_block_reaction_list"
	"go_company/api/includes/type/db/company_conversation"
	"go_company/api/includes/type/db/company_thread"
	"go_company/api/includes/type/define"
	"go_company/api/includes/type/structures"
	"sync"
)

// -------------------------------------------------------
// данный файл пакета reactions содержит всю логику хранения задач
// на изменение реакций диалогов и тредов
// -------------------------------------------------------

const (
	ConversationEntityType = "conversation"
	ThreadEntityType       = "thread"
)

// структура переменной с хранилищем
type ReactionQueueStruct struct {
	mu    sync.Mutex
	cache map[string]*structures.ReactionCacheItem // ключ string - "{$message_map}"
}

// создаем store с реакциям
func MakeReactionStore() *ReactionQueueStruct {

	return &ReactionQueueStruct{
		cache: make(map[string]*structures.ReactionCacheItem),
	}
}

// AddAddedReactionTask метод добавляет задачу на обновление реакции
func (store *ReactionQueueStruct) AddAddedReactionTask(ctx context.Context, companyConversationConn *company_conversation.DbConn,
	companyThreadConn *company_thread.DbConn, entityMap string, entityType string, messageMap string, blockId int64, reactionName string, userId int64, updatedAtMs int64, wsUserList interface{}, wsEventVersionList []structures.WsEventVersionItemStruct) (bool, error) {

	reactionBlockRow, isExist, err := dbCloudMessageBlockReactionList.GetOne(ctx, companyConversationConn, companyThreadConn, entityType, entityMap, blockId)
	if err != nil {
		return false, err
	}

	// добавляем реакцию, ставим блокировку
	store.mu.Lock()

	// проверяем существование такого ключа и реакции с таким ключом
	reactionCacheItem := store.createIfNotExistReactionCacheItem(entityMap, entityType, blockId, messageMap)
	reactionItem := createIfNotExistReactionItem(reactionName, reactionCacheItem)

	ok, isOverflow := onAddReaction(userId, updatedAtMs, messageMap, reactionName, &reactionItem, reactionBlockRow, isExist)
	if !ok {

		store.mu.Unlock()
		return isOverflow, nil
	}

	reactionItem.EventList[userId] = struct {
		IsAdd              bool
		WsEventVersionList []structures.WsEventVersionItemStruct
		WsUserList         interface{}
	}{
		IsAdd:              true,
		WsEventVersionList: wsEventVersionList,
		WsUserList:         wsUserList,
	}

	reactionCacheItem.ReactionList[reactionName] = reactionItem
	store.cache[messageMap] = reactionCacheItem

	// снимаем блокировку
	store.mu.Unlock()
	return false, nil
}

// добавляем реакцию
func onAddReaction(userId int64, updatedAtMs int64, messageMap string, reactionName string, reactionItem *structures.ReactionStruct, reactionBlockRow *dbCloudMessageBlockReactionList.ReactionBlockRow, isExist bool) (bool, bool) {

	_, ok := reactionItem.AddUserList[userId]
	if ok {
		return false, false
	}

	deleteAtMs, ok := reactionItem.RemoveUserList[userId]
	if ok {

		if deleteAtMs > updatedAtMs {
			return false, false
		}
		delete(reactionItem.RemoveUserList, userId)
	}

	// если такой блок сущствует то соверши доп проверки
	if isExist {

		_, exist := reactionBlockRow.ReactionData.MessageReactionList[messageMap][reactionName]

		// если такой реакции нет и мы ее переполняем то не надо добавлять
		if !exist && len(reactionBlockRow.ReactionData.MessageReactionList[messageMap]) >= define.MaxReactionCount {
			return false, true
		}
	}

	reactionItem.AddUserList[userId] = updatedAtMs
	return true, false
}

// AddRemovedReactionTask метод добавляет задачу на обновление реакции
func (store *ReactionQueueStruct) AddRemovedReactionTask(entityMap string, entityType string, messageMap string, blockId int64, reactionName string, userId int64, updatedAtMs int64, wsUserList interface{}, wsEventVersionList []structures.WsEventVersionItemStruct) {

	// добавляем реакцию, ставим блокировку
	store.mu.Lock()

	// проверяем существование такого ключа и реакции с таким ключом
	reactionCacheItem := store.createIfNotExistReactionCacheItem(entityMap, entityType, blockId, messageMap)
	reactionItem := createIfNotExistReactionItem(reactionName, reactionCacheItem)

	// выполняем необзодимые действия с реакцией
	ok := onDeleteReaction(userId, updatedAtMs, &reactionItem)

	if !ok {

		store.mu.Unlock()
		return
	}

	reactionItem.EventList[userId] = struct {
		IsAdd              bool
		WsEventVersionList []structures.WsEventVersionItemStruct
		WsUserList         interface{}
	}{
		IsAdd:              false,
		WsEventVersionList: wsEventVersionList,
		WsUserList:         wsUserList,
	}

	reactionCacheItem.ReactionList[reactionName] = reactionItem
	store.cache[messageMap] = reactionCacheItem

	// снимаем блокировку
	store.mu.Unlock()
}

// удаляем реакцию
func onDeleteReaction(userId int64, updateAtMs int64, reactionItem *structures.ReactionStruct) bool {

	_, ok := reactionItem.RemoveUserList[userId]
	if ok {
		return false
	}

	addAtMs, ok := reactionItem.AddUserList[userId]
	if ok {

		if addAtMs > updateAtMs {
			return false
		}
		delete(reactionItem.AddUserList, userId)
	}
	reactionItem.RemoveUserList[userId] = updateAtMs
	return true
}

// создаем ключ для реакции
func (store *ReactionQueueStruct) createIfNotExistReactionCacheItem(entityMap string, entityType string, blockId int64, messageMap string) *structures.ReactionCacheItem {

	// проверяем существование такого ключа
	reactionCacheItem, exist := store.cache[messageMap]
	if !exist {

		// если не существует, то создаем
		reactionCacheItem = &structures.ReactionCacheItem{
			EntityMap:    entityMap,
			EntityType:   entityType,
			BlockID:      blockId,
			ReactionList: make(map[string]structures.ReactionStruct),
		}
	}

	return reactionCacheItem
}

// получаем реакцию
func createIfNotExistReactionItem(reactionName string, reactionCacheItem *structures.ReactionCacheItem) structures.ReactionStruct {

	// проверяем существование реакции с таким ключом
	reactionItem, exist := reactionCacheItem.ReactionList[reactionName]
	if !exist {

		// если не существует, то создаем
		reactionItem = structures.ReactionStruct{
			AddUserList:    make(map[int64]int64),
			RemoveUserList: make(map[int64]int64),
			EventList:      make(map[int64]structures.WsEventStruct),
		}
	}

	return reactionItem
}

// GetAndClearCache получаем все записи и очищаем из update кэша
func (store *ReactionQueueStruct) GetAndClearCache() map[string]*structures.ReactionCacheItem {

	// ВАЖНО: случай когда поинтер переключился но реакция начала в него ставиться, ждем пока разлочится кэш
	// обязательно обычный лок, так как нам нужно чтобы кэш был полностью свободен и к нему точно никто не ждет доступа
	store.mu.Lock()
	cache := store.cache
	store.cache = make(map[string]*structures.ReactionCacheItem)
	store.mu.Unlock()

	return cache
}
