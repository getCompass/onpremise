package readMessageObserver

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	grpcCompanyCacheClient "go_company/api/includes/grpc/client/company_cache"
	grpсSenderClient "go_company/api/includes/grpc/client/sender"
	dbConversationDynamic "go_company/api/includes/type/db/company_conversation/conversation_dynamic"
	dbConversationMeta "go_company/api/includes/type/db/company_conversation/conversation_meta"
	dbConversationMessageReadParticipants "go_company/api/includes/type/db/company_conversation/message_read_participants"
	dbThreadMessageReadParticipants "go_company/api/includes/type/db/company_thread/message_read_participants"
	dbThreadDynamic "go_company/api/includes/type/db/company_thread/thread_dynamic"
	dbThreadMeta "go_company/api/includes/type/db/company_thread/thread_meta"
	Isolation "go_company/api/includes/type/isolation"
	"go_company/api/includes/type/sender"
	senderEvents "go_company/api/includes/type/sender/events"
	eventConversationLastMessageRead "go_company/api/includes/type/sender/events/conversation_last_message_read"
	eventThreadLastMessageRead "go_company/api/includes/type/sender/events/thread_last_message_read"
	"go_company/api/includes/type/structures"
	"maps"
	"sort"
	"strconv"
	"strings"
)

const SHOW_MESSAGE_READ_STATUS = "show_message_read_status"

var CONFIG_DEFAULT_VALUES = map[string]int{
	SHOW_MESSAGE_READ_STATUS: 1,
}

// HandleReadParticipants обратотать прочитавших участников
func HandleReadParticipants(isolation *Isolation.Isolation, readParticipants map[string]structures.ReadMessageCacheItem) error {

	log.Infof("Взяли в работу задачу по прочтению")

	// получить информацию о пользователях чатов
	userIdList := make([]int64, 0, len(readParticipants))

	for _, readParticipant := range readParticipants {
		userIdList = append(userIdList, readParticipant.UserId)
	}

	// получаем значения конфигов, чтобы понять, кому и какие вски надо отправить
	cMap, notFoundList, err := grpcCompanyCacheClient.ConfigGetList(isolation, []string{SHOW_MESSAGE_READ_STATUS})

	if err != nil {

		log.Errorf("Не смогли получить информацию о конфиге")
		return err
	}

	// для ненайденых устанавливаем значения по умолчанию
	for _, key := range notFoundList {
		cMap[key] = CONFIG_DEFAULT_VALUES[key]
	}

	needSendWs := cMap[SHOW_MESSAGE_READ_STATUS] != 0

	// группируем кеш по сущностям
	entityReadParticipantsMap, conversationIdList, threadIdList := groupReadParticipants(readParticipants)

	// получаем список мет для чатов
	conversationMetaList, err := dbConversationMeta.GetAllUsers(isolation.Context, isolation.GetCompanyId(), isolation.CompanyConversationConn, conversationIdList)

	if err != nil {
		return err
	}

	// получаем списк мет для тредов
	threadMetaList, err := dbThreadMeta.GetAllUsers(isolation.Context, isolation.GetCompanyId(), isolation.CompanyThreadConn, threadIdList)

	if err != nil {
		return err
	}

	// выделяем под ивенты место под максимальное количество отправок ws
	eventList := make([]*sender.Event, 0, len(entityReadParticipantsMap)*2)

	// для каждого чата или треда обновляем счетчики
	for entityMap, entityReadParticipants := range entityReadParticipantsMap {

		switch entityReadParticipants.EntityType {
		case "conversation":

			key := dbConversationMeta.GetRowKey(entityReadParticipants.EntityMetaId, entityReadParticipants.DbShard)
			if _, exist := conversationMetaList[key]; !exist {
				continue
			}

			e, err := handleConversation(isolation, conversationMetaList[key], entityReadParticipants)

			if err != nil {
				log.Errorf("Не смогли обновить чат %v %v", entityMap, err.Error())
				continue
			}

			eventList = append(eventList, e...)

		case "thread":

			key := dbThreadMeta.GetRowKey(entityReadParticipants.EntityMetaId, entityReadParticipants.DbShard)
			if _, exist := threadMetaList[key]; !exist {
				continue
			}

			e, err := handleThread(isolation, threadMetaList[key], entityReadParticipants)

			if err != nil {
				log.Errorf("Не смогли обновить тред %v %v", entityMap, err.Error())
				continue
			}

			eventList = append(eventList, e...)
		}
	}

	// если нужно отправить ws - отправляем
	if len(eventList) > 0 && needSendWs {
		grpсSenderClient.SendEventBatching(isolation, eventList)
	}

	return nil
}

