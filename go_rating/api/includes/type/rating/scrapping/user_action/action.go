package user_action

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	Isolation "go_rating/api/includes/type/isolation"
	"go_rating/api/includes/type/rating/collecting/pivot"
	"go_rating/api/system"
	"sync"
	"time"
)

const slicePeriod = 2 * time.Hour // удаляем кэши старее этого значения

const groupsCreated = "groups_created"                            // количество созданных групп
const conversationsRead = "conversations_read"                    // количество прочитанных чатов
const conversationMessagesSent = "conversation_messages_sent"     // количество отправленных сообщений в чате
const conversationReactionsAdded = "conversation_reactions_added" // количество поставленных реакций в чате
const conversationRemindsCreated = "conversation_reminds_created" // количество созданных напоминаний в чате
const calls = "calls"                                             // количество совершенных звонков
const threadsCreated = "threads_created"                          // количество созданных тредов
const threadsRead = "threads_read"                                // количество прочитанных тредов
const threadMessagesSent = "thread_messages_sent"                 // количество отправленных сообщений в тред
const threadReactionsAdded = "thread_reactions_added"             // количество поставленных реакций в треде
const threadRemindsCreated = "thread_reminds_created"             // количество созданных напоминаний в треде

// агрегатор количества действий
// собирает и держит в себе количество действий
type useraction struct {

	// количество действий пользователей разбитое по 15 минуткам
	storedActionUserListBy15Min map[int64]*ActionUserListStruct

	// количество действий в диалогах разбитое по 15 минуткам
	storedActionConversationListBy15Min map[int64]*ActionConversationListStruct
	mx                                  sync.RWMutex // семафор для хранилища
}

// ActionUserListStruct структура объекта
type ActionUserListStruct struct {
	SpaceId        int64
	UserActionList map[int64]*UserActionListStruct
}

// UserActionListStruct структура объекта
type UserActionListStruct struct {
	GroupsCreated              int64
	ConversationsRead          int64
	ConversationMessagesSent   int64
	ConversationReactionsAdded int64
	ConversationRemindsCreated int64
	Calls                      int64
	ThreadsCreated             int64
	ThreadsRead                int64
	ThreadMessagesSent         int64
	ThreadReactionsAdded       int64
	ThreadRemindsCreated       int64
}

// ActionConversationListStruct структура объекта
type ActionConversationListStruct struct {
	SpaceId                int64
	ConversationActionList map[string]*ConversationActionListStruct
}

// ConversationActionListStruct структура объекта
type ConversationActionListStruct struct {
	GroupsCreated              int64
	ConversationsRead          int64
	ConversationMessagesSent   int64
	ConversationReactionsAdded int64
	ConversationRemindsCreated int64
	Calls                      int64
	ThreadsCreated             int64
	ThreadsRead                int64
	ThreadMessagesSent         int64
	ThreadReactionsAdded       int64
	ThreadRemindsCreated       int64
}

// makeUserAction создает экземпляр useraction
func makeUserAction(isolation *Isolation.Isolation) *useraction {

	mAction := &useraction{
		storedActionUserListBy15Min:         map[int64]*ActionUserListStruct{},
		storedActionConversationListBy15Min: map[int64]*ActionConversationListStruct{},
		mx:                                  sync.RWMutex{},
	}

	go mAction.routine(isolation)
	return mAction
}

// основная рутина жизненного цикла экземпляра useraction
func (m *useraction) routine(isolation *Isolation.Isolation) {

	Isolation.Inc("useraction-life-routine")

	// добавляем экземпляр в коллектор и объявляем вызов unregister колбека
	unregister := pivot.LeaseActionCollector(Isolation.Global()).RegisterSource("useraction:"+isolation.GetUniq(), m)
	defer func() {

		unregister()
		Isolation.Dec("useraction-life-routine")
	}()

	for {

		select {
		case <-time.After(time.Minute * 15):

			// удаляем все устаревшие записи
			m.purgeUserAction(time.Now().Unix()-int64(slicePeriod.Seconds()), true)
			m.purgeConversationAction(time.Now().Unix()-int64(slicePeriod.Seconds()), true)

		case <-isolation.GetContext().Done():

			return
		}
	}
}

