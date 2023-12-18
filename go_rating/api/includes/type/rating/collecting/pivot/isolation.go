package pivot

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_rating/api/conf"
	CompanyEnvironment "go_rating/api/includes/type/company_config"
	Database "go_rating/api/includes/type/database"
	CompanyConverstion "go_rating/api/includes/type/database/company_conversation"
	Isolation "go_rating/api/includes/type/isolation"
	"go_rating/api/includes/type/socket"
	socketAuthKey "go_rating/api/includes/type/socket/auth"
	"go_rating/api/includes/type/structures"
	"math/rand"
	"time"
)

const isolationGlobalPivotScreenTimeKey = "pivot.global_pivot_screen_time_key"          // ключ глобального коллектора экранного времени для пивота
const isolationGlobalPivotUserActionKey = "pivot.global_pivot_user_action_key"          // ключ глобального коллектора количества действий пользователя для пивота
const isolationGlobalPivotUserAnswerTimeKey = "pivot.global_pivot_user_answer_time_key" // ключ глобального коллектора времени ответа пользователя на сообщения
const maxPivotListItemCountPerChunk = 20                                                // максимальное число объектов в пачке

// IsolationReg функция для регистрации пакета в изоляциях
// должна вызваться при старте сервиса
func IsolationReg() {

	Isolation.RegPackageGlobal("pivot", nil, isolationInitGlobal, isolationInvalidate)
}

// инициализация глобальной изоляции
func isolationInitGlobal(isolation *Isolation.Isolation) error {

	// добавляем все нужные данные в изоляцию
	isolation.Set(isolationGlobalPivotScreenTimeKey, MakeScreentimeCollector(isolation))
	isolation.Set(isolationGlobalPivotUserActionKey, MakeActionCollector(isolation))
	isolation.Set(isolationGlobalPivotUserAnswerTimeKey, MakeUserAnswerTimeCollector(isolation))

	// вешаем обзервер экранного времени
	go observeScreenTime(isolation)

	// вешаем обзерверы количества действий
	go observeUserAction(isolation)
	go observeConversationAction(isolation)

	// вешаем обзервер на время ответа на сообщения
	go observeAnswerTime(isolation)

	return nil
}

// инвалидация изоляции
// глобальная изоляция не содержит уникальных ключей, поэтому инвалидция общая
func isolationInvalidate(isolation *Isolation.Isolation) error {

	// добавляем все нужные данные в изоляцию
	isolation.Set(isolationGlobalPivotScreenTimeKey, nil)
	isolation.Set(isolationGlobalPivotUserActionKey, nil)
	return nil
}

// обзервим экранное время
func observeScreenTime(isolation *Isolation.Isolation) {

	ctx := isolation.GetContext()
	observeInterval := (time.Minute * 5) + (time.Duration(rand.Intn(30)+1) * time.Second)
	for {

		select {

		case <-time.After(observeInterval):

			storedScreenTime, err := CollectScreenTime(isolation)
			if err != nil {

				log.Errorf("error in screen time observer: %v", err)
				continue
			}

			sendScreenTime(storedScreenTime)

		case <-ctx.Done():

			log.Info("Закрыли обсервер для экранного времени")
			return

		}
	}
}

// отправляем экранное время в пивот
func sendScreenTime(storedScreenTime []*UserScreenTimeListStruct) {

	// если нечего отправлять
	if storedScreenTime == nil || len(storedScreenTime) == 0 {
		return
	}

	var spaceUserScreenTimeList []structures.SpaceUserScreenTimeListStruct

	// проходим по сброшенному кэшу
	for _, storedScreenTimeItem := range storedScreenTime {

		var userScreenTimeList []structures.UserScreenTimeStruct
		for _, userScreenTimeItem := range storedScreenTimeItem.UserScreenTimeList {

			userScreenTimeList = append(userScreenTimeList, structures.UserScreenTimeStruct{
				UserID:        userScreenTimeItem.UserID,
				ScreenTime:    userScreenTimeItem.ScreenTime,
				LocalOnlineAt: userScreenTimeItem.LocalOnlineAt,
			})
		}

		spaceUserScreenTimeList = append(spaceUserScreenTimeList, structures.SpaceUserScreenTimeListStruct{
			SpaceId:            storedScreenTimeItem.SpaceId,
			CacheAt:            storedScreenTimeItem.CacheAt,
			UserScreenTimeList: userScreenTimeList,
		})
	}

	// если можно все отправить разом, то шлем одним запросом
	if len(spaceUserScreenTimeList) <= maxPivotListItemCountPerChunk {

		ratingSaveScreenTime(structures.RequestSaveScreenTimeDataStruct{
			ScreenTimeList: spaceUserScreenTimeList,
		})
		return
	}

	// если слишком большой массив, то бьем на пачки и шлем кусками
	for i := 0; i < len(spaceUserScreenTimeList); i += maxPivotListItemCountPerChunk {

		end := i + maxPivotListItemCountPerChunk

		if end > len(spaceUserScreenTimeList) {
			end = len(spaceUserScreenTimeList)
		}

		ratingSaveScreenTime(structures.RequestSaveScreenTimeDataStruct{
			ScreenTimeList: spaceUserScreenTimeList[i:end],
		})
	}
}

