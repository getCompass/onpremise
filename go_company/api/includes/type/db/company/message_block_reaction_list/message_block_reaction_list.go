package dbCloudMessageBlockReactionList

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей количество реакций
// -------------------------------------------------------

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	dbCloud "go_company/api/includes/type/db/company"
	"go_company/api/includes/type/db/company_conversation"
	"go_company/api/includes/type/db/company_thread"
)

const tableKey = "message_block_reaction_list"

// структура, описывающая елемент поля reaction_data таблицы message_block_reaction_count
type ReactionDataStruct struct {
	Version             int                                   `json:"version"`
	MessageReactionList map[string]map[string]map[int64]int64 `json:"message_reaction_list"`
}

type ReactionBlockRow struct {
	BlockId      int
	ReactionData ReactionDataStruct
}

// получить запись на обновление
func GetOne(ctx context.Context, conversationConn *company_conversation.DbConn, threadConn *company_thread.DbConn, entityType string, entityMap string,
	blockID int64) (*ReactionBlockRow, bool, error) {

	shardKey := dbCloud.GetDBConn(conversationConn, threadConn, entityType)

	reactionBlockRow := &ReactionBlockRow{}
	var reactionDataJson []byte

	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("SELECT block_id, reaction_data FROM `%s` WHERE `%s` = ? AND `block_id` = ? LIMIT %d", getTableName(), getEntityMapName(entityType), 1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := shardKey.QueryRowContext(queryCtx, query, entityMap, blockID).Scan(&reactionBlockRow.BlockId, &reactionDataJson)
	if err != nil {

		if err == sql.ErrNoRows {
			return nil, false, nil
		}

		return nil, false, err
	}

	err = go_base_frame.Json.Unmarshal(reactionDataJson, &reactionBlockRow.ReactionData)
	if err != nil {
		return nil, false, err
	}
	return reactionBlockRow, true, nil
}

// получить запись на обновление
func GetOneForUpdate(ctx context.Context, transactionItem *sql.Tx, entityType string, entityMap string, blockID int64) (*ReactionBlockRow, bool, error) {

	reactionBlockRow := &ReactionBlockRow{}
	var reactionDataJson []byte

	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("SELECT block_id, reaction_data FROM `%s` WHERE `%s` = ? AND `block_id` = ? LIMIT %d", getTableName(), getEntityMapName(entityType), 1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := transactionItem.QueryRowContext(queryCtx, query, entityMap, blockID).Scan(&reactionBlockRow.BlockId, &reactionDataJson)
	if err != nil {

		if err == sql.ErrNoRows {
			return nil, false, nil
		}

		return nil, false, err
	}

	err = go_base_frame.Json.Unmarshal(reactionDataJson, &reactionBlockRow.ReactionData)
	if err != nil {
		return nil, false, err
	}
	return reactionBlockRow, false, nil
}

// обновить запись
func UpdateOne(ctx context.Context, transactionItem *sql.Tx, entityType string, entityMap string, blockID int64, reactionData interface{}) error {

	// запаковываем reactionData в JSON
	jsonedReactionData, err := go_base_frame.Json.Marshal(reactionData)
	if err != nil {
		return fmt.Errorf("[cleaner.go][InitCleanerGoroutine] Не удалось запаковать в JSON: %+v\r\nОшибка: %v", reactionData, err)
	}

	// совершаем запрос
	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("UPDATE `%s` SET `updated_at` = ?, `reaction_data` = ?  WHERE `%s` = ? AND `block_id` = ? LIMIT %d", getTableName(),
		getEntityMapName(entityType), 1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err = transactionItem.ExecContext(queryCtx, query, functions.GetCurrentTimeStamp(), jsonedReactionData, entityMap, blockID)
	return err
}

// создать запись
func InsertIgnoreOne(ctx context.Context, conversationConn *company_conversation.DbConn, threadConn *company_thread.DbConn, entityType string, entityMap string,
	blockID int64, reactionData interface{}) error {

	// запаковываем reactionData в JSON
	jsonedReactionData, err := go_base_frame.Json.Marshal(reactionData)
	if err != nil {
		return fmt.Errorf("[cleaner.go][InitCleanerGoroutine] Не удалось запаковать в JSON: %+v\r\nОшибка: %v", reactionData, err)
	}

	shardKey := dbCloud.GetDBConn(conversationConn, threadConn, entityType)

	query := fmt.Sprintf("INSERT IGNORE INTO `%s` (`%s`, `block_id`, `created_at`, `updated_at`, `reaction_data`) VALUES (?,?,?,?,?)",
		getTableName(), getEntityMapName(entityType))

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err = shardKey.ExecContext(queryCtx, query, entityMap, blockID, functions.GetCurrentTimeStamp(), 0, jsonedReactionData)
	return err
}

// -------------------------------------------------------
// PROTECTED METHODS
// --------------------------------------------------------

// получить название map сущности
func getEntityMapName(entityType string) string {

	return fmt.Sprintf("%s_map", entityType)
}

// получаем названи таблицы
func getTableName() string {

	return fmt.Sprintf("%s", tableKey)
}
