package pivot_user

import (
	"context"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pivot_cache/api/system/sharding"
	"strings"
	"time"
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

// обновить last_online_at для сессии
func UpdateLastOnlineAt(shardId string, tableId string, sessionUniqList []string) error {

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// проверяем, что у нас имеется подключение к необходимой базе данных, где хранится сессия
	shardKey := getCustomShardKey(shardId)
	conn := sharding.Mysql(ctx, shardKey)
	if conn == nil {
		return fmt.Errorf("пришел shard_id: %s для которого не найдено подключение", shardKey)
	}

	// создаем строку с uniq сессий для IN оператора
	sessionJson, _ := json.Marshal(sessionUniqList)
	sessionString := strings.Trim(string(sessionJson), "[]")

	// получаем начало текущей минуты
	startOfMinute := time.Now().Truncate(time.Minute).Unix()

	// обновляем запись в таблице
	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("UPDATE `%s` SET `updated_at` = ?, `last_online_at` = ?  WHERE `session_uniq` IN (%s) LIMIT ?",
		getUserSessionActiveListTableName(tableId), sessionString)
	_, err := conn.Update(ctx, query, functions.GetCurrentTimeStamp(), startOfMinute, len(sessionUniqList))
	if err != nil {

		log.Errorf(err.Error())
		return err
	}

	return nil
}
