package user_test

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_pivot_cache/api/system/sharding"
	"math"
)

// -------------------------------------------------------
// вспомогательный файл для тестов
// -------------------------------------------------------

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
		"user_id", "npc_type", "partner_id", "created_at", "updated_at", "country_code", "short_description", "status", "full_name", "avatar_file_map", "extra",
	}).AddRow(userId, 1, 0, currentTime, currentTime, 1, "test", 1, "test", "test", "{}")
	mock.AddRow(tableName, rows, userId)

	return nil
}

// удаляем пользователя
func deleteUser(userId int64) {

	dbName := getPivotUserShardKey(userId)

	// подменяем соединение с базой на заглушку
	connection, mock, err := sqlmock.NewWithDSN(dbName)
	if err != nil {
		return
	}
	tableName := getPivotUserUserListTableName(userId)
	mysql.ReplaceConnection(dbName, connection)

	mock.DeleteRow(tableName, userId)
}

// получаем shardKey на основе userID
func getPivotUserShardKey(userID int64) string {

	return fmt.Sprintf("pivot_user_%d0m", int64(math.Ceil(float64(userID)/10000000)))
}

// получаем tableName, в которой хранится информация о пользователе, на основе userID
func getPivotUserUserListTableName(userID int64) string {

	return fmt.Sprintf("user_list_%d", int64(math.Ceil(float64(userID)/1000000)))
}
