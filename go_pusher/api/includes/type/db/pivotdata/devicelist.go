package pivotdata

import (
	"context"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_pusher/api/system/sharding"
	"strings"
)

// получить информацию о списке пользователей
const deviceListTableKey = "device_list"

// получаем запись из таблицы device_token для пользователей в массиве userList
func GetByUserDeviceIdList(ctx context.Context, userIdList []int64, deviceList []string, shardKey string, tableKey string) (map[int]map[string]string, error) {

	conn := sharding.Mysql(ctx, shardKey)
	if conn == nil {
		return nil, fmt.Errorf("passed wrong shard_id: %s", shardKey)
	}

	var plugForInDevice []string
	var queryArgsDevice []interface{}

	for _, v := range deviceList {

		plugForInDevice = append(plugForInDevice, "?")
		queryArgsDevice = append(queryArgsDevice, v)
	}

	userJson, _ := json.Marshal(userIdList)
	userString := strings.Trim(string(userJson), "[]")

	// осуществляем запрос
	queryArgsDevice = append(queryArgsDevice, len(plugForInDevice))
	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `user_id` IN (%s) AND `device_id` IN (%s) LIMIT ?", tableKey, userString, strings.Join(plugForInDevice, ","))
	row, err := conn.GetAll(ctx, query, queryArgsDevice...)
	if err != nil {
		return nil, fmt.Errorf("mysql request failed: %s database: %s error: %v", query, shardKey, err)
	}

	return row, nil
}

// получаем запись из базы для пользователя на обновление
func GetByDeviceIdForUpdate(ctx context.Context, tx mysql.TransactionStruct, deviceId string) (map[string]string, error) {

	tableKey := getDeviceListTableName(deviceId)

	// делаем запрос
	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `device_id` = ? LIMIT ? FOR UPDATE", tableKey)
	row, err := tx.FetchQuery(ctx, query, deviceId, 1)
	if err != nil {
		return nil, nil
	}

	return row, err
}

// обновляем список токенов
func UpdateExtra(ctx context.Context, tx mysql.TransactionStruct, deviceId string, validExtra []byte) error {

	tableKey := getDeviceListTableName(deviceId)

	// делаем запрос
	// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
	query := fmt.Sprintf("UPDATE `%s` SET `extra` = ?, `updated_at` = ? WHERE `device_id` = ? LIMIT ?", tableKey)

	_, err := tx.FetchQuery(ctx, query, string(validExtra), functions.GetCurrentTimeStamp(), deviceId, 1)
	if err != nil {
		return err
	}

	return nil
}

// создаем список tableName для запросов
func GetDeviceListGroupedTableName(deviceIdList []string) map[string][]string {

	groupedUserIdList := make(map[string][]string, 1)

	for _, value := range deviceIdList {

		groupedUserIdList[getDeviceListTableName(value)] = append(groupedUserIdList[getDeviceListTableName(value)], value)
	}

	return groupedUserIdList
}

// получаем tableName, в которой хранится информация о пользователе, на основе userID
func getDeviceListTableName(deviceId string) string {

	return fmt.Sprintf("%s_%s", deviceListTableKey, strings.ToLower(deviceId[len(deviceId)-1:]))
}
