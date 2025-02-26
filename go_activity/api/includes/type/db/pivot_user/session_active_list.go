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

const (
	sessionActiveListTableKey = "session_active_list"
	batchSize                 = 1000
)

// структура данных для обновления активности пользователя
type UserSessionActivity struct {
	UserId       int64
	SessionUniq  string
	LastPingWsAt int64
}

// UpdateUserSessionActivity обновляет записи активности сессий
// @long
func UpdateUserSessionActivity(ctx context.Context, activities []UserSessionActivity) error {

	if len(activities) == 0 {
		log.Infof("передан пустой массив activities для обновления")
		return nil
	}

	// группируем записи по шард-картам
	shardMap := make(map[string][]UserSessionActivity)
	for _, activity := range activities {
		shardKey := getShardKey(activity.UserId)
		shardMap[shardKey] = append(shardMap[shardKey], activity)
	}

	// обрабатываем каждую шард-карту
	for shardKey, shardActivities := range shardMap {

		// получаем соединение с базой
		conn := sharding.Mysql(ctx, shardKey)
		if conn == nil {
			log.Errorf("нет подключения к шарду базы: %s", shardKey)
			continue
		}

		// группируем записи по таблицам
		tableMap := make(map[string]map[int64][]UserSessionActivity)
		for _, activity := range shardActivities {

			tableName := getSessionActiveListTableName(activity.UserId)

			if _, exist := tableMap[tableName]; !exist {
				tableMap[tableName] = make(map[int64][]UserSessionActivity)
			}

			lastPingWsAt := activity.LastPingWsAt
			if _, isExist := tableMap[tableName][lastPingWsAt]; !isExist {
				tableMap[tableName][lastPingWsAt] = []UserSessionActivity{}
			}
			tableMap[tableName][lastPingWsAt] = append(tableMap[tableName][lastPingWsAt], activity)
		}

		// обрабатываем каждую таблицу
		for tableName, tblActivitiesByPingWsAt := range tableMap {

			for pingWsAt, tblActivities := range tblActivitiesByPingWsAt {

				// формируем список обновляемых значений
				values := make([]interface{}, 0)
				values = append(values, pingWsAt, time.Now().Unix())

				for i := 0; i < len(tblActivities); i += batchSize {
					end := i + batchSize
					if end > len(tblActivities) {
						end = len(tblActivities)
					}

					batch := tblActivities[i:end]

					whereClauses := make([]string, 0, len(batch))

					for _, activity := range batch {
						values = append(values, activity.SessionUniq)
						whereClauses = append(whereClauses, "?")
					}

					// формируем sql-запрос
					// EXPLAIN (INDEX=PRIMARY)
					query := fmt.Sprintf(
						"UPDATE `%s` SET last_online_at = ?, updated_at = ? WHERE session_uniq IN (%s)",
						tableName, strings.Join(whereClauses, ", "),
					)

					// выполняем запрос
					_, err := conn.FetchQuery(ctx, query, values...)
					if err != nil {
						log.Errorf("ошибка при обновлении данных в таблице %s (шард %s): %v", tableName, shardKey, err)
						continue
					}

					log.Infof("успешно обновлены записи в таблице %s (шард %s): %d записей", tableName, shardKey, len(batch))
				}
			}
		}
	}

	return nil
}

// getSessionActiveListTableName возвращает имя таблицы для пользователя по его id
func getSessionActiveListTableName(userID int64) string {

	return fmt.Sprintf("%s_%d", sessionActiveListTableKey, int64(math.Ceil(float64(userID)/1000000)))
}