// запрос на отправку экранного времени в пивот
func ratingSaveScreenTime(request structures.RequestSaveScreenTimeDataStruct) {

	jsonParams, err := go_base_frame.Json.Marshal(request)

	signature := socketAuthKey.GetSignature(conf.GetConfig().SocketKeyMe, jsonParams)
	response, err := socket.DoCall("php_pivot", "rating.saveScreenTime", jsonParams, signature, 0)
	if err != nil || response.Status != "ok" {

		log.Errorf("Не смогли выполнить запрос %v", err)
		if err == nil {
			err = fmt.Errorf("response status not ok: %s", response.Status)
		}
	}
}

// обзервим количество действий пользователей
func observeUserAction(isolation *Isolation.Isolation) {

	ctx := isolation.GetContext()
	observeInterval := (time.Minute * 5) + (time.Duration(rand.Intn(30)+1) * time.Second)
	for {

		select {

		case <-time.After(observeInterval):

			storedAction, err := CollectUserAction(isolation)
			if err != nil {

				log.Errorf("error in user action observer: %v", err)
				continue
			}

			sendUserAction(storedAction)

		case <-ctx.Done():

			log.Info("Закрыли обсервер для количества действий пользователей")
			return

		}
	}
}

// отправляем количество действий пользователей в пивот
func sendUserAction(storedAction []*UserActionListStruct) {

	// если нечего отправлять
	if storedAction == nil || len(storedAction) == 0 {
		return
	}

	var userList []structures.UserListStruct

	// проходим по сброшенному кэшу
	for _, storedActionItem := range storedAction {

		for _, userItem := range storedActionItem.UserList {

			userList = append(userList, structures.UserListStruct{
				UserId:   userItem.UserId,
				SpaceId:  storedActionItem.SpaceId,
				ActionAt: storedActionItem.ActionAt,
				ActionList: structures.ActionListStruct{
					GroupsCreated:              userItem.ActionList.GroupsCreated,
					ConversationsRead:          userItem.ActionList.ConversationsRead,
					ConversationMessagesSent:   userItem.ActionList.ConversationMessagesSent,
					ConversationReactionsAdded: userItem.ActionList.ConversationReactionsAdded,
					ConversationRemindsCreated: userItem.ActionList.ConversationRemindsCreated,
					Calls:                      userItem.ActionList.Calls,
					ThreadsCreated:             userItem.ActionList.ThreadsCreated,
					ThreadsRead:                userItem.ActionList.ThreadsRead,
					ThreadMessagesSent:         userItem.ActionList.ThreadMessagesSent,
					ThreadReactionsAdded:       userItem.ActionList.ThreadReactionsAdded,
					ThreadRemindsCreated:       userItem.ActionList.ThreadRemindsCreated,
				},
			})
		}
	}

	// если можно все отправить разом, то шлем одним запросом
	if len(userList) <= maxPivotListItemCountPerChunk {

		ratingSaveUserActionList(structures.RequestSaveUserActionListStruct{
			UserList: userList,
		})
		return
	}

	// если слишком большой массив, то бьем на пачки и шлем кусками
	for i := 0; i < len(userList); i += maxPivotListItemCountPerChunk {

		end := i + maxPivotListItemCountPerChunk

		if end > len(userList) {
			end = len(userList)
		}

		ratingSaveUserActionList(structures.RequestSaveUserActionListStruct{
			UserList: userList[i:end],
		})
	}
}

