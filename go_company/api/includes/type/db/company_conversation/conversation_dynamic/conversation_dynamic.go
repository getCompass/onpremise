package dbConversationDynamic

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей количество реакций
// -------------------------------------------------------

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_company/api/includes/type/db/company_conversation"
)

type DynamicRow struct {
	ReactionsUpdatedAt      int
	ReactionsUpdatedVersion int
}

type LastReadMessage struct {
	MessageMap               string          `json:"message_map"`
	ConversationMessageIndex int64           `json:"conversation_message_index"`
	ReadParticipants         map[int64]int64 `json:"read_participants"`
}

const tableKeyDynamic = "conversation_dynamic"

// GetOneForUpdate получить запись на обновление
func GetOneForUpdate(ctx context.Context, dbConn *company_conversation.DbConn, transactionItem *sql.Tx, conversationMap string) (*DynamicRow, error) {

	dynamicRow := &DynamicRow{}

	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("SELECT reactions_updated_at, reactions_updated_version FROM `%s` WHERE `conversation_map` = ? LIMIT %d", getTableName(), 1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := transactionItem.QueryRowContext(queryCtx, query, conversationMap).Scan(&dynamicRow.ReactionsUpdatedAt, &dynamicRow.ReactionsUpdatedVersion)
	if err != nil {
		return nil, err
	}

	return dynamicRow, nil
}

// UpdateReactionsUpdatedData обновить временную метку и версию обновления реакций
func UpdateReactionsUpdatedData(ctx context.Context, dbConn *company_conversation.DbConn, conversationMap string, reactionsUpdatedVersion int) error {

	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	// nosemgrep
	query := fmt.Sprintf("UPDATE `%s` SET `reactions_updated_at` = ?, `reactions_updated_version` = ? WHERE `conversation_map` = ? LIMIT %d", getTableName(), 1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err := dbConn.Conn.ExecContext(queryCtx, query, functions.GetCurrentTimeStamp(), reactionsUpdatedVersion, conversationMap)
	return err
}

// GetLastReadMessage получить последнее сообщение чата
func GetLastReadMessageForUpdate(ctx context.Context, tx *sql.Tx, conversationMap string) (*LastReadMessage, error) {

	var lastReadMessageRaw []byte

	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("SELECT `last_read_message` FROM `%s` WHERE `conversation_map` = ? LIMIT ? FOR UPDATE", getTableName())

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := tx.QueryRowContext(queryCtx, query, conversationMap, 1).Scan(&lastReadMessageRaw)

	if err != nil {
		return nil, err
	}

	lastReadMessage := &LastReadMessage{}

	err = json.Unmarshal(lastReadMessageRaw, &lastReadMessage)

	if err != nil {
		return nil, err
	}

	return lastReadMessage, nil

}

// UpdateLastMessage обновить последнее сообщение в dynamic
func UpdateLastMessage(ctx context.Context, tx *sql.Tx, conversationMap string, lastReadMessage *LastReadMessage) error {

	lastReadMessageRaw, err := json.Marshal(lastReadMessage)

	if err != nil {
		return err
	}

	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	// nosemgrep
	query := fmt.Sprintf("UPDATE `%s` SET `last_read_message` = ?, `updated_at` = ? WHERE `conversation_map` = ? LIMIT ?", getTableName())

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err = tx.ExecContext(queryCtx, query, lastReadMessageRaw, functions.GetCurrentTimeStamp(), conversationMap, 1)
	return err
}

// получаем названи таблицы
func getTableName() string {

	return tableKeyDynamic
}
