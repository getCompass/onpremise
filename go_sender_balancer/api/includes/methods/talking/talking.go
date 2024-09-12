package talking

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender_balancer/api/conf"
	"go_sender_balancer/api/includes/gateway/tcp"
	"go_sender_balancer/api/includes/type/balancer"
	"go_sender_balancer/api/includes/type/sender"
	"go_sender_balancer/api/includes/type/structures"
	"google.golang.org/grpc/status"
	"sync"
	"time"
)

var (
	routineKeyStore = sync.Map{}
)

// метод для установки пользовательского токена на go_sender для авторизации подключения
// принимает параметры: user_id int64, token string, expire	int64
// обращается к ноде go_sender, чтобы тот сохранил токен к себе
func SetToken(Token string, Platform string, DeviceId string, UserId int64, Expire int64) (int64, error) {

	if UserId < 1 {
		return 0, status.Error(401, "passed bad user_id")
	}

	if len(Token) < 1 {
		return 0, status.Error(407, "passed incorrect token")
	}

	var skippedNodeList []int64
	for {

		// получаем конфигурацию go_sender, на который необходимо установить токен
		senderNodeId := balancer.GetSenderNodeId(skippedNodeList)
		if senderNodeId == -1 {
			break
		}

		// отправляем socket запрос на go_sender, для установки токена
		err := tcp.SetToken(senderNodeId, Token, UserId, Expire, Platform, DeviceId)
		if err != nil {
			skippedNodeList = append(skippedNodeList, senderNodeId)
			continue
		}

		return senderNodeId, nil
	}

	log.Infof("failure to find node\r\n")
	return 0, status.Error(408, "failure to find node")
}

// метод для отправки WS событий на активные подключения пользователей
// nosemgrep
func SendEvent(userList []structures.UserStruct, event string, eventVersionList interface{}, pushData interface{}, wsUsers interface{}, uuid string, routineKey string, isNeedPush int, channel string) {

	// запускаем в рутине чтобы выполнение шло асинхронно
	go func() {

		waitRoutineKey(routineKey)
		defer doneRoutineKey(routineKey)

		// заческаем начло времени выполнения метода
		start := time.Now()

		// получаем все соединения пользователей
		userConnectionList, needPushUserIdList := GetUserConnectionList(userList)

		// отправляем запросы на ноды
		if len(userConnectionList) > 0 {
			tcp.SendEvent(userConnectionList, event, eventVersionList, wsUsers, uuid, routineKey, channel)
		}

		if len(needPushUserIdList) > 0 && isNeedPush == 1 {
			tcp.SendPush(needPushUserIdList, pushData)
		}

		// логируем окончание выполнения метода и сохраняем аналитику
		end := time.Since(start)
		log.Infof("Function executing time: %d\r\n", end)
	}()
}

// отправляем событие всем подключенным пользователям
// nosemgrep
func BroadcastEvent(Event string, EventVersionList interface{}, WSUsers interface{}, uuid string, routineKey string, channel string) {

	go func() {

		// засекаем начло времени выполнения метода
		start := time.Now()

		for _, node := range conf.GetShardingConfig().Go["sender"].Nodes {

			// передаем по значению, чтобы замыкание не поломалось
			go func(node conf.GoNodeShardingStruct) {

				waitRoutineKey(routineKey)
				defer doneRoutineKey(routineKey)

				sender.Broadcast(node.Id, Event, EventVersionList, WSUsers, uuid, routineKey, channel)
			}(node)
		}

		// логируем окончание выполнения метода и сохраняем аналитику
		end := time.Since(start)
		log.Infof("Function executing time: %d\r\n", end)
	}()
}

// метод для получения списка ws соединений по идентификатору пользователя (userId)
func GetOnlineConnectionsByUserId(UserId int64) ([]tcp.ConnectionInfoStruct, error) {

	if UserId < 1 {
		return nil, status.Error(401, "passed bad user_id")
	}

	// получаем все соединения
	userConnectionList := tcp.GetAllUserConnectionsInfo(UserId)

	// если соединений для пользователя нет - кладем в ответ пустой массив
	if len(userConnectionList) < 1 {
		userConnectionList = []tcp.ConnectionInfoStruct{}
	}

	return userConnectionList, nil
}

// метод для закрытия подключений по идентификатору пользователя (userId)
func CloseConnectionsByUserId(UserId int64) error {

	// проверка user_id на корректность
	if UserId < 1 {
		return status.Error(401, "passed bad user_id")
	}

	// закрываем соединения
	tcp.CloseConnectionsByUserId(UserId)

	return nil
}

