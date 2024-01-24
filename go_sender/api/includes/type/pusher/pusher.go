package pusher

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	gatewayPhpCompany "go_sender/api/includes/gateway/php_company"
	gatewayPhpPivot "go_sender/api/includes/gateway/php_pivot"
	"go_sender/api/includes/type/db/company_data"
	"go_sender/api/includes/type/push"
	"go_sender/api/includes/type/user_notification"
	"time"
)

const getPivotSocketKeyLimit = 3 // лимит для получения сокета пивота

type Conn struct {
	pivotSocketKey string
	companyId      int64
	currentServer  string
}

func MakePusherConn(currentServer string, socketKeyMe string, companyId int64) *Conn {

	pivotSocketKey := ""

	// если это pivot, ничего не делаем
	if currentServer == "domino" || currentServer == "monolith" {

		pivotSocketKey = gatewayPhpCompany.GetPivotSocketKey(companyId, socketKeyMe)

		// если по какой-то причине не смогли с первого раза получить ключ для сокета
		if pivotSocketKey == "" {
			pivotSocketKey = waitForGetPivotSocketKey(socketKeyMe, companyId)
		}
	}

	return &Conn{
		pivotSocketKey: pivotSocketKey,
		currentServer:  currentServer,
		companyId:      companyId,
	}
}

// ждём для повтора получения сокет-ключа pivot
func waitForGetPivotSocketKey(socketKeyMe string, companyId int64) string {

	pivotSocketKey := ""
	errorCount := 0

	for pivotSocketKey == "" && errorCount <= getPivotSocketKeyLimit {

		log.Infof("не смогли получить ключ pivot-сокета for company %v, пробуем ещё раз. error_count: %v", companyId, errorCount)
		errorCount++

		time.Sleep(1 * time.Second)
		pivotSocketKey = gatewayPhpCompany.GetPivotSocketKey(companyId, socketKeyMe)
	}

	// если в итоге не смогли заполучить ключ
	if pivotSocketKey == "" {
		panic("not passed pivot socket key from company")
	}

	return pivotSocketKey
}

// SendPush идея что пуши отправляются только локально
// @long
func (conn *Conn) SendPush(uuid string, pushData push.PushDataStruct, userNotificationRowList []*company_data.NotificationRow, userForceNotificationRowList []*company_data.NotificationRow) {

	if conn.currentServer != "domino" && conn.currentServer != "monolith" {
		return
	}

	var userNotificationStructList []user_notification.UserNotificationStruct
	for _, userNotificationRow := range userNotificationRowList {

		// получаем extra пользователя
		extra, err := user_notification.GetUserExtra(userNotificationRow)
		if err != nil {

			log.Successf("no user extra %v", err)
			continue
		}

		userNotification := prepareUserNotificationStruct(
			userNotificationRow.UserId, userNotificationRow.SnoozedUntil, userNotificationRow.Token, userNotificationRow.DeviceList, extra, 0)

		// если имеются устройства у пользователя и - он не замьютил ивент или получен флаг зафорсить отправку пуша
		if len(userNotification.DeviceList) > 0 && (pushData.IsNeedForcePush == 1 || !userNotification.IsEventSnoozed(pushData.EventType)) {
			userNotificationStructList = append(userNotificationStructList, userNotification)
		}
	}

	if len(userNotificationStructList) > 0 {

		var NeedForcePush = 0
		gatewayPhpPivot.SendPush(userNotificationStructList, pushData, uuid, conn.companyId, conn.pivotSocketKey, NeedForcePush)
	}

	var userForceNotificationStructList []user_notification.UserNotificationStruct
	for _, userForceNotificationRow := range userForceNotificationRowList {

		// получаем extra пользователя
		extra, err := user_notification.GetUserExtra(userForceNotificationRow)
		if err != nil {

			log.Successf("no user extra %v", err)
			continue
		}

		userForceNotification := prepareUserNotificationStruct(
			userForceNotificationRow.UserId, userForceNotificationRow.SnoozedUntil, userForceNotificationRow.Token, userForceNotificationRow.DeviceList, extra, 1)

		// если имеются устройства у пользователя
		if len(userForceNotification.DeviceList) > 0 {
			userForceNotificationStructList = append(userForceNotificationStructList, userForceNotification)
		}
	}

	if len(userForceNotificationStructList) > 0 {

		var NeedForcePush = 1
		gatewayPhpPivot.SendPush(userForceNotificationStructList, pushData, uuid, conn.companyId, conn.pivotSocketKey, NeedForcePush)
	}
}

// отправляем на локальный go_pusher задачу для отправки VoIP уведомления через RabbitMq очередь
func (conn *Conn) SendVoIP(userNotificationRow *company_data.NotificationRow, pushData interface{}, uuid string, timeToLive int64, sentDeviceList []string) {

	if conn.currentServer != "domino" && conn.currentServer != "monolith" {
		return
	}

	userNotificationStruct := prepareUserNotificationStruct(userNotificationRow.UserId, userNotificationRow.SnoozedUntil, userNotificationRow.Token, userNotificationRow.DeviceList, user_notification.ExtraField{}, 0)

	gatewayPhpPivot.SendVoipPush(userNotificationStruct, pushData, uuid, timeToLive, sentDeviceList, conn.companyId, conn.pivotSocketKey)
}

// подготовить структуру записи уведомления пользователя
func prepareUserNotificationStruct(userId int64, snoozedUntil int64, token string, deviceList string, extra user_notification.ExtraField, needForcePush int) user_notification.UserNotificationStruct {

	var deviceSliceList []string

	_ = json.Unmarshal([]byte(deviceList), &deviceSliceList)

	return user_notification.UserNotificationStruct{
		UserID:        userId,
		SnoozedUntil:  snoozedUntil,
		Token:         token,
		DeviceList:    deviceSliceList,
		ExtraField:    extra,
		NeedForcePush: needForcePush,
	}
}
