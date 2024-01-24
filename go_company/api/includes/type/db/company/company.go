package dbCloud

// пакет для работы с conversation|thread базами

import (
	"database/sql"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/includes/type/db/company_conversation"
	"go_company/api/includes/type/db/company_thread"
)

// получаем shardKey на основе entityType
func GetDBConn(conversationConn *company_conversation.DbConn, threadConn *company_thread.DbConn, entityType string) *sql.DB {

	switch entityType {
	case "conversation":
		return conversationConn.Conn
	case "thread":
		return threadConn.Conn
	default:
		return nil
	}
}

// открываем транзакцию
func BeginTransaction(conversationConn *company_conversation.DbConn, threadConn *company_thread.DbConn, entityType string) (*sql.Tx, error) {

	conn := GetDBConn(conversationConn, threadConn, entityType)
	transactionItem, err := conn.Begin()
	return transactionItem, err
}

// откатываем транзакцию
func RollbackTransaction(transactionItem *sql.Tx) error {

	return transactionItem.Rollback()
}

// коммитим транзакцию
func CommitTransaction(transactionItem *sql.Tx) error {

	err := transactionItem.Commit()
	if err != nil {

		log.Errorf("Откатили транзакцию %v", err)
		return transactionItem.Rollback()
	}

	return nil
}