// запрос на отправку количества действий в пивот
func ratingSaveUserActionList(request structures.RequestSaveUserActionListStruct) {

	jsonParams, err := go_base_frame.Json.Marshal(request)

	signature := socketAuthKey.GetSignature(conf.GetConfig().SocketKeyMe, jsonParams)
	response, err := socket.DoCall("php_pivot", "rating.saveUserActionList", jsonParams, signature, 0)
	if err != nil || response.Status != "ok" {

		log.Errorf("Не смогли выполнить запрос %v", err)
		if err == nil {
			err = fmt.Errorf("response status not ok: %s", response.Status)
		}
	}
}

// обзервим количество действий в диалогах
func observeConversationAction(isolation *Isolation.Isolation) {

	ctx := isolation.GetContext()
	observeInterval := (time.Minute * 5) + (time.Duration(rand.Intn(30)+1) * time.Second)
	for {

		select {

		case <-time.After(observeInterval):

			storedAction, err := CollectConversationAction(isolation)
			if err != nil {

				log.Errorf("error in conversation action observer: %v", err)
				continue
			}

			sendConversationAction(storedAction)

		case <-ctx.Done():

			log.Info("Закрыли обсервер для количества действий в диалогах")
			return

		}
	}
}

// сохраняем количество действий в диалогах в базу
func sendConversationAction(storedAction []*ConversationActionListStruct) {

	// если нечего отправлять
	if storedAction == nil || len(storedAction) == 0 {
		return
	}

	// проходим по сброшенному кэшу
	for _, storedActionItem := range storedAction {

		for _, conversationItem := range storedActionItem.ConversationList {

			isolation := CompanyEnvironment.GetEnv(storedActionItem.SpaceId)
			if isolation == nil {

				log.Errorf("trying to get isolation for space id %v, but isolation already invalidated", storedActionItem.SpaceId)
				continue
			}
			ctx := isolation.GetContext()

			conn := Database.LeaseConversationConnection(isolation)
			if conn == nil {

				log.Errorf("no connection to database %v in conversation action observer", storedActionItem.SpaceId)
				continue
			}

			conversationRow, err := CompanyConverstion.ConversationDynamicTable.GetOne(ctx, conn, conversationItem.ConversationMap)
			if err != nil {

				log.Errorf("error get conversation from db, space_id: %v, err: %v", storedActionItem.SpaceId, err)
				continue
			}

			if conversationRow == nil {

				log.Errorf("empty conversation row from db, space_id: %v", storedActionItem.SpaceId)
				continue
			}

			conversationDynamic := &CompanyConverstion.ConversationDynamicRecord{
				ConversationMap:  conversationRow.ConversationMap,
				TotalActionCount: incConversationTotalActionCount(conversationRow.TotalActionCount, conversationItem.ActionList),
			}

			err = CompanyConverstion.ConversationDynamicTable.UpdateTotalActionCount(ctx, conn, conversationDynamic)
			if err != nil {

				log.Errorf("error update conversation in db, space_id: %v, err: %v", storedActionItem.SpaceId, err)
				continue
			}
		}
	}
}

// инкрементим количество действий в диалоге
func incConversationTotalActionCount(totalActionCount int64, actionList ActionListStruct) int64 {

	// извините по actionList нельзя бежать range
	totalActionCount += actionList.GroupsCreated
	totalActionCount += actionList.ConversationsRead
	totalActionCount += actionList.ConversationMessagesSent
	totalActionCount += actionList.ConversationReactionsAdded
	totalActionCount += actionList.ConversationRemindsCreated
	totalActionCount += actionList.Calls
	totalActionCount += actionList.ThreadsCreated
	totalActionCount += actionList.ThreadsRead
	totalActionCount += actionList.ThreadMessagesSent
	totalActionCount += actionList.ThreadReactionsAdded
	totalActionCount += actionList.ThreadRemindsCreated

	return totalActionCount
}

