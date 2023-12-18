package session_test

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_pivot_cache/api/system/sharding"
	"math"
)

// -------------------------------------------------------
// вспомогательный файл для тестов
// -------------------------------------------------------

// функция для добавления пользователя в базу кластера
func addMockActiveUserSession(sessionUniq string, userId int64, ipAddress string) error {

	// получаем текущее время
	currentTime := functions.GetCurrentTimeStamp()

	dbName := getPivotUserShardKey(userId)
	tableName := getPivotUserSecurityUserSessionActiveListTableName(userId)

	// подменяем соединение с базой на заглушку
	connection, mock, err := sqlmock.NewWithDSN(dbName)
	if err != nil {
		return err
	}
	sharding.ReplaceConnection(dbName, connection)

	// подставляем значение заглушки
	rows := sqlmock.NewRows([]string{
		"session_uniq", "user_id", "created_at", "updated_at", "login_at", "ip_address", "user_agent", "extra",
	}).AddRow(sessionUniq, userId, currentTime, currentTime, currentTime, ipAddress, "", "{}")
	mock.AddRow(tableName, rows, sessionUniq)

	return nil
}

// получаем tableName на основе tableID
func getPivotUserSecurityUserSessionActiveListTableName(userID int64) string {

	return fmt.Sprintf("session_active_list_%d", int64(math.Ceil(float64(userID)/1000000)))
}

// функция для добавления пользователя в базу кластера
func addMockUser(userId int64) error {

	// получаем текущее время
	currentTime := functions.GetCurrentTimeStamp()

	dbName := getPivotUserShardKey(userId)
	tableName := getPivotUserUserListTableName(userId)

	// подменяем соединение с базой на заглушку
	connection, mock, err := sqlmock.NewWithDSN(dbName)
	if err != nil {
		return err
	}
	sharding.ReplaceConnection(dbName, connection)

	// подставляем значение заглушки
	rows := sqlmock.NewRows([]string{
		"user_id", "npc_type", "created_at", "updated_at", "country_code", "full_name", "full_name_updated_at", "avatar_file_map", "extra",
	}).AddRow(userId, 1, currentTime, currentTime, 1, "test", 1, "test", "{}")
	mock.AddRow(tableName, rows, userId)

	return nil
}

// получаем shardKey на основе userID
func getPivotUserShardKey(userID int64) string {

	return fmt.Sprintf("pivot_user_%d0m", int64(math.Ceil(float64(userID)/10000000)))
}

// получаем tableName, в которой хранится информация о пользователе, на основе userID
func getPivotUserUserListTableName(userID int64) string {

	return fmt.Sprintf("user_list_%d", int64(math.Ceil(float64(userID)/1000000)))
}
