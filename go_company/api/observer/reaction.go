package observer

import (
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/conf"
	dbCloud "go_company/api/includes/type/db/company"
	dbCloudMessageBlockReactionList "go_company/api/includes/type/db/company/message_block_reaction_list"
	dbConversationDynamic "go_company/api/includes/type/db/company_conversation/conversation_dynamic"
	"go_company/api/includes/type/define"
	Isolation "go_company/api/includes/type/isolation"
	reactionStorage "go_company/api/includes/type/reaction_storage"
	"go_company/api/includes/type/sender"
	"go_company/api/includes/type/structures"
	"sync"
)

// структура хранилища реакций
type reactionObserverItem struct {
	EntityMap                string
	EntityType               string
	BlockID                  int64
	ReactionListByMessageMap map[string]map[string]structures.ReactionStruct // ключ string - "{$block_message_index}_{$reaction_index}"
}

// обрабатываем кэш
func handleReactionCache(isolation *Isolation.Isolation, cache map[string]*structures.ReactionCacheItem) {

	groupedByConcatBlockId := make(map[string]reactionObserverItem)

	// пробегаемся по всем реакциям блока диалога/треда
	for messageMap, reactionCacheItem := range cache {

		key := getKeyForObserverItem(reactionCacheItem)
		_, exist := groupedByConcatBlockId[key]
		if !exist {

			groupedByConcatBlockId[key] = reactionObserverItem{
				EntityMap:                reactionCacheItem.EntityMap,
				EntityType:               reactionCacheItem.EntityType,
				BlockID:                  reactionCacheItem.BlockID,
				ReactionListByMessageMap: make(map[string]map[string]structures.ReactionStruct),
			}
		}
		groupedByConcatBlockId[key].ReactionListByMessageMap[messageMap] = reactionCacheItem.ReactionList
	}

	// инициируем новый wait group
	// отдельно для каждой рутины, чтобы если случилась гонка, не упало на WaitGroup.Wait
	wg := sync.WaitGroup{}

	for _, reactionCacheItem := range groupedByConcatBlockId {

		// запускаем отдельную горутину, которая обновит реакции для конкретного блока диалога/треда
		wg.Add(1)
		go func(reactionCacheItem reactionObserverItem) {

			defer wg.Done()
			err := doUpdateReaction(
				isolation,
				reactionCacheItem.EntityMap,
				reactionCacheItem.EntityType,
				reactionCacheItem.BlockID,
				reactionCacheItem.ReactionListByMessageMap)

			if err != nil {
				log.Errorf(fmt.Sprintf("%v", err))
			}
		}(reactionCacheItem)
	}
	wg.Wait()
}

// получаем кэш для обсервера
func getKeyForObserverItem(reactionCacheItem *structures.ReactionCacheItem) string {

	return fmt.Sprintf("%s_%s_%d", reactionCacheItem.EntityMap, reactionCacheItem.EntityType, reactionCacheItem.BlockID)
}