// обзервим время ответа на сообщения
func observeAnswerTime(isolation *Isolation.Isolation) {

	ctx := isolation.GetContext()
	observeInterval := (time.Minute * 5) + (time.Duration(rand.Intn(30)+1) * time.Second)
	for {

		select {

		case <-time.After(observeInterval):

			storedUserAnswerTime, err := CollectUserAnswerTime(isolation)
			if err != nil {

				log.Errorf("error in answer time observer: %v", err)
				continue
			}

			sendUserAnswerTime(storedUserAnswerTime)

		case <-ctx.Done():

			log.Info("Закрыли обсервер для экранного времени")
			return

		}
	}
}

// отправляем время ответа на сообщения в пивот
func sendUserAnswerTime(storedUserAnswerTime []*UserAnswerTimeConversationListStruct) {

	// если нечего отправлять
	if storedUserAnswerTime == nil || len(storedUserAnswerTime) == 0 {
		return
	}

	var conversationUserAnswerTimeList []structures.ConversationUserAnswerTimeListStruct

	// проходим по собранному кэшу
	for _, conversationItem := range storedUserAnswerTime {

		var userAnswerTimeTimeList []structures.UserAnswerTimeStruct
		for _, userAnswerTimeItem := range conversationItem.UserAnswerTimeList {

			userAnswerTimeTimeList = append(userAnswerTimeTimeList, structures.UserAnswerTimeStruct{
				UserID:     userAnswerTimeItem.UserID,
				AnswerTime: userAnswerTimeItem.AnswerTime,
				AnsweredAt: userAnswerTimeItem.AnsweredAt,
			})
		}

		conversationUserAnswerTimeList = append(conversationUserAnswerTimeList, structures.ConversationUserAnswerTimeListStruct{
			Min15StartAt:       conversationItem.Min15StartAt,
			SpaceId:            conversationItem.SpaceId,
			ConversationKey:    conversationItem.ConversationKey,
			UserAnswerTimeList: userAnswerTimeTimeList,
		})
	}

	// если можно все отправить разом, то шлем одним запросом
	if len(conversationUserAnswerTimeList) <= maxPivotListItemCountPerChunk {

		ratingSaveUserAnswerTime(structures.RequestSaveUserAnswerTimeDataStruct{
			ConversationList: conversationUserAnswerTimeList,
		})
		return
	}

	// если слишком большой массив, то бьем на пачки и шлем кусками
	for i := 0; i < len(conversationUserAnswerTimeList); i += maxPivotListItemCountPerChunk {

		end := i + maxPivotListItemCountPerChunk

		if end > len(conversationUserAnswerTimeList) {
			end = len(conversationUserAnswerTimeList)
		}

		ratingSaveUserAnswerTime(structures.RequestSaveUserAnswerTimeDataStruct{
			ConversationList: conversationUserAnswerTimeList[i:end],
		})
	}
}

// запрос на отправку времени ответа на сообщения в пивот
func ratingSaveUserAnswerTime(request structures.RequestSaveUserAnswerTimeDataStruct) {

	jsonParams, err := go_base_frame.Json.Marshal(request)

	signature := socketAuthKey.GetSignature(conf.GetConfig().SocketKeyMe, jsonParams)
	response, err := socket.DoCall("php_pivot", "rating.saveUserAnswerTime", jsonParams, signature, 0)
	if err != nil || response.Status != "ok" {

		log.Errorf("Не смогли выполнить запрос %v", err)
		if err == nil {
			err = fmt.Errorf("response status not ok: %s", response.Status)
		}
	}
}

// --------------------------------------
// функции доступа к изолированным данных
// --------------------------------------

// LeaseScreentimeCollector возвращает экземпляр ScreentimeCollector
func LeaseScreentimeCollector(isolation *Isolation.Isolation) *ScreentimeCollector {

	isolatedValue := isolation.Get(isolationGlobalPivotScreenTimeKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*ScreentimeCollector)
}

// LeaseActionCollector возвращает экземпляр ActionCollector
func LeaseActionCollector(isolation *Isolation.Isolation) *ActionCollector {

	isolatedValue := isolation.Get(isolationGlobalPivotUserActionKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*ActionCollector)
}

// LeaseUserAnswerTimeCollector возвращает экземпляр UserAnswerTimeCollector
func LeaseUserAnswerTimeCollector(isolation *Isolation.Isolation) *UserAnswerTimeCollector {

	isolatedValue := isolation.Get(isolationGlobalPivotUserAnswerTimeKey)
	if isolatedValue == nil {
		return nil
	}

	return isolatedValue.(*UserAnswerTimeCollector)
}
