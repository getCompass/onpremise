package pivot_user

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_activity/api/system/sharding"
	"math"
	"strings"
	"time"
)

const userListTableKey = "user_activity_list"

// GetUserRowFromDb получить информацию об активности пользователя
func GetUserRowFromDb(ctx context.Context, userID int64) (map[string]string, error) {

	// получаем название базы данных на основе user_id пользователя
	shardKey := getShardKey(userID)

	// проверяем, что у нас имеется подключение к этой базе
	log.Infof("получаем соединение к базе для пользователя = %v", userID)
	conn := sharding.Mysql(ctx, shardKey)
	if conn == nil {
		return nil, fmt.Errorf("пришел shard_id: %s для которого не найдено подключение", shardKey)
	}

	// осуществляем запрос
	log.Infof("осуществляем запрос для пользователя = %v", userID)
	row, err := conn.FetchQuery(ctx, fmt.Sprintf("SELECT * FROM `%s` WHERE `user_id` = ? LIMIT ?", getUserListTableName(userID)), userID, 1)
	if err != nil {
		return nil, fmt.Errorf("неудачный запрос: user_id %d в базе %s Error: %v", userID, shardKey, err)
	}

	log.Infof("запрос выполнен для пользователя = %v. Row %d", userID, row)

	// если запись не нашлась
	if _, exist := row["user_id"]; !exist {
		return nil, nil
	}

	return row, nil
}

// получаем tableName, в которой хранится информация о пользователе, на основе userID
func getUserListTableName(userID int64) string {

	return fmt.Sprintf("%s_%d", userListTableKey, int64(math.Ceil(float64(userID)/1000000)))
}

type UserAddedActivity struct {
	UserId       int64
	LastPingWsAt int64
}

// InsertOrUpdateUserActivities записываем
func InsertOrUpdateUserActivities(ctx context.Context, activities []UserAddedActivity) error {

	if len(activities) == 0 {

		log.Infof("Передан пустой массив activities")
		return nil
	}

	const batchSize = 1000 // максимальный размер одной пачки

	shardMap := make(map[string][]UserAddedActivity)
	for _, a := range activities {
		shardKey := getShardKey(a.UserId)
		shardMap[shardKey] = append(shardMap[shardKey], a)
	}

	for shardKey, shardActivities := range shardMap {
		conn := sharding.Mysql(ctx, shardKey)
		if conn == nil {
			log.Errorf("Нет подключения к шарду: %s", shardKey)
			continue
		}

		tableMap := make(map[string][]UserAddedActivity)
		for _, a := range shardActivities {
			tableName := getUserListTableName(a.UserId)
			tableMap[tableName] = append(tableMap[tableName], a)
		}

		for tableName, tblActivities := range tableMap {
			for i := 0; i < len(tblActivities); i += batchSize {
				end := i + batchSize
				if end > len(tblActivities) {
					end = len(tblActivities)
				}

				batch := tblActivities[i:end]

				// формируем INSERT ... ON DUPLICATE KEY UPDATE запрос
				query := fmt.Sprintf(
					"INSERT INTO `%s` (user_id, status, created_at, updated_at, last_ws_ping_at) VALUES ",
					tableName,
				)
				vals := []interface{}{}
				now := time.Now().Unix()

				for _, activity := range batch {
					query += "(?, ?, ?, ?, ?),"
					vals = append(vals, activity.UserId, 0, now, now, activity.LastPingWsAt) // status = 0
				}
				query = strings.TrimSuffix(query, ",") // удаляем лишнюю запятую
				query += ` ON DUPLICATE KEY UPDATE 
                            status=VALUES(status), 
                            updated_at=VALUES(updated_at), 
                            last_ws_ping_at=VALUES(last_ws_ping_at)`

				// выполняем запрос с использованием FetchQuery
				_, err := conn.FetchQuery(ctx, query, vals...)
				if err != nil {
					log.Errorf("Ошибка при обновлении данных в таблице %s (шард %s): %v", tableName, shardKey, err)
					continue
				}
			}
		}
	}

	return nil
}
