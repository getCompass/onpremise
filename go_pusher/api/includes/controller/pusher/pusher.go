package pusher

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pusher/api/includes/type/analyticspush"
	"go_pusher/api/includes/type/apns"
	"go_pusher/api/includes/type/device"
	"go_pusher/api/includes/type/firebase"
	"go_pusher/api/includes/type/gwsolution"
	"go_pusher/api/includes/type/huawei"
	"go_pusher/api/includes/type/premise"
	"go_pusher/api/includes/type/push"
	"go_pusher/api/includes/type/push/badge"
	"go_pusher/api/includes/type/push/textpush"
	"go_pusher/api/includes/type/push/voippush"
	"go_pusher/api/includes/type/pushtype"
	"go_pusher/api/includes/type/socket"
	"go_pusher/api/includes/type/usernotification"
	"time"
)

const (
	TaskPushType = 1
	TaskReadType = 2
)

// на FIREBASE отправляем Push-уведомления сразу пачкой по тысяче токенов за запрос
// на APNS отправляем по одному запросу на каждый токен
// отправляем пуш уведомление ряду пользователей
// * badge значение при этом не обновляется
// @long
func SendPush(ctx context.Context, Uuid string, UserList []usernotification.UserNotificationStruct, PushData push.PushDataStruct) {

	// получаем настройки уведомлений для пользователей
	userNotificationMap := usernotification.GetPreparedUserNotificationMap(ctx, UserList)

	var analyticList = make(map[int64]analyticspush.PushStruct)

	for key, userNotification := range userNotificationMap {

		uuidPush := functions.GenerateUuid()

		// создадим и запишем обьект аналитики
		analyticItem := analyticspush.PushStruct{
			Uuid:         uuidPush,
			UserId:       userNotification.UserId,
			EventTime:    time.Now().Unix(),
			EventType:    0,
			DeviceId:     "",
			TokenHash:    "",
			PushId:       "",
			PushResponse: 0,
		}

		analyticItem.OnTalkingStartWorking()
		analyticspush.Add(analyticItem, uuidPush)

		// группируем объекты аналитики по пользователям
		analyticList[userNotification.UserId] = analyticItem

		if PushData.IsNeedForcePush == 0 && !isNeedSendPush(userNotification, PushData, analyticItem) {

			if userNotification.NeedForcePush == 0 {
				delete(userNotificationMap, key)
			}
		}

	}

	// получаем девайсы пользователей, которым отправляем analyticspush уведомление
	deviceList := device.GetUserDeviceList(ctx, userNotificationMap, analyticList, true)

	// собираем информацию о пуше для каждого юзера
	tokenPushMap := makeTokenPushMap(deviceList, Uuid, analyticList, []string{})

	providerInfoList := make([]push.ProviderInfo, 0)
	for tokenType, tokenPushList := range tokenPushMap {

		switch tokenType {

		case device.TokenTypeFirebaseLegacy, device.TokenTypeFirebaseV1:
			providerInfoList = append(providerInfoList, makeFirebaseProviderInfo(tokenType, tokenPushList))
		case device.TokenTypeApns:
			providerInfoList = append(providerInfoList, makeApnsProviderInfo(device.TokenTypeApns, tokenPushList))
		case device.TokenTypeHuawei:
			providerInfoList = append(providerInfoList, makeHuaweiProviderInfo(tokenPushList))
		}

	}

	// отправляем пуш всем пользователям
	requestSender(TaskPushType, providerInfoList, PushData)
}