// сгруппировать прочитавших пользователей
func groupReadParticipants(readParticipants map[string]structures.ReadMessageCacheItem) (map[string]*structures.EntityReadMessageStruct, map[int][]int64, map[int][]int64) {

	conversationMapIdList := make(map[int][]int64)
	threadMapIdList := make(map[int][]int64)

	entityReadParticipantsMap := make(map[string]*structures.EntityReadMessageStruct)

	// группируем полученный кеш по чатам/тредам, а внутри чатов/тредов - по сообщениям
	for key, readParticipant := range readParticipants {

		keySlice := strings.Split(key, "||")
		_, err := strconv.ParseInt(keySlice[0], 10, 64)

		if err != nil {
			continue
		}

		entityMap := keySlice[1]

		// распределяем мапы по слайсам
		switch readParticipant.EntityType {
		case "conversation":

			if conversationMapIdList[readParticipant.DbShard] == nil {
				conversationMapIdList[readParticipant.DbShard] = make([]int64, 0, len(readParticipants))
			}
			conversationMapIdList[readParticipant.DbShard] = append(conversationMapIdList[readParticipant.DbShard], readParticipant.EntityMetaId)
		case "thread":

			if threadMapIdList[readParticipant.DbShard] == nil {
				threadMapIdList[readParticipant.DbShard] = make([]int64, 0, len(readParticipants))
			}
			threadMapIdList[readParticipant.DbShard] = append(threadMapIdList[readParticipant.DbShard], readParticipant.EntityMetaId)
		}

		// обновляем мапу с прочитавшими пользователями, сгрупированным по чатам/тредам
		if _, exists := entityReadParticipantsMap[entityMap]; !exists {
			entityReadParticipantsMap[entityMap] = createEntityReadParticipantsItem(entityMap, readParticipant)
		} else {
			entityReadParticipantsMap[entityMap] = updateEntityReadParticipantsItem(entityReadParticipantsMap[entityMap], readParticipant)
		}
	}

	return entityReadParticipantsMap, conversationMapIdList, threadMapIdList
}

// создать новую запись прочитавшего
func createEntityReadParticipantsItem(entityMap string, readParticipant structures.ReadMessageCacheItem) *structures.EntityReadMessageStruct {

	return &structures.EntityReadMessageStruct{
		EntityMap:    entityMap,
		EntityType:   readParticipant.EntityType,
		EntityKey:    readParticipant.EntityKey,
		EntityMetaId: readParticipant.EntityMetaId,
		TableShard:   readParticipant.TableShard,
		DbShard:      readParticipant.DbShard,

		ReadMessageParticipants: map[int64]*structures.ReadMessageStruct{
			readParticipant.EntityMessageIndex: {
				EntityMessageIndex:      readParticipant.EntityMessageIndex,
				MessageMap:              readParticipant.MessageMap,
				MessageKey:              readParticipant.MessageKey,
				MessageCreatedAt:        readParticipant.MessageCreatedAt,
				HideReadParticipantList: readParticipant.HideReadParticipantList,
				ReadParticipants: map[int64]*structures.ReadParticipant{
					readParticipant.UserId: {
						UserId: readParticipant.UserId,
						ReadAt: readParticipant.ReadAt,
					},
				},
			},
		},
	}
}

// обновить запись прочитавших
func updateEntityReadParticipantsItem(erp *structures.EntityReadMessageStruct, readParticipant structures.ReadMessageCacheItem) *structures.EntityReadMessageStruct {

	if _, exist := erp.ReadMessageParticipants[readParticipant.EntityMessageIndex]; !exist {

		erp.ReadMessageParticipants[readParticipant.EntityMessageIndex] = &structures.ReadMessageStruct{
			EntityMessageIndex:      readParticipant.EntityMessageIndex,
			MessageMap:              readParticipant.MessageMap,
			MessageKey:              readParticipant.MessageKey,
			MessageCreatedAt:        readParticipant.MessageCreatedAt,
			HideReadParticipantList: readParticipant.HideReadParticipantList,
			ReadParticipants:        make(map[int64]*structures.ReadParticipant),
		}
	}

	v := erp.ReadMessageParticipants[readParticipant.EntityMessageIndex]

	v.ReadParticipants[readParticipant.UserId] = &structures.ReadParticipant{
		UserId: readParticipant.UserId,
		ReadAt: readParticipant.ReadAt,
	}

	return erp
}

