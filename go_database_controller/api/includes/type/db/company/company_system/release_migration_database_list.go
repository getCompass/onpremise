package company_system

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_database_controller/api/system/sharding"
)

const releaseTableKey = "migration_release_database_list"

func GetReleaseMigrationDatabase(ctx context.Context, credentials *sharding.DbCredentials, dbName string) (*MigrationDatabaseStruct, error) {

	// проверяем, что у нас имеется подключение к необходимой базе данных
	conn := sharding.MysqlWithCredentials(ctx, dbKey, credentials)
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `full_database_name` = ? LIMIT ?", releaseTableKey)
	row, err := conn.FetchQuery(ctx, query, dbName, 1)
	if err != nil {
		return nil, fmt.Errorf("неудачный запрос: %s в базу %s Error: %v", query, dbKey, err)
	}

	return makeMigrationReleaseDatabaseStruct(
		row["full_database_name"],
		row["database_name"],
		functions.StringToInt(row["is_completed"]),
		functions.StringToInt(row["current_version"]),
		functions.StringToInt(row["previous_version"]),
		functions.StringToInt(row["expected_version"]),
		functions.StringToInt(row["highest_version"]),
		functions.StringToInt(row["last_migrated_type"]),
		functions.StringToInt(row["last_migrated_at"]),
		row["last_migrated_type"],
		functions.StringToInt64(row["created_at"])), nil
}

func InsertReleaseMigrationDatabase(ctx context.Context, credentials *sharding.DbCredentials, shardDbName string, dbName string) (*MigrationDatabaseStruct, error) {

	// проверяем, что у нас имеется подключение к необходимой базе данных
	conn := sharding.MysqlWithCredentials(ctx, dbKey, credentials)
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	insert := map[string]interface{}{}

	migrationDatabaseStruct := &MigrationDatabaseStruct{
		FullDatabaseName: shardDbName,
		DatabaseName:     dbName,
		IsCompleted:      1,
		CurrentVersion:   0,
		PreviousVersion:  0,
		ExpectedVersion:  0,
		HighestVersion:   0,
		LastMigratedType: 0,
		LastMigratedAt:   0,
		LastMigratedFile: "",
		CreatedAt:        functions.GetCurrentTimeStamp(),
	}

	insert["full_database_name"] = migrationDatabaseStruct.FullDatabaseName
	insert["database_name"] = migrationDatabaseStruct.DatabaseName
	insert["is_completed"] = migrationDatabaseStruct.IsCompleted
	insert["current_version"] = migrationDatabaseStruct.CurrentVersion
	insert["previous_version"] = migrationDatabaseStruct.PreviousVersion
	insert["expected_version"] = migrationDatabaseStruct.ExpectedVersion
	insert["highest_version"] = migrationDatabaseStruct.HighestVersion
	insert["last_migrated_type"] = migrationDatabaseStruct.LastMigratedType
	insert["last_migrated_at"] = migrationDatabaseStruct.LastMigratedAt
	insert["last_migrated_file"] = migrationDatabaseStruct.LastMigratedFile
	insert["created_at"] = migrationDatabaseStruct.CreatedAt

	_, err := conn.Insert(ctx, releaseTableKey, insert, false)
	if err != nil {
		return nil, err
	}

	return migrationDatabaseStruct, err
}

func UpdateStartRelease(ctx context.Context, credentials *sharding.DbCredentials, dbName string, isCompleted int, expectedVersion int, lastMigratedType int, lastMigratedAt int64, lastMigratedFile string) error {

	// проверяем, что у нас имеется подключение к необходимой базе данных
	conn := sharding.MysqlWithCredentials(ctx, dbKey, credentials)
	if conn == nil {
		return fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	// совершаем запрос
	query := fmt.Sprintf("UPDATE `%s` SET `is_completed` = ?, `expected_version` = ?, `last_migrated_type` = ?, `last_migrated_at` = ?, "+
		"`last_migrated_file` = ?  WHERE `full_database_name` = ? LIMIT %d", releaseTableKey, 1)

	_, err := conn.Update(ctx, query, isCompleted, expectedVersion, lastMigratedType, lastMigratedAt, lastMigratedFile, dbName)
	if err != nil {
		return err
	}
	return nil
}

func UpdateEndRelease(ctx context.Context, credentials *sharding.DbCredentials, dbName string, isCompleted int, previousVersion int, currentVersion int, highestVersion int) error {

	// проверяем, что у нас имеется подключение к необходимой базе данных
	conn := sharding.MysqlWithCredentials(ctx, dbKey, credentials)
	if conn == nil {
		return fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	// совершаем запрос
	query := fmt.Sprintf("UPDATE `%s` SET `is_completed` = ?, `previous_version` = ?, `current_version` = ?, `highest_version` = ? "+
		"WHERE `full_database_name` = ? LIMIT %d", releaseTableKey, 1)

	_, err := conn.Update(ctx, query, isCompleted, previousVersion, currentVersion, highestVersion, dbName)
	if err != nil {
		return err
	}
	return nil
}

func makeMigrationReleaseDatabaseStruct(shardDbName string, dbName string, isCompleted int, currentVersion int, previousVersion int,
	expectedVersion int, highestVersion int, lastMigratedType int, lastMigratedAt int, lastMigratedFile string, createdAt int64) *MigrationDatabaseStruct {

	return &MigrationDatabaseStruct{
		FullDatabaseName: shardDbName,
		DatabaseName:     dbName,
		IsCompleted:      isCompleted,
		CurrentVersion:   currentVersion,
		PreviousVersion:  previousVersion,
		ExpectedVersion:  expectedVersion,
		HighestVersion:   highestVersion,
		LastMigratedType: lastMigratedType,
		LastMigratedAt:   lastMigratedAt,
		LastMigratedFile: lastMigratedFile,
		CreatedAt:        createdAt,
	}
}
