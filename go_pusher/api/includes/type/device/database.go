package device

import (
	"context"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_pusher/api/includes/type/analyticspush"
	"go_pusher/api/includes/type/db/pivotdata"
	"go_pusher/api/includes/type/usernotification"
)

// получаем пользовательские device токены
func GetUserDeviceList(ctx context.Context, userNotificationMap map[int64]usernotification.UserNotificationStruct, analyticList map[int64]analyticspush.PushStruct, isOnlyCompany bool) []DeviceStruct {

	// подготавливаем массив userList к формату
	// map[string]map[string][]int64 (map[shard_key]map[table_name][]user_list)

	var existDeviceUserList []int64
	var deviceList []string
	var userList []int64
	var outputMap = make([]DeviceStruct, 0)
	for k, v := range userNotificationMap {

		deviceList = append(deviceList, v.DeviceList...)
		userList = append(userList, k)
	}
	preparedDeviceMap := pivotdata.GetDeviceListGroupedTableName(deviceList)

	// проходимся по каждой таблице
	for tableName, tableDeviceList := range preparedDeviceMap {

		// результат выборки
		resultMap := getDeviceListByUserList(ctx, userList, tableDeviceList, pivotdata.DbKey, tableName)

		for userId, devices := range resultMap {

			analyticItem := analyticList[userId]

			deviceOutputList := prepareDeviceList(devices, userNotificationMap[userId].Token, analyticItem, isOnlyCompany)
			if len(deviceOutputList) < 1 {
				continue
			}

			existDeviceUserList = append(existDeviceUserList, userId)

			outputMap = mergeSlices(outputMap, deviceOutputList)
		}
	}

	for _, userId := range userList {

		isExist, _ := functions.InArray(userId, existDeviceUserList)
		if !isExist {

			analyticItem := analyticList[userId]
			analyticspush.UpdateAnalyticPush(analyticItem, analyticspush.PushStatusDeviceWithTokenListIsEmpty)
		}
	}

	return outputMap
}

// получаем список токенов пользователей
func getDeviceListByUserList(ctx context.Context, userIdList []int64, deviceList []string, shardKey string, tableName string) map[int64][]DeviceStruct {

	// получаем записи из базы
	row, err := pivotdata.GetByUserDeviceIdList(ctx, userIdList, deviceList, shardKey, tableName)
	if err != nil {

		log.Error(fmt.Sprintf("mysql request error: %v", err))
		return nil
	}

	// формируем ответ
	outputMap := makeGetDeviceListByUserListOutput(row)

	return outputMap
}

// формируем ответ
func makeGetDeviceListByUserListOutput(rowMap map[int]map[string]string) map[int64][]DeviceStruct {

	outputMap := make(map[int64][]DeviceStruct)
	for _, item := range rowMap {

		// получаем extra пользователя
		extra, err := getUserExtra(item)
		if err != nil {
			continue
		}

		// сохраняем в массив с результатами
		deviceTokenRow := makeDeviceTokenRow(item["user_id"], item["device_id"], extra)
		outputMap[deviceTokenRow.UserId] = append(outputMap[deviceTokenRow.UserId], deviceTokenRow)
	}
	return outputMap
}

// обновляем токен для пользователя
func updateTokenList(ctx context.Context, deviceId string, badTokenList []string) bool {

	tx, err := pivotdata.BeginTransaction(ctx)
	if err != nil {
		return false
	}

	row, _ := pivotdata.GetByDeviceIdForUpdate(tx, deviceId)
	if !isRowExist(tx, row) {
		return true
	}

	validTokenList, isSuccess := makeValidTokenList(row, badTokenList)
	if !isSuccess {
		return false
	}

	err = update(tx, deviceId, validTokenList)
	if err != nil {
		return false
	}
	if !pivotdata.CommitTransaction(tx) {
		return false
	}
	return true
}

// существует ли запись
func isRowExist(tx mysql.TransactionStruct, rowMap map[string]string) bool {

	// если не получили запись
	if _, exist := rowMap["device_id"]; !exist {

		_ = tx.Rollback()
		return false
	}

	return true
}

// формируем список валидных токенов
func makeValidTokenList(rowMap map[string]string, badTokenList []string) (DeviceStruct, bool) {

	device := prepareDeviceStruct(rowMap)

	validTokenList := make([]TokenItem, 0)

	for _, tempTokenItem := range device.GetTokenList() {

		// оставляем лишь только валидные
		if functions.StringSliceContains(badTokenList, tempTokenItem.Token) {
			continue
		}
		validTokenList = append(validTokenList, tempTokenItem)
	}

	device.SetTokenList(validTokenList)

	return device, true
}

// обновляем запись
func update(tx mysql.TransactionStruct, deviceId string, device DeviceStruct) error {

	// формируем json строку
	extraFieldJSON, err := json.Marshal(device.ExtraField)
	if err != nil {
		return err
	}

	// обновляем список токенов
	err = pivotdata.UpdateExtra(tx, deviceId, extraFieldJSON)
	if err != nil {
		return err
	}

	return nil
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// получаем extra пользователя
func getUserExtra(row map[string]string) (extraField, error) {

	var extra extraField

	// получаем extra пользователя
	err := json.Unmarshal([]byte(row["extra"]), &extra)

	return extra, err
}

// формируем RowStruct
func makeDeviceTokenRow(userId string, deviceId string, extra extraField) DeviceStruct {

	return DeviceStruct{
		UserId:     functions.StringToInt64(userId),
		DeviceId:   deviceId,
		ExtraField: extra,
	}
}

// подготовить список девайсов пользователя с токенами
func prepareDeviceList(deviceList []DeviceStruct, token string, analyticItem analyticspush.PushStruct, isOnlyCompany bool) []DeviceStruct {

	var deviceOutputList = make([]DeviceStruct, 0)
	for _, v := range deviceList {

		if isOnlyCompany {

			deviceSlice := v.GetUserCompanyTokenList()

			// проверяем, что в девайсе есть токен компании, и ему можно отослать пуш
			if functions.IsStringInSlice(token, deviceSlice) {
				deviceOutputList = append(deviceOutputList, v)
			} else {
				analyticspush.UpdateAnalyticPush(analyticItem, analyticspush.PushStatusCompanyTokenNotExist)
			}
		} else {
			deviceOutputList = append(deviceOutputList, v)
		}
	}

	return deviceOutputList
}

// подготовить структуру девайса для апдейта
func prepareDeviceStruct(row map[string]string) DeviceStruct {

	var extra extraField
	_ = json.Unmarshal([]byte(row["extra"]), &extra)

	return DeviceStruct{
		UserId:     functions.StringToInt64(row["user_id"]),
		DeviceId:   row["device_id"],
		ExtraField: extra,
	}
}

// соединить массивы девайсов с разных таблиц
func mergeSlices(s1 []DeviceStruct, s2 []DeviceStruct) []DeviceStruct {

	slice := make([]DeviceStruct, 0, len(s1)+len(s2))
	for i := range s1 {
		slice = append(slice, s1[i])
	}
	for i := range s2 {
		slice = append(slice, s2[i])
	}
	return slice
}