// обрабатываем чат
func handleConversation(isolation *Isolation.Isolation, conversationMeta dbConversationMeta.UsersRow, conversationReadMessage *structures.EntityReadMessageStruct) ([]*sender.Event, error) {

	eventList := make([]*sender.Event, 0, 2)
	userIdList := make([]int64, 0, len(conversationMeta.Users))

	for userId, _ := range conversationMeta.Users {
		userIdList = append(userIdList, userId)
	}

	needSendWs, maxReadMessage, err := handleConversationDb(isolation, conversationReadMessage.EntityMap, conversationReadMessage.TableShard, conversationReadMessage.ReadMessageParticipants)

	if err != nil {
		return eventList, err
	}

	if needSendWs {
		eventList = prepareEventList(isolation, conversationReadMessage.EntityType, conversationReadMessage.EntityKey, maxReadMessage, userIdList)
	}

	return eventList, nil
}

// добавляем данные в БД для чата
func handleConversationDb(isolation *Isolation.Isolation, conversationMap string, tableShard int, readParticipantsByMessageIndex map[int64]*structures.ReadMessageStruct) (bool, *structures.ReadMessageStruct, error) {

	needSendWs := false
	tx, err := isolation.CompanyConversationConn.Conn.Begin()

	if err != nil {
		return false, nil, err
	}

	// если где то отвалимся - делаем ролбек, иначе коммитимся
	defer func() {

		if err != nil {
			tx.Rollback()
		}

		tx.Commit()

	}()

	for _, readMessage := range readParticipantsByMessageIndex {

		insertRowList := make([]dbConversationMessageReadParticipants.Row, 0, len(readMessage.ReadParticipants))

		for _, insertUser := range readMessage.ReadParticipants {

			insertRowList = append(insertRowList, dbConversationMessageReadParticipants.Row{
				ConversationMap:          conversationMap,
				UserId:                   insertUser.UserId,
				ConversationMessageIndex: readMessage.EntityMessageIndex,
				ReadAt:                   insertUser.ReadAt,
				MessageCreatedAt:         readMessage.MessageCreatedAt,
				CreatedAt:                functions.GetCurrentTimeStamp(),
				UpdatedAt:                0,
				MessageMap:               readMessage.MessageMap,
			})
		}

		err = dbConversationMessageReadParticipants.InsertArray(isolation.Context, tableShard, tx, insertRowList)

		if err != nil {
			return false, nil, err
		}
	}

	maxIndex, maxReadMessage := getMaxMessage(readParticipantsByMessageIndex)

	// если последнее сообщение с участниками не найдено, то завершаем выполнение
	if maxReadMessage == nil {
		return false, nil, nil
	}

	// формируем список id пользователей для dynamic записи
	maxReadMessageUserMap := make(map[int64]int64, len(maxReadMessage.ReadParticipants))

	for _, v := range maxReadMessage.ReadParticipants {
		maxReadMessageUserMap[v.UserId] = v.ReadAt
	}

	lastReadMessage, err := dbConversationDynamic.GetLastReadMessageForUpdate(isolation.Context, tx, conversationMap)

	if err != nil {
		return false, nil, err
	}

	// если прочитанное сообщение новее - обновляем его
	if maxIndex > lastReadMessage.ConversationMessageIndex {

		lastReadMessage = &dbConversationDynamic.LastReadMessage{
			MessageMap:               maxReadMessage.MessageMap,
			ConversationMessageIndex: maxReadMessage.EntityMessageIndex,
			ReadParticipants:         maxReadMessageUserMap,
		}
		err = dbConversationDynamic.UpdateLastMessage(isolation.Context, tx, conversationMap, lastReadMessage)

		if err != nil {
			return false, nil, err
		}

		needSendWs = true
	}

	// если прочитали текущее сообщение - обновляем участников, добавляя новых
	if maxIndex == lastReadMessage.ConversationMessageIndex {

		maps.Copy(maxReadMessageUserMap, lastReadMessage.ReadParticipants)

		lastReadMessage = &dbConversationDynamic.LastReadMessage{
			MessageMap:               lastReadMessage.MessageMap,
			ConversationMessageIndex: lastReadMessage.ConversationMessageIndex,
			ReadParticipants:         maxReadMessageUserMap,
		}
		err = dbConversationDynamic.UpdateLastMessage(isolation.Context, tx, conversationMap, lastReadMessage)

		if err != nil {
			return false, nil, err
		}

		needSendWs = true
	}

	readParticipants := make(map[int64]*structures.ReadParticipant, len(maxReadMessageUserMap))

	for userId, readAt := range lastReadMessage.ReadParticipants {

		readParticipants[userId] = &structures.ReadParticipant{
			UserId: userId,
			ReadAt: readAt,
		}
	}

	maxReadMessage.ReadParticipants = readParticipants

	return needSendWs, maxReadMessage, nil
}

