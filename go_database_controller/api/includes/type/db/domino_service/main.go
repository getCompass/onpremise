package domino_service

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_database_controller/api/system/sharding"
)

const dbKey = "domino_service"

type TransactionStruct struct {
	transaction *mysql.TransactionStruct
}

// BeginTransaction открываем транзакцию
func BeginTransaction(ctx context.Context) (*TransactionStruct, error) {

	conn := sharding.Mysql(ctx, GetDbName())
	if conn == nil {
		return &TransactionStruct{}, fmt.Errorf("database error")
	}

	transaction, err := conn.BeginTransaction()
	if err != nil {
		return &TransactionStruct{}, err
	}
	return &TransactionStruct{
		transaction: &transaction,
	}, nil
}

// CommitTransaction коммитим транзакцию
func (tx *TransactionStruct) CommitTransaction() error {

	err := tx.transaction.Commit()
	if err != nil {

		log.Errorf("Откатили транзакцию %v", err)
		err = tx.transaction.Rollback()
	}

	return err
}

// RollbackTransaction коммитим транзакцию
func (tx *TransactionStruct) RollbackTransaction() error {

	return tx.transaction.Rollback()
}

// GetDbName получить имя базы данных
func GetDbName() string {

	return dbKey
}
