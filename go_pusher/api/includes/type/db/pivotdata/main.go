package pivotdata

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_pusher/api/system/sharding"
)

const DbKey = "pivot_data"

// открываем транзакцию
func BeginTransaction(ctx context.Context) (mysql.TransactionStruct, error) {

	shardKey := DbKey

	// получаем объект подключения к базе данных
	databaseConnection := sharding.Mysql(ctx, shardKey)
	if databaseConnection == nil {
		return mysql.TransactionStruct{}, fmt.Errorf("wrong shard_id: %s", shardKey)
	}

	// начинаем транзакцию
	tx, err := databaseConnection.BeginTransaction()

	return tx, err
}

// коммитим транзакцию
func CommitTransaction(tx mysql.TransactionStruct) bool {

	// завершаем транзакцию
	if result := tx.Commit(); result != nil {

		_ = tx.Rollback()
		return false
	}

	return true
}