// этот метод используется только для PIVOT в отличие от метода выше
// @long - большая структура
func SendPivotPush(ctx context.Context, Uuid string, UserList []int64, PushData push.PushDataStruct) {

	// получаем настройки уведомлений для пользователей
	userNotificationMap := usernotification.GetUserNotificationList(ctx, UserList)

	var analyticList = make(map[int64]analyticspush.PushStruct)

	// собираем информацию о пуше для каждого юзера
	for key, userNotification := range userNotificationMap {

		uuidPush := functions.GenerateUuid()

		// создадим и запишем обьект аналитики
		analyticItem := analyticspush.PushStruct{
			Uuid:         uuidPush,
			UserId:       userNotification.UserId,
			EventTime:    time.Now().Unix(),
			EventType:    0,
			DeviceId:     "",
			TokenHash:    "",
			PushId:       "",
			PushResponse: 0,
		}

		analyticItem.OnTalkingStartWorking()
		analyticspush.Add(analyticItem, uuidPush)

		analyticspush.UpdateAnalyticPush(analyticItem, analyticspush.PushStatusPivotTalkingWorking)

		if !isNeedSendPivotPush(userNotification, analyticItem) {
			delete(userNotificationMap, key)
		}

		// группируем объекты аналитики по пользователям
		analyticList[userNotification.UserId] = analyticItem
	}

	// получаем девайсы пользователей, которым отправляем analyticspush уведомление
	deviceList := device.GetUserDeviceList(ctx, userNotificationMap, analyticList, false)

	tokenPushMap := makeTokenPushMap(deviceList, Uuid, analyticList, []string{})

	providerInfoList := make([]push.ProviderInfo, 0)
	for tokenType, tokenPushList := range tokenPushMap {

		switch tokenType {

		case device.TokenTypeFirebaseLegacy, device.TokenTypeFirebaseV1:
			providerInfoList = append(providerInfoList, makeFirebaseProviderInfo(tokenType, tokenPushList))
		case device.TokenTypeApns:
			providerInfoList = append(providerInfoList, makeApnsProviderInfo(device.TokenTypeApns, tokenPushList))
		case device.TokenTypeHuawei:
			providerInfoList = append(providerInfoList, makeHuaweiProviderInfo(tokenPushList))
		}

	}

	// отправляем пуш всем пользователям
	requestSender(TaskPushType, providerInfoList, PushData)
}

// отправляем voip analyticspush на firebase & apns
// @long
func SendVoipPush(ctx context.Context, UserNotification usernotification.UserNotificationStruct, Uuid string, PushData push.PushDataStruct, SentDeviceList []string) {

	go func() {

		userList := []usernotification.UserNotificationStruct{UserNotification}
		userNotificationMap := usernotification.GetPreparedUserNotificationMap(ctx, userList)

		if len(userNotificationMap) < 1 {
			return
		}

		var analyticList = make(map[int64]analyticspush.PushStruct)

		for _, userNotification := range userNotificationMap {

			uuidPush := functions.GenerateUuid()

			// создадим и запишем обьект аналитики
			analyticItem := analyticspush.PushStruct{
				Uuid:         uuidPush,
				UserId:       userNotification.UserId,
				EventTime:    time.Now().Unix(),
				EventType:    0,
				DeviceId:     "",
				TokenHash:    "",
				PushId:       "",
				PushResponse: 0,
			}

			analyticItem.OnTalkingStartWorking()
			analyticspush.Add(analyticItem, uuidPush)

			// группируем объекты аналитики по пользователям
			analyticList[userNotification.UserId] = analyticItem
		}

		deviceList := device.GetUserDeviceList(ctx, userNotificationMap, analyticList, true)

		if len(deviceList) < 1 {
			return
		}

		// собираем информацию о пуше для каждого юзера
		tokenPushMap := makeTokenPushMap(deviceList, Uuid, analyticList, SentDeviceList)

		providerInfoList := make([]push.ProviderInfo, 0)
		for tokenType, tokenPushList := range tokenPushMap {

			switch tokenType {

			case device.TokenTypeFirebaseLegacy, device.TokenTypeFirebaseV1:
				providerInfoList = append(providerInfoList, makeFirebaseProviderInfo(tokenType, tokenPushList))
			case device.TokenTypeVoipApns:

				// если получили флаг, что voip пуш не требуется
				if PushData.VoipPush.IsNeedSendApns == 0 {
					continue
				}

				providerInfoList = append(providerInfoList, makeApnsProviderInfo(device.TokenTypeVoipApns, tokenPushList))
			case device.TokenTypeHuawei:
				providerInfoList = append(providerInfoList, makeHuaweiProviderInfo(tokenPushList))
			}

		}

		if len(providerInfoList) < 1 {
			return
		}

		// получаем инфу о пользователе, начавшем звонок
		PushData.VoipPush.UserInfo = getUserInfoForVoip(PushData.VoipPush.UserId)

		// отправляем пуш всем пользователям
		requestSender(TaskPushType, providerInfoList, PushData)
	}()
}

