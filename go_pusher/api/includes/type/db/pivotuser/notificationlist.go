package pivotuser

import (
	"context"
	"encoding/json"
	"fmt"
	"go_pusher/api/system/sharding"
	"math"
	"strings"
)

const userNotificationListTableKey = "notification_list"

// получить информацию о списке пользователей
func GetUserNotificationListFromDb(ctx context.Context, userIdList []int64) ([]map[string]string, error) {

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

		tableNames := getUserNotificationListGroupedTableName(value)

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

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// получаем tableName, в которой хранится информация о пользователе, на основе userID
func getUserNotificationListTableName(userID int64) string {

	return fmt.Sprintf("%s_%d", userNotificationListTableKey, int64(math.Ceil(float64(userID)/1000000)))
}

// создаем список tableName для запросов
func getUserNotificationListGroupedTableName(userIdList []int64) map[string][]int64 {

	groupedUserIdList := make(map[string][]int64, 1)

	for _, value := range userIdList {

		groupedUserIdList[getUserNotificationListTableName(value)] = append(groupedUserIdList[getUserNotificationListTableName(value)], value)
	}

	return groupedUserIdList
}