// структура с количеством реакций сообщения
func doUpdateReaction(isolation *Isolation.Isolation, entityMap string, entityType string, blockId int64, reactionListByMessageMap map[string]map[string]structures.ReactionStruct) error {

	// открываем транзакцию
	transactionItem, err := dbCloud.BeginTransaction(isolation.CompanyConversationConn, isolation.CompanyThreadConn, entityType)
	if err != nil {

		log.Errorf(err.Error())
		return err
	}

	messageBlockReactionCountRow, isExist, err := dbCloudMessageBlockReactionList.GetOneForUpdate(isolation.Context, transactionItem, entityType, entityMap, blockId)
	if err != nil {

		log.Errorf(err.Error())
		return dbCloud.RollbackTransaction(transactionItem)
	}
	if !isExist {

		messageBlockReactionCountRow, transactionItem, err = createBlockIfNotExist(transactionItem, isolation, entityType, entityMap, blockId)
		if err != nil {

			log.Errorf(err.Error())
			return dbCloud.RollbackTransaction(transactionItem)
		}
	}

	// проходимся по всем реакциям
	for messageMap, reactionList := range reactionListByMessageMap {

		overflowReactionList := make(map[string]int64)
		for reactionName, v := range reactionList {

			_, isExist := messageBlockReactionCountRow.ReactionData.MessageReactionList[messageMap]
			if !isExist {
				messageBlockReactionCountRow.ReactionData.MessageReactionList[messageMap] = make(map[string]map[int64]int64)
			}

			_, isExist = messageBlockReactionCountRow.ReactionData.MessageReactionList[messageMap][reactionName]
			if !isExist {
				messageBlockReactionCountRow.ReactionData.MessageReactionList[messageMap][reactionName] = make(map[int64]int64)
			}
			for userId, updatedAtMs := range v.AddUserList {

				// если реакций слишком много собираем массив переполнения
				if len(messageBlockReactionCountRow.ReactionData.MessageReactionList[messageMap]) > define.MaxReactionCount {

					_, isExist = overflowReactionList[reactionName]
					if !isExist || overflowReactionList[reactionName] < updatedAtMs {
						overflowReactionList[reactionName] = updatedAtMs
					}
				}
				messageBlockReactionCountRow.ReactionData.MessageReactionList[messageMap][reactionName][userId] = updatedAtMs
			}

			for userId := range v.RemoveUserList {
				delete(messageBlockReactionCountRow.ReactionData.MessageReactionList[messageMap][reactionName], userId)
			}
			if len(messageBlockReactionCountRow.ReactionData.MessageReactionList[messageMap][reactionName]) < 1 {
				delete(messageBlockReactionCountRow.ReactionData.MessageReactionList[messageMap], reactionName)
			}
		}

		// если когда собрали реакции массив все еще переполнен то
		fixOverflow(messageMap, overflowReactionList, messageBlockReactionCountRow)
	}

	err = dbCloudMessageBlockReactionList.UpdateOne(isolation.Context, transactionItem, entityType, entityMap, blockId, messageBlockReactionCountRow.ReactionData)
	if err != nil {

		log.Errorf(err.Error())
		return dbCloud.RollbackTransaction(transactionItem)
	}

	// если сущность диалога
	var reactionsUpdatedVersion = 0
	if entityType == reactionStorage.ConversationEntityType {

		// получаем dynamic-запись на обновление
		dynamicRow, err := dbConversationDynamic.GetOneForUpdate(isolation.Context, isolation.CompanyConversationConn, transactionItem, entityMap)
		if err != nil {

			log.Errorf(err.Error())
			return dbCloud.RollbackTransaction(transactionItem)
		}

		// обновляем временную метку и версию обновления реакций
		reactionsUpdatedVersion = dynamicRow.ReactionsUpdatedVersion + 1
		err = dbConversationDynamic.UpdateReactionsUpdatedData(isolation.Context, isolation.CompanyConversationConn, entityMap, reactionsUpdatedVersion)
		if err != nil {
			return err
		}
	}

	err = dbCloud.CommitTransaction(transactionItem)
	if err != nil {

		log.Errorf(err.Error())
		return err
	}

	err = afterWork(isolation, reactionListByMessageMap, &messageBlockReactionCountRow.ReactionData, reactionsUpdatedVersion, entityType)
	return err
}