// инкрементим количество действий у пользователя и диалога в хранилище
func (m *useraction) push(spaceId int64, userId int64, action string, conversationMap string, isHuman int64) {

	if err := m.pushOne(spaceId, userId, action, conversationMap, isHuman); err != nil {
		log.Errorf("passed bad action %s", err.Error())
	}
}

// инкрементим количество действий у пользователя и диалога в хранилище
func (m *useraction) pushOne(spaceId int64, userId int64, action string, conversationMap string, isHuman int64) error {

	m.mx.Lock()
	defer m.mx.Unlock()

	min15Start := system.Min15Start()

	err := m.pushUserAction(min15Start, spaceId, userId, action, isHuman)
	if err != nil {
		log.Errorf("push user action failed %v", err)
	}

	err = m.pushConversationAction(min15Start, spaceId, conversationMap, action)
	if err != nil {
		log.Errorf("push conversation action failed %v", err)
	}

	return nil
}

// инкрементим количество действий пользователя
func (m *useraction) pushUserAction(min15Start int64, spaceId int64, userId int64, action string, isHuman int64) error {

	min15Cache, exist := m.storedActionUserListBy15Min[min15Start]
	if !exist {

		min15Cache = &ActionUserListStruct{
			SpaceId:        spaceId,
			UserActionList: map[int64]*UserActionListStruct{},
		}
		m.storedActionUserListBy15Min[min15Start] = min15Cache
	}

	userActionCount, exist := min15Cache.UserActionList[userId]
	if !exist {

		userActionCount = &UserActionListStruct{
			GroupsCreated:              0,
			ConversationsRead:          0,
			ConversationMessagesSent:   0,
			ConversationReactionsAdded: 0,
			ConversationRemindsCreated: 0,
			Calls:                      0,
			ThreadsCreated:             0,
			ThreadsRead:                0,
			ThreadMessagesSent:         0,
			ThreadReactionsAdded:       0,
			ThreadRemindsCreated:       0,
		}
	}

	switch action {

	case groupsCreated:
		userActionCount.GroupsCreated++
		break

	case conversationsRead:
		userActionCount.ConversationsRead++
		break

	case conversationMessagesSent:

		if isHuman == 1 {
			userActionCount.ConversationMessagesSent++
		}
		break

	case conversationReactionsAdded:
		userActionCount.ConversationReactionsAdded++
		break

	case conversationRemindsCreated:
		userActionCount.ConversationRemindsCreated++
		break

	case calls:
		userActionCount.Calls++
		break

	case threadsCreated:

		if isHuman == 1 {
			userActionCount.ThreadsCreated++
		}
		break

	case threadsRead:
		userActionCount.ThreadsRead++
		break

	case threadMessagesSent:

		if isHuman == 1 {
			userActionCount.ThreadMessagesSent++
		}
		break

	case threadReactionsAdded:
		userActionCount.ThreadReactionsAdded++
		break

	case threadRemindsCreated:
		userActionCount.ThreadRemindsCreated++
		break

	default:
		return fmt.Errorf("undefined user action: %s", action)
	}

	min15Cache.UserActionList[userId] = userActionCount

	return nil
}

