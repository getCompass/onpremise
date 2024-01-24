package pivot_user

import (
	"context"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pivot_cache/api/system/sharding"
	"math"
	"strings"
)

const userListTableKey = "user_list"

// GetUserRowFromDb получить информацию о пользователе
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

// GetUserListFromDb получить информацию о списке пользователей
func GetUserListFromDb(ctx context.Context, userIdList []int64) ([]map[string]string, error) {

	// получаем массив названий баз данных на основе user_id пользователя
	shardKeys := getGroupedShardKey(userIdList)

	//создаем список для отсортированных записей
	rowsResult := make([]map[string]string, 0)

	for shardKey, value := range shardKeys {

		// проверяем, что у нас имеется подключение к этой базе
		conn := sharding.Mysql(ctx, shardKey)
		if conn == nil {
			return nil, fmt.Errorf("пришел shard_id: %s для которого не найдено подключение", shardKey)
		}

		tableNames := getUserListGroupedTableName(value)

		for tableName, userList := range tableNames {

			// создаем строку с id пользователей для IN оператора
			userJson, _ := json.Marshal(userList)
			userString := strings.Trim(string(userJson), "[]")

			// выполняем запрос
			sql := fmt.Sprintf("SELECT * FROM `%s` WHERE `user_id` IN (%s) LIMIT ?", tableName, userString)
			rows, err := conn.GetAll(ctx, sql, len(userList))
			if err != nil {
				return nil, fmt.Errorf("неудачный запрос: user_id в базе %s Error: %v", shardKey, err)
			}

			//перебираем записи и отсеиваем невалидные
			for _, row := range rows {

				// если запись не нашлась
				if _, exist := row["user_id"]; !exist {
					continue
				}
				rowsResult = append(rowsResult, row)
			}
		}
	}

	return rowsResult, nil
}

// создаем список tableName для запросов
func getUserListGroupedTableName(userIdList []int64) map[string][]int64 {

	groupedUserIdList := make(map[string][]int64, 1)

	for _, value := range userIdList {

		groupedUserIdList[getUserListTableName(value)] = append(groupedUserIdList[getUserListTableName(value)], value)
	}

	return groupedUserIdList
}

// получаем tableName, в которой хранится информация о пользователе, на основе userID
func getUserListTableName(userID int64) string {

	return fmt.Sprintf("%s_%d", userListTableKey, int64(math.Ceil(float64(userID)/1000000)))
}