// обработать тред
func handleThread(isolation *Isolation.Isolation, threadMeta dbThreadMeta.UsersRow, threadReadMessage *structures.EntityReadMessageStruct) ([]*sender.Event, error) {

	eventList := make([]*sender.Event, 0, 2)
	userIdList := make([]int64, 0, len(threadMeta.Users))

	for userId, _ := range threadMeta.Users {
		userIdList = append(userIdList, userId)
	}

	needSendWs, maxReadMessage, err := handleThreadDb(isolation, threadReadMessage.EntityMap, threadReadMessage.TableShard, threadReadMessage.ReadMessageParticipants)

	if err != nil {

		log.Errorf("Не смогли обновить тред %v %v", threadReadMessage.EntityMap, err.Error())
		return eventList, err
	}

	if needSendWs {
		eventList = prepareEventList(isolation, threadReadMessage.EntityType, threadReadMessage.EntityKey, maxReadMessage, userIdList)
	}

	return eventList, nil
}

// добавляем данные в БД треда
func handleThreadDb(isolation *Isolation.Isolation, threadMap string, tableShard int, readParticipantsByMessageIndex map[int64]*structures.ReadMessageStruct) (bool, *structures.ReadMessageStruct, error) {

	needSendWs := false
	tx, err := isolation.CompanyThreadConn.Conn.Begin()

	// если где то отвалимся - делаем ролбек, иначе коммитимся
	defer func() {

		if err != nil {
			tx.Rollback()
		}

		tx.Commit()

	}()

	for _, readParticipants := range readParticipantsByMessageIndex {

		insertRowList := make([]dbThreadMessageReadParticipants.Row, 0, len(readParticipants.ReadParticipants))

		for _, insertUser := range readParticipants.ReadParticipants {

			insertRowList = append(insertRowList, dbThreadMessageReadParticipants.Row{
				ThreadMap:          threadMap,
				ThreadMessageIndex: readParticipants.EntityMessageIndex,
				UserId:             insertUser.UserId,
				ReadAt:             insertUser.ReadAt,
				MessageCreatedAt:   readParticipants.MessageCreatedAt,
				CreatedAt:          functions.GetCurrentTimeStamp(),
				UpdatedAt:          0,
				MessageMap:         readParticipants.MessageMap,
			})
		}
		err = dbThreadMessageReadParticipants.InsertArray(isolation.Context, tableShard, tx, insertRowList)

		if err != nil {
			return false, nil, err
		}
	}

	maxIndex, maxReadMessage := getMaxMessage(readParticipantsByMessageIndex)

	// если последнее сообщение с участниками не найдено, то завершаем выполнение
	if maxReadMessage == nil {
		return false, nil, nil
	}

	// формируем список id пользователей для dynamic заиси
	maxReadMessageUserMap := make(map[int64]int64, len(maxReadMessage.ReadParticipants))

	for _, v := range maxReadMessage.ReadParticipants {
		maxReadMessageUserMap[v.UserId] = v.ReadAt
	}

	lastReadMessage, err := dbThreadDynamic.GetLastReadMessageForUpdate(isolation.Context, tx, threadMap)

	if err != nil {
		return false, nil, err
	}

	if maxIndex > lastReadMessage.ThreadMessageIndex {

		lastReadMessage = &dbThreadDynamic.LastReadMessage{
			MessageMap:         maxReadMessage.MessageMap,
			ThreadMessageIndex: maxReadMessage.EntityMessageIndex,
			ReadParticipants:   maxReadMessageUserMap,
		}
		err = dbThreadDynamic.UpdateLastMessage(isolation.Context, tx, threadMap, lastReadMessage)

		if err != nil {
			return false, nil, err
		}

		needSendWs = true
	}

	if maxIndex == lastReadMessage.ThreadMessageIndex {

		maps.Copy(maxReadMessageUserMap, lastReadMessage.ReadParticipants)

		lastReadMessage = &dbThreadDynamic.LastReadMessage{
			MessageMap:         lastReadMessage.MessageMap,
			ThreadMessageIndex: lastReadMessage.ThreadMessageIndex,
			ReadParticipants:   maxReadMessageUserMap,
		}
		err = dbThreadDynamic.UpdateLastMessage(isolation.Context, tx, threadMap, lastReadMessage)

		if err != nil {
			return false, nil, err
		}

		needSendWs = true
	}

	readParticipants := make(map[int64]*structures.ReadParticipant, len(maxReadMessageUserMap))

	for userId, readAt := range lastReadMessage.ReadParticipants {

		readParticipants[userId] = &structures.ReadParticipant{
			UserId: userId,
			ReadAt: readAt,
		}
	}

	maxReadMessage.ReadParticipants = readParticipants

	return needSendWs, maxReadMessage, nil
}