// инкрементим количество действий в диалоге
func (m *useraction) pushConversationAction(min15Start int64, spaceId int64, conversationMap string, action string) error {

	min15Cache, exist := m.storedActionConversationListBy15Min[min15Start]
	if !exist {

		min15Cache = &ActionConversationListStruct{
			SpaceId:                spaceId,
			ConversationActionList: map[string]*ConversationActionListStruct{},
		}
		m.storedActionConversationListBy15Min[min15Start] = min15Cache
	}

	conversationActionCount, exist := min15Cache.ConversationActionList[conversationMap]
	if !exist {

		conversationActionCount = &ConversationActionListStruct{
			GroupsCreated:              0,
			ConversationsRead:          0,
			ConversationMessagesSent:   0,
			ConversationReactionsAdded: 0,
			ConversationRemindsCreated: 0,
			Calls:                      0,
			ThreadsCreated:             0,
			ThreadsRead:                0,
			ThreadMessagesSent:         0,
			ThreadReactionsAdded:       0,
			ThreadRemindsCreated:       0,
		}
	}

	switch action {

	case groupsCreated:
		conversationActionCount.GroupsCreated++
		break

	case conversationsRead:
		conversationActionCount.ConversationsRead++
		break

	case conversationMessagesSent:

		conversationActionCount.ConversationMessagesSent++
		break

	case conversationReactionsAdded:
		conversationActionCount.ConversationReactionsAdded++
		break

	case conversationRemindsCreated:
		conversationActionCount.ConversationRemindsCreated++
		break

	case calls:
		conversationActionCount.Calls++
		break

	case threadsCreated:

		conversationActionCount.ThreadsCreated++
		break

	case threadsRead:
		conversationActionCount.ThreadsRead++
		break

	case threadMessagesSent:

		conversationActionCount.ThreadMessagesSent++
		break

	case threadReactionsAdded:
		conversationActionCount.ThreadReactionsAdded++
		break

	case threadRemindsCreated:
		conversationActionCount.ThreadRemindsCreated++
		break

	default:
		return fmt.Errorf("undefined conversation action: %s", action)
	}

	min15Cache.ConversationActionList[conversationMap] = conversationActionCount

	return nil
}

// CollectUserAction возвращает все 15-ти минутки
// после сбора данных очищает хранилище
func (m *useraction) CollectUserAction() []*pivot.UserActionListStruct {

	var output []*pivot.UserActionListStruct

	m.mx.Lock()
	defer m.mx.Unlock()

	// проходим по всем 15-ти минуткам
	actionSendTill := int64(0)
	for min15Start, storedUserActionList := range m.storedActionUserListBy15Min {

		// текущий кэш не трогаем
		if min15Start >= system.Min15Start() {
			continue
		}

		// сохраняем до какой максимальной 15-ти минутки почистили
		if min15Start > actionSendTill {
			actionSendTill = min15Start
		}

		userList := make([]*pivot.UserListStruct, 0)

		// пробегаемся по всем 15-ти минуткам
		for userId, item := range storedUserActionList.UserActionList {

			userList = append(userList, &pivot.UserListStruct{
				UserId: userId,
				ActionList: pivot.ActionListStruct{
					GroupsCreated:              item.GroupsCreated,
					ConversationsRead:          item.ConversationsRead,
					ConversationMessagesSent:   item.ConversationMessagesSent,
					ConversationReactionsAdded: item.ConversationReactionsAdded,
					ConversationRemindsCreated: item.ConversationRemindsCreated,
					Calls:                      item.Calls,
					ThreadsCreated:             item.ThreadsCreated,
					ThreadsRead:                item.ThreadsRead,
					ThreadMessagesSent:         item.ThreadMessagesSent,
					ThreadReactionsAdded:       item.ThreadReactionsAdded,
					ThreadRemindsCreated:       item.ThreadRemindsCreated,
				},
			})
		}

		actionList := &pivot.UserActionListStruct{
			SpaceId:  storedUserActionList.SpaceId,
			ActionAt: min15Start,
			UserList: userList,
		}

		output = append(output, actionList)
	}

	// запускаем очистку хранилища если что-то очистили
	if actionSendTill > 0 {

		m.purgeUserAction(actionSendTill, false)
		log.Infof("user action list was collected till %d", actionSendTill)
	}

	return output
}