// отправляем тестовый пуш для устройства
func SendTestPush(tokenItem device.TokenItem, pushType string) {

	// создаем текстовый пуш с тестовыми данными
	tokenPushItem := createTokenPush(tokenItem, functions.GenerateUuid(), analyticspush.PushStruct{})
	tokenPushList := make([]device.PushTokenListGroupedByTokenType, 0)
	tokenPushList = append(tokenPushList, tokenPushItem)
	providerInfoList := make([]push.ProviderInfo, 0)

	pushStruct := textpush.CreateTest(pushType)
	pushDataStruct := push.PushDataStruct{
		TextPush:        &pushStruct,
		IsNeedForcePush: 1,
		PushType:        pushtype.TextPushType,
	}

	switch tokenItem.TokenType {

	case device.TokenTypeFirebaseLegacy, device.TokenTypeFirebaseV1:
		providerInfoList = append(providerInfoList, makeFirebaseProviderInfo(tokenItem.TokenType, tokenPushList))
	case device.TokenTypeApns:
		providerInfoList = append(providerInfoList, makeApnsProviderInfo(device.TokenTypeApns, tokenPushList))
	case device.TokenTypeHuawei:
		providerInfoList = append(providerInfoList, makeHuaweiProviderInfo(tokenPushList))
	}

	// отправляем пуши
	requestSender(TaskPushType, providerInfoList, pushDataStruct)
}

// получить информацию о пользователе для voip пуша
func getUserInfoForVoip(userId int64) voippush.UserInfo {

	if userId == 0 {
		return voippush.UserInfo{}
	}

	response, err := socket.GetUserInfo(userId)

	if err != nil {
		return voippush.UserInfo{}
	}

	return voippush.UserInfo{
		FullName:      response.Response.FullName,
		AvatarFileKey: response.Response.AvatarFileKey,
		AvatarColor:   response.Response.AvatarColor,
	}
}

