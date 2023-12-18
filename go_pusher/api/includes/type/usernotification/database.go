package usernotification

import (
	"context"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pusher/api/includes/type/db/pivotuser"
)

// получить список записей уведомлений пользователей с проведенным схождением срезов девайсов
func GetPreparedUserNotificationMap(ctx context.Context, userNotificationList []UserNotificationStruct) map[int64]UserNotificationStruct {

	var userIdList []int64
	var outputUserNotificationMap = make(map[int64]UserNotificationStruct)

	for _, v := range userNotificationList {
		userIdList = append(userIdList, v.UserId)
	}
	pivotUserNotificationList := GetUserNotificationList(ctx, userIdList)

	for _, v := range userNotificationList {

		v.DeviceList = interSectSlices(v.DeviceList, pivotUserNotificationList[v.UserId].DeviceList)
		v.ExtraField = pivotUserNotificationList[v.UserId].ExtraField
		v.SnoozedUntil = pivotUserNotificationList[v.UserId].SnoozedUntil

		outputUserNotificationMap[v.UserId] = v

	}

	return outputUserNotificationMap
}

// получаем пользовательские device токены
func GetUserNotificationList(ctx context.Context, userList []int64) map[int64]UserNotificationStruct {

	outputMap := make(map[int64]UserNotificationStruct)

	// результат выборки
	resultMap := getUserNotificationListByUserList(ctx, userList)

	// проходимся по результатам выборки и сохраняем в output
	for userId, deviceTokenRow := range resultMap {
		outputMap[userId] = deviceTokenRow
	}

	return outputMap
}

// получаем список токенов пользователей
func getUserNotificationListByUserList(ctx context.Context, userList []int64) map[int64]UserNotificationStruct {

	// получаем записи из базы
	row, err := pivotuser.GetUserNotificationListFromDb(ctx, userList)
	if err != nil {

		log.Error(fmt.Sprintf("mysql request error: %v", err))
		return nil
	}

	// формируем ответ
	outputMap := makeGetUserNotificationListByUserListOutput(row)

	return outputMap
}

// формируем ответ
func makeGetUserNotificationListByUserListOutput(rowMap []map[string]string) map[int64]UserNotificationStruct {

	outputMap := make(map[int64]UserNotificationStruct)
	for _, item := range rowMap {

		// получаем список девайсов пользователя
		deviceList, err := getUserDeviceList(item)
		if err != nil {
			continue
		}

		// получаем extra пользователя
		extra, err := getUserExtra(item)
		if err != nil {
			continue
		}

		// сохраняем в массив с результатами
		deviceTokenRow := makeDeviceTokenRow(item["user_id"], item["snoozed_until"], deviceList, extra)
		outputMap[deviceTokenRow.UserId] = deviceTokenRow
	}
	return outputMap
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// получаем список токенов пользователя
func getUserDeviceList(row map[string]string) ([]string, error) {

	var deviceList []string

	// получаем токены пользователя
	err := json.Unmarshal([]byte(row["device_list"]), &deviceList)

	return deviceList, err
}

// получаем extra пользователя
func getUserExtra(row map[string]string) (extraField, error) {

	var extra extraField

	// получаем extra пользователя
	err := json.Unmarshal([]byte(row["extra"]), &extra)

	return extra, err
}

// формируем RowStruct
func makeDeviceTokenRow(userId string, snoozedUntil string, deviceList []string, extra extraField) UserNotificationStruct {

	return UserNotificationStruct{
		UserId:       functions.StringToInt64(userId),
		SnoozedUntil: functions.StringToInt64(snoozedUntil),
		DeviceList:   deviceList,
		ExtraField:   extra,
	}
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// найти схождение срезов
func interSectSlices(s1, s2 []string) (inter []string) {

	hash := make(map[string]bool)

	for _, e := range s1 {

		hash[e] = true
	}

	for _, e := range s2 {

		if hash[e] {

			inter = append(inter, e)
		}
	}

	// удаляем повторения
	inter = removeDups(inter)
	return
}

// Удалить повторения в срезе
func removeDups(elements []string) (nodups []string) {

	encountered := make(map[string]bool)

	for _, element := range elements {

		if !encountered[element] {

			nodups = append(nodups, element)
			encountered[element] = true
		}
	}
	return
}
