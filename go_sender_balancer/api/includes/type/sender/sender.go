package sender

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender_balancer/api/includes/type/balancer"
	"go_sender_balancer/api/includes/type/structures"
	"time"
)

// информация о соедиении
type ConnectionInfoStruct struct {
	SenderNodeId int64 `json:"sender_node_id"`

	ConnId      int64  `json:"connection_id"`
	UserId      int64  `json:"user_id"`
	IpAddress   string `json:"ip_address"`
	ConnectedAt int64  `json:"connected_at"`
	UserAgent   string `json:"user_agent"`
	Platform    string `json:"platform"`
	IsFocused   int    `json:"is_focused"`
}

// отправить один ивент всем локальным сендерам
func SendEvent(userConnectionList []structures.UserConnectionStruct, event string, eventVersionList interface{}, wsUsers interface{}, uuid string, routineKey string) []structures.UserConnectionStruct {

	// получаем id всех нод go_sender
	senderNodeIdList := getAllSenderNodeList(userConnectionList)

	// инициализируем список подключений, которые успешно получили эвент
	var sentConnectionList []structures.UserConnectionStruct

	// отправляем запросы на каждую ноду
	for _, item := range senderNodeIdList {

		// отправляем запрос с отправкой сообщения на локальный go_sender
		userList := structures.ConvertUserConnectionListToUserList(userConnectionList)
		userList = doCallPublish(item, userList, event, eventVersionList, wsUsers, uuid, routineKey)

		// формируем список соединений на которые был отправлен запрос
		successSendEventConnection := prepareSuccessSendEventConnectionList(userList, item)

		// добавляем к списку подключений, которые успешно получили эвент
		sentConnectionList = append(sentConnectionList, successSendEventConnection...)
	}

	// возвращаем список подключений, на который успешно отправили эвент
	return sentConnectionList
}

// получаем массив нод на которые отправим эвенты
func getAllSenderNodeList(userConnectionList []structures.UserConnectionStruct) []int64 {

	var senderNodeIdList []int64
	allSenderNodeList := balancer.GetSenderIdList()

	// пробегаемся по массиву пользовательских соединений
	for _, item := range userConnectionList {

		senderNodeIdList = append(senderNodeIdList, item.SenderNodeId)
	}

	// убираем одинаковые
	senderNodeIdList = functions.UniqueInt64(senderNodeIdList)

	// если их нет, кладем в массив все текущие ноды на дпс
	if len(senderNodeIdList) == 0 {
		senderNodeIdList = allSenderNodeList
	}

	return senderNodeIdList
}

// формируем массив со списком соединений на которые успешно отправили события
func prepareSuccessSendEventConnectionList(userList []int64, nodeId int64) []structures.UserConnectionStruct {

	// инициализируем запрос с соединениями на которые бул успешно отправлен запрос
	var successSendEventConnectionList []structures.UserConnectionStruct

	// проходимся по каждому пользователю которому удалось отправить
	for _, item := range userList {

		// добваляем соединение в ответ
		successSendEventConnection := structures.UserConnectionStruct{
			UserId:          item,
			SenderNodeId:    nodeId,
			LastConnectedAt: functions.GetCurrentTimeStamp(),
		}
		successSendEventConnectionList = append(successSendEventConnectionList, successSendEventConnection)
	}

	return successSendEventConnectionList
}

// метод, для получения соединений с всех нод go_sender
func GetAllUserConnectionsInfo(userId int64) []ConnectionInfoStruct {

	// получаем идентификаторы ноды для этого пользователя
	senderNodeList := balancer.GetSenderNodeIdListForUser(userId)

	// инициализцируем переменную, собирающую коннекты со всех go_sender нод
	var output []ConnectionInfoStruct

	// проходим по каждой ноде
	for _, item := range senderNodeList {

		// собираем конекты со всех go_sender нод
		connectionInfoList := doCallSenderGetOnlineConnectionsByUserId(item, userId)
		output = append(output, connectionInfoList...)
	}

	return output
}

// закрывает соедиения на go_sender нодах
func CloseConnectionsByUserId(userId int64) {

	// получаем идентификаторы go_sender ноды для этого пользователя
	senderNodeList := balancer.GetSenderNodeIdListForUser(userId)

	// отправляем запросы на go_sender ноды для закрытия ws подключений
	for _, item := range senderNodeList {

		err := doCallCloseConnectionsByUserId(item, userId)
		if err != nil {

			log.Errorf("%v", err)
			return
		}

		// удаляем из хранилища информацию о соединениях пользователя
		balancer.RemoveUserConnection(userId, item)
	}
}

// отправляем запросы на go_sender ноды для получения онлайна
func GetOnlineOfflineUserList(userList []int64) ([]int64, []int64) {

	var OnlineUserList []int64
	var OfflineUserList []int64

	// пробегаемся по всем пользователям
	userListGroupByNodeId := map[int64][]int64{}
	for _, item := range userList {

		senderNodeList := balancer.GetSenderNodeIdListForUser(item)
		for _, nodeId := range senderNodeList {
			userListGroupByNodeId[nodeId] = append(userListGroupByNodeId[nodeId], item)
		}
	}

	for nodeId, userList := range userListGroupByNodeId {

		onlineUserList, offlineUserList := doCallGetOnlineUsers(nodeId, userList)
		OnlineUserList = append(OnlineUserList, onlineUserList...)
		OfflineUserList = append(OfflineUserList, offlineUserList...)
	}

	return OnlineUserList, OfflineUserList
}

// метод для установки токена пользователю
func SetToken(nodeId int64, token string, userId int64, expire int64, platform string, deviceId string) error {

	response, err := doCallSetToken(nodeId, token, userId, expire, platform, deviceId)
	if err != nil {
		return err
	}

	if response.Status != "ok" {
		return err
	}

	return nil
}

// функция собирает онлайн с ноды go_sender
func GetOnlineUserList(nodeId int64, uuid string, limit int) []UserOnlineDevicesStruct {

	var output []UserOnlineDevicesStruct
	var offset = 0
	var err error
	var response SendGetOnlineUserListRequestResponseStruct

	for {

		response, err = doCallGetOnlineUserList(nodeId, uuid, limit, offset)
		if err != nil {
			break
		}

		output = append(output, response.Response.UserList...)
		if !response.Response.HasNext {
			break
		}
		offset += limit
	}

	if err != nil {

		log.Errorf("%v, ждем 10 сек и пробуем снова", err)
		time.Sleep(10 * time.Second)
		return GetOnlineUserList(nodeId, uuid, limit)
	}
	return output
}