// обновляет badge
func UpdateBadge(deviceStruct device.DeviceStruct, badgeCount int64, conversationKeyList []string, threadKeyList []string) {

	badgePush := badge.PushStruct{
		BadgeCount:          badgeCount,
		ConversationKeyList: conversationKeyList,
		ThreadKeyList:       threadKeyList,
	}

	providerInfoList := make([]push.ProviderInfo, 0)

	for _, tokenItem := range deviceStruct.GetTokenList() {

		if !functions.IsStringInSlice(tokenItem.AppName, device.AllowedAppNameList) {
			tokenItem.AppName = device.DefaultAppName
		}

		var tokenPushList []device.PushTokenListGroupedByTokenType

		tokenMap := map[string]string{}
		tokenMap[tokenItem.Token] = deviceStruct.DeviceId

		tokenPushList = append(tokenPushList, device.PushTokenListGroupedByTokenType{
			SoundType: device.SoundTypeAliasMap[tokenItem.TokenType],
			AppName:   tokenItem.AppName,
			Uuid:      functions.GenerateUuid(),
			TokenList: tokenMap,
			Version:   tokenItem.Version,
		})

		switch tokenItem.TokenType {

		case device.TokenTypeFirebaseLegacy, device.TokenTypeFirebaseV1:
			providerInfoList = append(providerInfoList, makeFirebaseProviderInfo(tokenItem.TokenType, tokenPushList))
		case device.TokenTypeApns:
			providerInfoList = append(providerInfoList, makeApnsProviderInfo(device.TokenTypeApns, tokenPushList))
		case device.TokenTypeHuawei:
			providerInfoList = append(providerInfoList, makeHuaweiProviderInfo(tokenPushList))
		}
	}

	pushData := push.PushDataStruct{
		BadgePush: &badgePush,
	}
	requestSender(TaskReadType, providerInfoList, pushData)
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// формируем для каждого токена пуш
// @long
func makeTokenPushMap(deviceList []device.DeviceStruct, uuid string, analyticList map[int64]analyticspush.PushStruct, sentDeviceList []string) map[int][]device.PushTokenListGroupedByTokenType {

	// собираем информацию о пуше для каждого токена
	groupedPushTokenList := make(map[int][]device.PushTokenListGroupedByTokenType)

	for _, deviceRow := range deviceList {

		analyticItem := analyticList[deviceRow.UserId]

		tokenList := deviceRow.GetTokenList()

		// если список токенов пуст
		if len(tokenList) < 1 {

			analyticspush.UpdateAnalyticPush(analyticItem, analyticspush.PushStatusEmptyTokenList)
			continue
		}

		for _, tokenItem := range tokenList {

			if tokenItem.TokenType == device.TokenTypeVoipApns {

				// в случае APNS не шлём на ios-устройство уже было доставлено WS-событие
				isGotEvent, _ := functions.InArray(tokenItem.DeviceId, sentDeviceList)
				if isGotEvent {
					continue
				}
			}

			if _, ok := device.SoundTypeAliasMap[tokenItem.TokenType]; !ok {
				tokenItem.SoundType = device.SoundType1
			}

			// если в итоговой мапе нет такого типа токена - добавляем
			if _, ok := groupedPushTokenList[tokenItem.TokenType]; !ok {
				groupedPushTokenList[tokenItem.TokenType] = []device.PushTokenListGroupedByTokenType{}
			}

			// пробегаясь по каждому токену – апгрейдим объект аналитики, устанавливая device_id & token_hash
			// дальше объект копируется и сохраняется в groupedPushTokenList
			// в результате в аналитике по каждому токену будет установлен соответствующий device_id & token_hash
			analyticItem.SetDeviceId(tokenItem.DeviceId)
			analyticItem.SetTokenHash(tokenItem.Token)

			// если такой пуш уже есть, просто добавляем токен
			if key := isPushExist(groupedPushTokenList[tokenItem.TokenType], tokenItem); key != -1 {

				groupedPushTokenList[tokenItem.TokenType][key].TokenList[tokenItem.Token] = tokenItem.DeviceId
				groupedPushTokenList[tokenItem.TokenType][key].PushAnalytics[tokenItem.Token] = analyticItem
			} else {

				// иначе добавляем новый объект токена
				groupedPushTokenList[tokenItem.TokenType] = append(groupedPushTokenList[tokenItem.TokenType], createTokenPush(tokenItem, uuid, analyticItem))
			}
		}

	}
	return groupedPushTokenList
}

func makeFirebaseProviderInfo(tokenType int, groupedTokenPushList []device.PushTokenListGroupedByTokenType) push.ProviderInfo {

	tokenPushList := make([]firebase.TokenPush, 0)

	for _, tokenInfo := range groupedTokenPushList {

		tokenPushList = append(tokenPushList, firebase.TokenPush{
			SoundType:      tokenInfo.SoundType,
			AppName:        tokenInfo.AppName,
			TokenDeviceMap: tokenInfo.TokenList,
		})
	}

	return push.ProviderInfo{
		Type: tokenType,
		FirebaseSendInfo: &firebase.SendInfo{
			Version:       1,
			TokenPushList: tokenPushList,
		},
	}
}

func makeApnsProviderInfo(tokenType int, groupedTokenPushList []device.PushTokenListGroupedByTokenType) push.ProviderInfo {

	tokenPushList := make([]apns.TokenPush, 0)

	for _, tokenInfo := range groupedTokenPushList {

		tokenPushList = append(tokenPushList, apns.TokenPush{
			SoundType:      tokenInfo.SoundType,
			AppName:        tokenInfo.AppName,
			TokenDeviceMap: tokenInfo.TokenList,
		})
	}
	return push.ProviderInfo{
		Type: tokenType,
		ApnsSendInfo: &apns.SendInfo{
			Version:       1,
			TokenPushList: tokenPushList,
		},
	}
}

func makeHuaweiProviderInfo(groupedTokenPushList []device.PushTokenListGroupedByTokenType) push.ProviderInfo {

	tokenPushList := make([]huawei.TokenPush, 0)

	for _, tokenInfo := range groupedTokenPushList {

		tokenPushList = append(tokenPushList, huawei.TokenPush{
			SoundType:      tokenInfo.SoundType,
			AppName:        tokenInfo.AppName,
			TokenDeviceMap: tokenInfo.TokenList,
		})
	}
	return push.ProviderInfo{
		Type: device.TokenTypeHuawei,
		HuaweiSendInfo: &huawei.SendInfo{
			Version:       1,
			TokenPushList: tokenPushList,
		},
	}
}

// отправляем пуш
func requestSender(taskType int, providerInfoList []push.ProviderInfo, pushData push.PushDataStruct) {

	if len(providerInfoList) < 1 {
		return
	}

	serverInfo, err := premise.GetCurrent()

	if err != nil {
		log.Errorf("cant request sender to send push. Error: %v", err)
	}

	pushTask := push.PushTaskStruct{
		ServerUid:        serverInfo.ServerUid,
		Uuid:             functions.GenerateUuid(),
		Type:             taskType,
		ProviderInfoList: providerInfoList,
		PushData:         pushData,
	}
	err = gwsolution.SendPush(pushTask)

	if err != nil {
		log.Errorf("cant request sender to send push %v. Error: %v", pushTask, err)
	}

}

// проверяем, нужно ли отправлять пуш
func isNeedSendPush(userNotification usernotification.UserNotificationStruct, pushData push.PushDataStruct, analyticItem analyticspush.PushStruct) bool {

	// если у пользователя нет токенов
	if !isDevicesExist(userNotification.DeviceList) {

		analyticspush.UpdateAnalyticPush(analyticItem, analyticspush.PushStatusEmptyTokenList)
		return false
	}

	// уведомления данного типа отключены
	if isEventDisabledForUser(userNotification, pushData.EventType) {

		analyticspush.UpdateAnalyticPush(analyticItem, analyticspush.PushStatusNotificationsDisabled)
		return false
	}

	return true
}

// проверяем, нужно ли отправлять пивот пуш
func isNeedSendPivotPush(userNotification usernotification.UserNotificationStruct, analyticItem analyticspush.PushStruct) bool {

	// если у пользователя нет токенов
	if !isDevicesExist(userNotification.DeviceList) {

		analyticspush.UpdateAnalyticPush(analyticItem, analyticspush.PushStatusEmptyTokenList)
		return false
	}

	return true
}

// есть ли у пользователя токены
func isDevicesExist(deviceList []string) bool {

	// токенов нету - пишем в аналитику что пустой tokenList
	if len(deviceList) < 1 {
		return false
	}

	return true
}

// отключены ли у пользователя уведомления данного типа
func isEventDisabledForUser(userNotification usernotification.UserNotificationStruct, eventType int) bool {

	return isNotificationsDisabled(userNotification, eventType) || isNotificationsSnoozed(userNotification, eventType)
}

// проверяет, не отлючил ли пользователь уведомления этого типа, и заносит в статстику, если такие уведомления отключены
func isNotificationsDisabled(userNotification usernotification.UserNotificationStruct, eventType int) bool {

	// уведомления данного типа отключены
	if userNotification.IsEventDisabled(eventType) {
		return true
	}

	return false
}

// проверяет, не отложил ли пользователь уведомления этого типа, и заносит в статстику, если такие уведомления отложены
func isNotificationsSnoozed(userNotification usernotification.UserNotificationStruct, eventType int) bool {

	// уведомления данного типа отключены
	if userNotification.IsEventSnoozed(eventType) {
		return true
	}

	return false
}

// формируем структуру tokenPush
func createTokenPush(tokenItem device.TokenItem, uuid string, analyticsPush analyticspush.PushStruct) device.PushTokenListGroupedByTokenType {

	tokenList := make(map[string]string)

	tokenList[tokenItem.Token] = tokenItem.DeviceId

	pushAnalytics := make(map[string]analyticspush.PushStruct)
	pushAnalytics[tokenItem.Token] = analyticsPush

	return device.PushTokenListGroupedByTokenType{
		SoundType:     device.SoundTypeAliasMap[tokenItem.SoundType],
		Uuid:          uuid,
		AppName:       tokenItem.AppName,
		TokenList:     tokenList,
		Version:       tokenItem.Version,
		PushAnalytics: pushAnalytics,
	}
}

// проверить, есть ли похожая структура, чтобы просто добавить токен для таска
func isPushExist(pushList []device.PushTokenListGroupedByTokenType, tokenItem device.TokenItem) int {

	for key, pushItem := range pushList {

		if pushItem.SoundType == device.SoundTypeAliasMap[tokenItem.SoundType] && pushItem.AppName == tokenItem.AppName {

			return key
		}
	}

	return -1
}