// создаем блок если его нет
func createBlockIfNotExist(transactionItem *sql.Tx, isolation *Isolation.Isolation, entityType string, entityMap string, blockId int64) (*dbCloudMessageBlockReactionList.ReactionBlockRow, *sql.Tx, error) {

	_ = transactionItem.Rollback()

	var reactionData = dbCloudMessageBlockReactionList.ReactionDataStruct{
		Version:             1,
		MessageReactionList: make(map[string]map[string]map[int64]int64),
	}
	err := dbCloudMessageBlockReactionList.InsertIgnoreOne(isolation.Context, isolation.CompanyConversationConn, isolation.CompanyThreadConn,
		entityType, entityMap, blockId, reactionData)
	if err != nil {

		log.Errorf(err.Error())
		return nil, transactionItem, err
	}

	transactionItem, _ = dbCloud.BeginTransaction(isolation.CompanyConversationConn, isolation.CompanyThreadConn, entityType)
	messageBlockReactionCountRow, _, err := dbCloudMessageBlockReactionList.GetOneForUpdate(isolation.Context, transactionItem, entityType, entityMap, blockId)
	if err != nil {

		log.Errorf(err.Error())
		return nil, transactionItem, err
	}
	return messageBlockReactionCountRow, transactionItem, nil
}

// исправляем переполнение реакций
func fixOverflow(messageMap string, overflowReactionList map[string]int64, reactionBlockRow *dbCloudMessageBlockReactionList.ReactionBlockRow) {

	// если когда собрали реакции массив все еще переполнен то
	if len(reactionBlockRow.ReactionData.MessageReactionList[messageMap]) > define.MaxReactionCount {

		// получаем количество сколько нам надо удалить реакций
		overflowCount := len(reactionBlockRow.ReactionData.MessageReactionList[messageMap]) - define.MaxReactionCount

		for i := 0; i < overflowCount; i++ {

			var overflowUpdatedAt int64 = 0
			overflowReaction := ""
			for k, v := range overflowReactionList {

				if overflowUpdatedAt < v {
					overflowReaction = k
				}
			}
			delete(reactionBlockRow.ReactionData.MessageReactionList[messageMap], overflowReaction)
			delete(overflowReactionList, overflowReaction)
		}
	}
}

// метод для отправки всех ивентов
func afterWork(isolation *Isolation.Isolation, reactionListByMessageMap map[string]map[string]structures.ReactionStruct, reactionData *dbCloudMessageBlockReactionList.ReactionDataStruct, reactionsUpdatedVersion int, entityType string) error {

	// для каждой выставляемой реакции в отдельном потоке:
	for messageMap, reactionList := range reactionListByMessageMap {

		for reactionName, v := range reactionList {

			senderConfig := isolation.GetGlobalIsolation().GetShardingConfig().Go["sender"]

			go doSendEvent(v, isolation.GetCompanyId(), senderConfig, entityType, len(reactionData.MessageReactionList[messageMap][reactionName]), reactionsUpdatedVersion)
		}
	}
	return nil
}

// функция для отправки события о установке/удалении реакции
// @long - switch .. case
func doSendEvent(reactionItem structures.ReactionStruct, companyId int64, senderConfig conf.GoShardingStruct, entityType string, reactionCount int, reactionsUpdatedVersion int) {

	// формируем дефолтный requestMap
	var reactionIndex = 0
	for _, v := range reactionItem.EventList {

		switch entityType {
		case reactionStorage.ConversationEntityType:

			if v.IsAdd {
				sender.SendActionConversationMessageReactionAdded(companyId, v.WsUserList, v.WsEventVersionList, reactionCount, reactionIndex, reactionsUpdatedVersion, senderConfig)
			} else {
				sender.SendActionConversationMessageReactionRemoved(companyId, v.WsUserList, v.WsEventVersionList, reactionCount, reactionIndex, reactionsUpdatedVersion, senderConfig)
			}
		case reactionStorage.ThreadEntityType:

			if v.IsAdd {
				sender.SendActionThreadMessageReactionAdded(companyId, v.WsUserList, v.WsEventVersionList, reactionCount, reactionIndex, senderConfig)
			} else {
				sender.SendActionThreadMessageReactionRemoved(companyId, v.WsUserList, v.WsEventVersionList, reactionCount, reactionIndex, senderConfig)
			}
		default:
			log.Errorf("[cleaner.go][_doSendEvent] passed incorrect entity type")
		}
		reactionIndex++
	}
}