// метод для запроса онлайна ряда пользователей на ноды go_sender_*
func GetOnlineUsers(UserList []int64) ([]int64, []int64) {

	// проверяем кол-во идентификаторов в user_list
	if len(UserList) < 1 {
		return []int64{}, []int64{}
	}

	// отправляем запросы на go_sender ноды для получения онлайна
	onlineUserList, offlineUserList := tcp.GetOnlineOfflineUserList(UserList)
	if len(offlineUserList) < 1 {
		offlineUserList = []int64{}
	}
	if len(onlineUserList) < 1 {
		onlineUserList = []int64{}
	}

	return onlineUserList, offlineUserList
}

// получаем список соединений пользователя
func GetUserConnectionList(needWsEventUserList []structures.UserStruct) ([]structures.UserConnectionStruct, []int64) {

	// инициализируем массив соединений
	var allUserConnectionList []structures.UserConnectionStruct
	var needPushUserIdList []int64

	for _, item1 := range needWsEventUserList {

		// получаем для каждого юзера список всех его соединений из кеша
		var userConnectionList, _ = balancer.SenderCache.GetUserConnectionListByUserId(item1.UserId)

		if len(userConnectionList) > 0 {
			allUserConnectionList = append(allUserConnectionList, userConnectionList...)
		} else {
			needPushUserIdList = append(needPushUserIdList, item1.UserId)
		}
	}
	return allUserConnectionList, needPushUserIdList
}

// добавляем соединение пользователя
func AddConnectionToBalancer(nodeId int64, userId int64) {

	balancer.AddUserConnection(userId, nodeId)
}

// удаляем соединения пользователя или декрементим счетчик, если еще есть соединения
func DeleteConnectionFromBalancer(nodeId int64, userId int64, closedAllUserConnections bool) {

	if closedAllUserConnections {
		balancer.RemoveUserConnection(userId, nodeId)
		return
	}

	balancer.DecrementUserConnections(nodeId)
}

// удаляем все соединения ноды
func DeleteAllNodeConnectionsFromBalancer(nodeId int64) {

	balancer.ClearNode(nodeId)
}

// создана конференция Jitsi
// nosemgrep
func JitsiConferenceCreated(userId int64, event string, eventVersionList interface{}, pushData interface{}, wsUsers interface{}, uuid string, timeToLive int64, routineKey string, channel string) {

	go func() {

		addAndWaitRoutineKey(routineKey)
		defer doneRoutineKey(routineKey)

		// получаем все соединения пользователя
		userConnectionList, _ := balancer.SenderCache.GetUserConnectionListByUserId(userId)

		// отправляем ws-событие & voip-пуш при создании конференации Jitsi
		tcp.SendJitsiConferenceCreatedEvent(userId, userConnectionList, event, eventVersionList, pushData, wsUsers, uuid, timeToLive, routineKey, channel)
	}()
}

// отправляем voip-пуш Jitsi
// nosemgrep
func SendJitsiVoIPPush(userId int64, pushData interface{}, uuid string, routineKey string) {

	go func() {

		addAndWaitRoutineKey(routineKey)
		defer doneRoutineKey(routineKey)

		// получаем все соединения пользователя
		userConnectionList, _ := balancer.SenderCache.GetUserConnectionListByUserId(userId)

		// отправляем voip-пуш Jitsi
		tcp.SendJitsiVoIPPush(userId, userConnectionList, pushData, uuid, routineKey)
	}()
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// ждем рутину
func addAndWaitRoutineKey(routineKey string) {

	if routineKey == "" {
		return
	}

	// инициируем wait group
	wg := sync.WaitGroup{}
	wg.Add(1)

	// если такой wg есть он отдаст то он отдаст нам существующий
	wgTemp, exist := routineKeyStore.LoadOrStore(routineKey, &wg)
	if exist {

		// ждем пока существующий wg выполнится и пробуем запускаем функцию снова
		wgTemp.(*sync.WaitGroup).Wait()
		addAndWaitRoutineKey(routineKey)
	}
}

// ждем рутину
func waitRoutineKey(routineKey string) {

	if routineKey == "" {
		return
	}

	// инициируем wait group
	wg := sync.WaitGroup{}
	wg.Add(1)

	// если такой wg есть он отдаст то он отдаст нам существующий
	wgTemp, exist := routineKeyStore.LoadOrStore(routineKey, &wg)
	if exist {

		// ждем пока существующий wg выполнится и пробуем запускаем функцию снова
		wgTemp.(*sync.WaitGroup).Wait()
		waitRoutineKey(routineKey)
	}
}

// говорим что задача выполнена
func doneRoutineKey(routineKey string) {

	if routineKey == "" {
		return
	}

	wg, exist := routineKeyStore.Load(routineKey)
	if !exist {

		log.Error("[ANOMALY] key not found in store")
		return
	}

	// удаляем из хранилища
	routineKeyStore.Delete(routineKey)

	// выполняем
	wg.(*sync.WaitGroup).Done()
}