// получить последнее сообщение
func getMaxMessage(readParticipantsByMessageIndex map[int64]*structures.ReadMessageStruct) (int64, *structures.ReadMessageStruct) {

	maxIndex := int64(0)
	var maxReadMessage *structures.ReadMessageStruct

	for n, readMessage := range readParticipantsByMessageIndex {

		// удаляем участников, которых нужно скрыть для плашки
		for _, v := range readMessage.HideReadParticipantList {
			delete(readMessage.ReadParticipants, v)
		}

		// если остались участники после удаления, то сообщение можно обновлять
		if n > maxIndex && len(readMessage.ReadParticipants) != 0 {

			maxIndex = n
			maxReadMessage = readMessage
		}
	}
	return maxIndex, maxReadMessage
}

// подготовить список ивентов
func prepareEventList(isolation *Isolation.Isolation, entityType string, entityKey string, readMessage *structures.ReadMessageStruct, users []int64) []*sender.Event {

	eventName := "event." + entityType + ".last_message_read"

	eventList := make([]*sender.Event, 0, 2)

	if len(users) == 0 {
		return eventList
	}

	readParticipantList := make([]*structures.ReadParticipant, 0, len(readMessage.ReadParticipants))
	readParticipantIdList := make([]int64, 0, len(readMessage.ReadParticipants))

	for _, readParticipant := range readMessage.ReadParticipants {

		readParticipantList = append(readParticipantList, readParticipant)
		readParticipantIdList = append(readParticipantIdList, readParticipant.UserId)
	}

	// сортируем по времени прочтения
	sort.Slice(readParticipantList, func(i, j int) bool {
		return readParticipantList[i].ReadAt > readParticipantList[j].ReadAt
	})

	eventDataV1 := makeEventDataV1(entityType, entityKey, readMessage.MessageKey, readMessage.EntityMessageIndex, len(readMessage.ReadParticipants), readParticipantList)
	eventVersionList := make([]*sender.EventVersionItemStruct, 0, 1)
	eventVersionList = append(
		eventVersionList,
		&sender.EventVersionItemStruct{
			Version: eventDataV1.GetVersion(),
			Data:    eventDataV1.GetData(),
		},
	)

	eventList = append(eventList, sender.MakeEvent(
		isolation,
		eventName,
		eventVersionList,
		users,
		readParticipantIdList,
	),
	)

	return eventList
}

// подготовить структуру для ивента
func makeEventDataV1(entityType string, entityKey string, messageKey string, entityMessageIndex int64, readParticipantCount int, readParticipantList []*structures.ReadParticipant) senderEvents.EventVersionedInterface {

	switch entityType {
	case "conversation":
		return eventConversationLastMessageRead.MakeV1(entityKey, messageKey, entityMessageIndex, readParticipantCount, readParticipantList)
	case "thread":
		return eventThreadLastMessageRead.MakeV1(entityKey, messageKey, entityMessageIndex, readParticipantCount, readParticipantList)
	default:
		return nil
	}
}
