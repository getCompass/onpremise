package pivot_user

import (
	"context"
	"fmt"
	"go_pivot_cache/api/system/sharding"
)

const userSessionActiveListTableKey = "session_active_list"

// получаем сессию по ее uniq
func GetActiveSessionRow(ctx context.Context, shardId string, tableId string, sessionUniq string) (map[string]string, error) {

	// проверяем, что у нас имеется подключение к необходимой базе данных, где хранится сессия
	shardKey := getCustomShardKey(shardId)
	conn := sharding.Mysql(ctx, shardKey)
	if conn == nil {
		return nil, fmt.Errorf("пришел shard_id: %s для которого не найдено подключение", shardKey)
	}

	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `session_uniq` = ? LIMIT ?", getUserSessionActiveListTableName(tableId))
	row, err := conn.FetchQuery(ctx, query, sessionUniq, 1)
	if err != nil {
		return nil, fmt.Errorf("неудачный запрос: %s в базу %s Error: %v", sessionUniq, shardKey, err)
	}

	if _, exist := row["user_id"]; !exist {
		return nil, nil
	}

	return row, nil
}

// получаем tableName на основе userId
func getUserSessionActiveListTableName(tableId string) string {

	return fmt.Sprintf("%s_%s", userSessionActiveListTableKey, tableId)
}

// получаем shardKey на основе userId
func getCustomShardKey(shardId string) string {

	return fmt.Sprintf("%s_%s", dbKey, shardId)
}
