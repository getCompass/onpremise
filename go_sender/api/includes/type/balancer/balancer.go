package balancer

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/rabbit"
	"go_sender/api/includes/type/structures"
)

// -------------------------------------------------------
// пакет, отвечающий за балансировку пользовательских
// подключений между имеющимися go_sender нодами
// -------------------------------------------------------

// -------------------------------------------------------
// PUBLIC
// -------------------------------------------------------

type Conn struct {
	rabbitSenderBalancerQueue string
	nodeId                    int64
	isEnabled                 bool
	rabbitConn                *rabbit.ConnectionStruct
}

func MakeBalancerConn(rabbitSenderBalancerQueue string, nodeId int64, isEnabled bool, rabbitConn *rabbit.ConnectionStruct) *Conn {

	return &Conn{
		rabbitSenderBalancerQueue: rabbitSenderBalancerQueue,
		nodeId:                    nodeId,
		isEnabled:                 isEnabled,
		rabbitConn:                rabbitConn,
	}
}

// добавляем пользовательское подключение
func AddUserConnection(userId int64) {

	userConnection := structures.UserConnectionStruct{
		UserId: userId,
	}

	// добавляем ноду к пользовательским подключениям
	SenderCache.AddUserConnection(userConnection)
}

// удаляем пользовательское подключение
func RemoveUserConnection(userId int64) {

	// удаляем пользовательское соединение
	SenderCache.DeleteUserConnection(userId)
}

// структура rabbit задачи для отправки пушей из go_pusher
type addConnectionToBalancerRequestStruct struct {
	Method string `json:"method"`
	NodeId int64  `json:"node_id"`
	UserId int64  `json:"user_id"`
}

// добавляем соединение в балансер
func (conn *Conn) AddConnectionToBalancer(userId int64) {

	if !conn.isEnabled {
		return
	}

	balancerRabbitTask := structures.RabbitTask{}

	// создаем объект для отправки Push Notification пользователю
	addConnectionObject := addConnectionToBalancerRequestStruct{
		Method: "talking.addConnectionToBalancer",
		NodeId: conn.nodeId,
		UserId: userId,
	}

	// формируем задачу для rabbit
	balancerRabbitTask = makeAddConnectionRabbitTasks(addConnectionObject, balancerRabbitTask)

	// отправляем в rabbit задачу
	conn.rabbitConn.SendMessageListToQueue(conn.rabbitSenderBalancerQueue, balancerRabbitTask.Messages)
}

// структура rabbit задачи для отправки пушей из go_pusher
type deleteConnectionFromBalancerRequestStruct struct {
	Method                   string `json:"method"`
	NodeId                   int64  `json:"node_id"`
	UserId                   int64  `json:"user_id"`
	ClosedAllUserConnections bool   `json:"closed_all_user_connections"`
}

// удаляем соединение из балансера
func (conn *Conn) DeleteConnectionFromBalancer(userId int64, closedAllUserConnections bool) {

	if !conn.isEnabled {
		return
	}

	balancerRabbitTask := structures.RabbitTask{}

	// создаем объект для отправки Push Notification пользователю
	deleteConnectionObject := deleteConnectionFromBalancerRequestStruct{
		Method:                   "talking.deleteConnectionFromBalancer",
		NodeId:                   conn.nodeId,
		UserId:                   userId,
		ClosedAllUserConnections: closedAllUserConnections,
	}

	// формируем задачу для rabbit
	balancerRabbitTask = makeDeleteConnectionRabbitTasks(deleteConnectionObject, balancerRabbitTask)

	// отправляем в rabbit задачу
	conn.rabbitConn.SendMessageListToQueue(conn.rabbitSenderBalancerQueue, balancerRabbitTask.Messages)
}

// структура rabbit задачи для отправки пушей из go_pusher
type clearConnectionsFromBalancerRequestStruct struct {
	Method string `json:"method"`
	NodeId int64  `json:"node_id"`
}

// удаляем соединение из балансера
func (conn *Conn) ClearConnectionsInBalancer() {

	if !conn.isEnabled {
		return
	}

	balancerRabbitTask := structures.RabbitTask{}

	// создаем объект для отправки Push Notification пользователю
	clearConnectionsObject := clearConnectionsFromBalancerRequestStruct{
		Method: "talking.deleteAllNodeConnectionsFromBalancer",
		NodeId: conn.nodeId,
	}

	// формируем задачу для rabbit
	balancerRabbitTask = makeClearConnectionRabbitTasks(clearConnectionsObject, balancerRabbitTask)

	// отправляем в rabbit задачу
	conn.rabbitConn.SendMessageListToQueue(conn.rabbitSenderBalancerQueue, balancerRabbitTask.Messages)
}

// формируем задачу для отправки пуш уведомления
func makeDeleteConnectionRabbitTasks(deleteConnectionObject deleteConnectionFromBalancerRequestStruct, balancerRabbitTask structures.RabbitTask) structures.RabbitTask {

	jsonMessage, err := json.Marshal(deleteConnectionObject)
	if err != nil {
		log.Error("Не смогли сформировать JSON для запроса на удаление соединения в балансере")
		return structures.RabbitTask{}
	}
	balancerRabbitTask = structures.RabbitTask{
		Messages: append(balancerRabbitTask.Messages, jsonMessage),
	}

	return balancerRabbitTask
}

// формируем задачу для отправки пуш уведомления
func makeAddConnectionRabbitTasks(deleteConnectionObject addConnectionToBalancerRequestStruct, balancerRabbitTask structures.RabbitTask) structures.RabbitTask {

	jsonMessage, err := json.Marshal(deleteConnectionObject)
	if err != nil {
		log.Error("Не смогли сформировать JSON для запроса на добавление соединения в балансер")
		return structures.RabbitTask{}
	}
	balancerRabbitTask = structures.RabbitTask{
		Messages: append(balancerRabbitTask.Messages, jsonMessage),
	}

	return balancerRabbitTask
}

// формируем задачу для отправки пуш уведомления
func makeClearConnectionRabbitTasks(deleteConnectionObject clearConnectionsFromBalancerRequestStruct, balancerRabbitTask structures.RabbitTask) structures.RabbitTask {

	jsonMessage, err := json.Marshal(deleteConnectionObject)
	if err != nil {
		log.Error("Не смогли сформировать JSON для запроса на чистку соединений в балансере")
		return structures.RabbitTask{}
	}
	balancerRabbitTask = structures.RabbitTask{
		Messages: append(balancerRabbitTask.Messages, jsonMessage),
	}

	return balancerRabbitTask
}
