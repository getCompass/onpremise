package company_conversation

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей количество реакций
// -------------------------------------------------------

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
)

type DynamicRow struct {
	ReactionsUpdatedAt      int
	ReactionsUpdatedVersion int
}

const tableKeyDynamic = "conversation_dynamic"

// UpdateReactionsUpdatedData обновить временную метку и версию обновления реакций
func (dbConn *DbConn) UpdateReactionsUpdatedData(ctx context.Context, conversationMap string, reactionsUpdatedVersion int) error {

	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("UPDATE `%s` SET `reactions_updated_at` = ?, `reactions_updated_version` = ? WHERE `conversation_map` = ? LIMIT %d", getTableName(), 1)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	_, err := dbConn.Conn.ExecContext(queryCtx, query, functions.GetCurrentTimeStamp(), reactionsUpdatedVersion, conversationMap)
	return err
}

// GetOneForUpdate получить запись на обновление
func (dbConn *DbConn) GetOneForUpdate(ctx context.Context, transactionItem *sql.Tx, conversationMap string) (*DynamicRow, error) {

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

// получаем названи таблицы
func getTableName() string {

	return fmt.Sprintf("%s", tableKeyDynamic)
}