// выполняет очистку записей
func (m *useraction) purgeUserAction(actionSendTill int64, needBlock bool) {

	// спорное решение, но позволяет держать код чуть чище в других местах
	// при сборе данных нужно почистить хранилище, не снимая блокировки в основном хранилище
	if needBlock {

		m.mx.Lock()
		defer m.mx.Unlock()
	}

	for min15Start := range m.storedActionUserListBy15Min {

		if min15Start <= actionSendTill {

			delete(m.storedActionUserListBy15Min, min15Start)
			log.Infof("user action list for %v was purged", min15Start)
		}
	}
}

// CollectConversationAction возвращает все 15-ти минутки
// после сбора данных очищает хранилище;
func (m *useraction) CollectConversationAction() []*pivot.ConversationActionListStruct {

	var output []*pivot.ConversationActionListStruct

	m.mx.Lock()
	defer m.mx.Unlock()

	// проходим по всем 15-ти минуткам
	actionSendTill := int64(0)
	for min15Start, storedConversationActionList := range m.storedActionConversationListBy15Min {

		// текущий кэш не трогаем
		if min15Start >= system.Min15Start() {
			continue
		}

		// сохраняем до какой максимальной 15-ти минутки почистили
		if min15Start > actionSendTill {
			actionSendTill = min15Start
		}

		conversationList := make([]*pivot.ConversationListStruct, 0)

		// пробегаемся по всем 15-ти минуткам
		for conversationMap, item := range storedConversationActionList.ConversationActionList {

			conversationList = append(conversationList, &pivot.ConversationListStruct{
				ConversationMap: conversationMap,
				ActionList: pivot.ActionListStruct{
					GroupsCreated:              item.GroupsCreated,
					ConversationsRead:          item.ConversationsRead,
					ConversationMessagesSent:   item.ConversationMessagesSent,
					ConversationReactionsAdded: item.ConversationReactionsAdded,
					ConversationRemindsCreated: item.ConversationRemindsCreated,
					Calls:                      item.Calls,
					ThreadsCreated:             item.ThreadsCreated,
					ThreadsRead:                item.ThreadsRead,
					ThreadMessagesSent:         item.ThreadMessagesSent,
					ThreadReactionsAdded:       item.ThreadReactionsAdded,
					ThreadRemindsCreated:       item.ThreadRemindsCreated,
				},
			})
		}

		actionList := &pivot.ConversationActionListStruct{
			SpaceId:          storedConversationActionList.SpaceId,
			ActionAt:         min15Start,
			ConversationList: conversationList,
		}

		output = append(output, actionList)
	}

	// запускаем очистку хранилища если что-то очистили
	if actionSendTill > 0 {

		m.purgeConversationAction(actionSendTill, false)
		log.Infof("conversation action list was collected till %d", actionSendTill)
	}

	return output
}

// выполняет очистку записей
func (m *useraction) purgeConversationAction(actionSendTill int64, needBlock bool) {

	// спорное решение, но позволяет держать код чуть чище в других местах
	// при сборе данных нужно почистить хранилище, не снимая блокировки в основном хранилище
	if needBlock {

		m.mx.Lock()
		defer m.mx.Unlock()
	}

	for min15Start := range m.storedActionConversationListBy15Min {

		if min15Start <= actionSendTill {

			delete(m.storedActionConversationListBy15Min, min15Start)
			log.Infof("conversation action list for %v was purged", min15Start)
		}
	}
}

// --------------------------------------------
// region методы пакета, работающие с изоляцией
// --------------------------------------------

// Push инкрементим количество действий в агрегаторе
func Push(isolation *Isolation.Isolation, userId int64, action string, conversationMap string, isHuman int64) error {

	mAction := leaseUserAction(isolation)
	if mAction == nil {
		return fmt.Errorf("isolation doesn't have useraction instance")
	}

	mAction.push(isolation.GetCompanyId(), userId, action, conversationMap, isHuman)
	return nil
}
