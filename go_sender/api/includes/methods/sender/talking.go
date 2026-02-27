package talking

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/balancer"
	"go_sender/api/includes/type/db/company_data"
	Isolation "go_sender/api/includes/type/isolation"
	"go_sender/api/includes/type/push"
	"go_sender/api/includes/type/sender"
	"go_sender/api/includes/type/structures"
	"go_sender/api/includes/type/thread"
	"go_sender/api/includes/type/ws"
	"google.golang.org/grpc/status"
	"sync"
	"time"
)

var (
	routineKeyStore = sync.Map{}
)

// метод для установки пользовательского токена на go_sender для авторизации подключения
// принимает параметры: user_id int64, token string, expire	int64
func SetToken(isolation *Isolation.Isolation, Token string, Platform string, DeviceId string, UserId int64, Expire int64) error {

	if UserId < 1 {
		return status.Error(401, "passed bad user_id")
	}

	if len(Token) < 1 {
		return status.Error(407, "passed incorrect token")
	}

	// добавляем токен
	isolation.TokenStore.AddToken(Token, UserId, Expire, Platform, DeviceId)

	if isolation.GetCompanyId() == 0 {

		// добавляем соединение пользователя в кэш
		balancer.AddUserConnection(UserId)
	}

	return nil
}

// метод для очистки кэша с настройками уведомления пользователя
func ClearUserNotificationCache(isolation *Isolation.Isolation, UserId int64) error {

	if UserId < 1 {
		return status.Error(401, "passed bad user_id")
	}

	isolation.UserNotificationStorage.DeleteUserNotificationFromCache(UserId)

	return nil
}

// метод для отправки WS событий на активные подключения пользователей
// в случае, если у пользователя нет активных подключений
// отправляет push уведомление, если это необходимо
// nosemgrep
func SendEvent(isolation *Isolation.Isolation, UserList []structures.SendEventUserStruct, Event string, eventListByVersion map[int]interface{}, PushData push.PushDataStruct, WSUsers interface{}, Uuid, routineKey string, channel string) {

	// запускаем в рутине чтобы выполнение шло асинхронно
	go func() {

		addAndWaitRoutineKey(routineKey)
		defer doneRoutineKey(routineKey)

		// заческаем начло времени выполнения метода
		start := time.Now()

		// разделям пользователей на тех кому отправим эвенты и тех кому отправим пуши
		needWsEventUserList, needPushUserList, needForcePushUserList := splitUserByNeedPush(UserList)
		sentConnectionList := make([]structures.UserConnectionStruct, 0)

		// отправляем запросы
		if len(needWsEventUserList) > 0 {
			sentConnectionList = sender.SendEvent(isolation, needWsEventUserList, Event, eventListByVersion, WSUsers, channel)
		}

		if PushData.TextPush != nil {

			// разделяем всех пользователей которым отошлем пуши
			pushUserList := preparePushUserLists(needPushUserList, sentConnectionList)
			userNotificationRowList, _ := isolation.UserNotificationStorage.GetList(isolation.Context, isolation.NotificationSubStorage,
				isolation.CompanyDataConn, pushUserList)
			forcePushUserList := preparePushUserLists(needForcePushUserList, sentConnectionList)
			userForceNotificationRowList, _ := isolation.UserNotificationStorage.GetList(isolation.Context, isolation.NotificationSubStorage,
				isolation.CompanyDataConn, forcePushUserList)

			// отправляем пуши
			isolation.PusherConn.SendPush(Uuid, PushData, userNotificationRowList, userForceNotificationRowList)
		}

		// логируем окончание выполнения метода и сохраняем аналитику
		end := time.Since(start)
		if end.Seconds() > 1 {
			log.Infof("Function executing time: %d ms\r\n", end.Milliseconds())
		}
	}()
}

// метод для отправки WS событий для всех пользователей на активные подключения
// в случае, если у пользователя нет активных подключений
// отправляет push уведомление, если это необходимо
func SendEventToAll(isolation *Isolation.Isolation, Event string, eventListByVersion map[int]interface{}, PushData push.PushDataStruct, WSUsers interface{}, Uuid string,
	routineKey string, IsNeedPush int, channel string, excludeUserIdList []int64) {

	var userIdList []int64
	var userList []structures.SendEventUserStruct

	userIdList = isolation.UserConnectionStore.GetAllUserIdList()

	// исключаем элементы слайса пользователей, если им не нужно отправлять ws
	userIdList = functions.SliceDiff(userIdList, excludeUserIdList)

	for _, userId := range userIdList {

		var userStruct structures.SendEventUserStruct
		userStruct.UserId = userId
		userStruct.NeedPush = IsNeedPush
		userList = append(userList, userStruct)
	}

	SendEvent(isolation, userList, Event, eventListByVersion, PushData, WSUsers, Uuid, routineKey, channel)
}

// метод для получения списка ws соединений по идентификатору пользователя (userId)
func GetOnlineConnectionsByUserId(isolation *Isolation.Isolation, UserId int64) ([]sender.ConnectionInfoStruct, error) {

	if UserId < 1 {
		return nil, status.Error(401, "passed bad user_id")
	}

	// получаем все соединения
	localUserConnectionList := sender.GetAllUserConnectionsInfo(isolation, UserId)

	// если соединений для пользователя нет - кладем в ответ пустой массив
	if len(localUserConnectionList) < 1 {
		localUserConnectionList = []sender.ConnectionInfoStruct{}
	}

	return localUserConnectionList, nil
}

// метод для закрытия подключений по идентификатору пользователя (userId)
func CloseConnectionsByUserId(isolation *Isolation.Isolation, UserId int64) error {

	// проверка user_id на корректность
	if UserId < 1 {
		return status.Error(401, "passed bad user_id")
	}

	// закрываем соединения
	sender.CloseConnectionsByUserId(isolation, UserId)

	return nil
}

// метод для закрытия подключений по идентификатору пользователя (userId) с ожиданием ивента
func CloseConnectionsByUserIdWithWait(isolation *Isolation.Isolation, userId int64, routineKey string) error {

	// проверка user_id на корректность
	if userId < 1 {
		return status.Error(401, "passed bad user_id")
	}

	waitRoutineKey(routineKey)

	// закрываем соединения
	sender.CloseConnectionsByUserId(isolation, userId)

	return nil
}

// метод для закрытия подключений по идентификатору устройства (deviceId) с ожиданием ивента
func CloseConnectionsByDeviceIdWithWait(isolation *Isolation.Isolation, userId int64, deviceId string, routineKey string) error {

	// проверка user_id на корректность
	if deviceId == "" {
		return status.Error(401, "passed bad device_id")
	}

	// проверка user_id на корректность
	if userId < 1 {
		return status.Error(401, "passed bad user_id")
	}

	waitRoutineKey(routineKey)

	// закрываем соединения
	sender.CloseConnectionsByDeviceId(isolation, userId, deviceId)

	return nil
}

// метод для добавления задачи на отправку пушей пользователям в RabbitMq очередь микросервиса go_pusher
func AddTaskPushNotification(isolation *Isolation.Isolation, userList []int64, PushData push.PushDataStruct, Uuid string) {

	// отправляем пуш
	userNotificationRowList, _ := isolation.UserNotificationStorage.GetList(isolation.Context, isolation.NotificationSubStorage, isolation.CompanyDataConn, userList)
	userForceNotificationRowList := make([]*company_data.NotificationRow, 0)
	isolation.PusherConn.SendPush(Uuid, PushData, userNotificationRowList, userForceNotificationRowList)
}

// метод для получений онлайна ряда пользователей
func GetOnlineUsers(isolation *Isolation.Isolation, UserList []int64) ([]int64, []int64) {

	// проверяем кол-во идентификаторов в user_list
	if len(UserList) < 1 {
		return []int64{}, []int64{}
	}

	// получаем список онлайна
	onlineUserList, offlineUserList := sender.GetOnlineOfflineUserList(isolation, UserList)
	if len(offlineUserList) < 1 {
		offlineUserList = []int64{}
	}
	if len(onlineUserList) < 1 {
		onlineUserList = []int64{}
	}

	return onlineUserList, offlineUserList
}

// метод для добавления пользователя к треду
// nosemgrep
func AddUsersToThread(isolation *Isolation.Isolation, ThreadKey string, UserList []int64, routineKey string, channel string) {

	// исполняем запрос асинхронно
	go func() {

		addAndWaitRoutineKey(routineKey)
		defer doneRoutineKey(routineKey)

		// добавляем
		sender.AddUsersToThread(isolation, isolation.ThreadUcStore, isolation.ThreadAStore, UserList, ThreadKey, channel)
	}()
}

// метод для отправки typing события на go_sender_*
// nosemgrep
func SendTypingEvent(isolation *Isolation.Isolation, UserList []int64, Event string, EventVersionList map[int]interface{}, routineKey string, channel string) {

	go func() {

		addAndWaitRoutineKey(routineKey)
		defer doneRoutineKey(routineKey)

		// начинаем отсчитывать время выполнения метода
		start := time.Now()

		// формируем массивы с пользователями
		onlineUserList, _ := splitUsersByOnline(UserList)

		// отправляем
		if len(onlineUserList) > 0 {
			sender.SendTypingEvent(isolation, onlineUserList, Event, EventVersionList, channel)
		}

		// логируем время выполнения метода
		end := time.Since(start)
		log.Infof("Function executing time: %d\r\n", end)
	}()
}

// метод для отправки thread_typing события
func SendThreadTypingEvent(userConnectionStore *ws.UserConnectionStore, threadUserConnectionStore *thread.UserConnectionStore, Event string, threadKey string, EventVersionList map[int]interface{}) {

	// отправляем запрос на каждую ноду go_sender
	go sender.SendThreadTypingEvent(userConnectionStore, threadUserConnectionStore, Event, threadKey, EventVersionList)
}

// запрос для отправки voip пуша со звонком
// nosemgrep
func SendVoIP(isolation *Isolation.Isolation, UserId int64, PushData interface{}, Uuid string, TimeToLive int64, routineKey string) {

	// исполняем запрос асинхронно
	go func() {

		addAndWaitRoutineKey(routineKey)
		defer doneRoutineKey(routineKey)

		userIdList := []int64{UserId}
		userNotificationRowList, _ := isolation.UserNotificationStorage.GetList(isolation.Context, isolation.NotificationSubStorage,
			isolation.CompanyDataConn, userIdList)

		if len(userNotificationRowList) < 1 {
			return
		}

		userNotificationRow := userNotificationRowList[0]

		isolation.PusherConn.SendVoIP(userNotificationRow, PushData, Uuid, TimeToLive, []string{})
	}()
}

// запрос для отправки ws-события и voip-пуша о входящем звонке
// nosemgrep
func SendIncomingCall(isolation *Isolation.Isolation, UserId int64, eventVersionList map[int]interface{}, PushData interface{}, WSUsers interface{}, Uuid string, TimeToLive int64, routineKey string, channel string) {

	go func() {

		addAndWaitRoutineKey(routineKey)
		defer doneRoutineKey(routineKey)

		// инициируем массив с device_id устройств, на которые получили ws-событие
		var sentDeviceList []string

		// отправляем ws-событие
		sentDeviceListLocally := sender.SendIncomingCall(isolation, UserId, eventVersionList, WSUsers, channel)
		sentDeviceList = append(sentDeviceList, sentDeviceListLocally...)

		userIdList := []int64{UserId}
		userNotificationRowList, _ := isolation.UserNotificationStorage.GetList(isolation.Context, isolation.NotificationSubStorage,
			isolation.CompanyDataConn, userIdList)

		if len(userNotificationRowList) < 1 {
			return
		}

		userNotificationRow := userNotificationRowList[0]

		// отправляем VoIP-пуш на все android-устройства и только на те ios-устройства, которые не получили WS-события
		isolation.PusherConn.SendVoIP(userNotificationRow, PushData, Uuid, TimeToLive, sentDeviceList)
	}()
}

// запрос для отправки ws-события и voip-пуша при создании конференции Jitsi
// nosemgrep
func SendJitsiConferenceCreated(isolation *Isolation.Isolation, userId int64, event string, eventVersionList map[int]interface{}, pushData interface{}, wsUsers interface{}, uuid string, timeToLive int64, routineKey string, channel string) {

	go func() {

		addAndWaitRoutineKey(routineKey)
		defer doneRoutineKey(routineKey)

		// инициируем массив с device_id устройств, на которые получили ws-событие
		var sentDeviceList []string

		// отправляем ws-событие
		sentDeviceListLocally := sender.SendJitsiConferenceCreated(isolation, userId, event, eventVersionList, wsUsers, channel)
		sentDeviceList = append(sentDeviceList, sentDeviceListLocally...)

		// отправляем VoIP-пуш на все android-устройства и только на те ios-устройства, которые не получили WS-события
		isolation.PusherConn.SendJitsiVoIP(userId, pushData, uuid, timeToLive, sentDeviceList)
	}()
}

// запрос для отправки voip-пуша Jitsi
// nosemgrep
func SendJitsiVoipPush(isolation *Isolation.Isolation, userId int64, pushData interface{}, uuid string, routineKey string) {

	go func() {

		addAndWaitRoutineKey(routineKey)
		defer doneRoutineKey(routineKey)

		// отправляем VoIP-пуш
		isolation.PusherConn.SendJitsiVoIP(userId, pushData, uuid, 0, []string{})
	}()
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// разделям пользователей на тех кому отправим эвенты и тех кому отправим пуши
func splitUserByNeedPush(userList []structures.SendEventUserStruct) ([]int64, []int64, []int64) {

	// инициализируем массивы: пользователей которым отошлем события, пользователей которым отошлем пуш, временный для оффлайн пользователей
	var needWsEventUserList []int64
	var needPushUserList []int64
	var needForcePushUserList []int64

	for _, item := range userList {

		// разделяем пользователей, в зависимости от того что им отошлем
		needWsEventUserList, needPushUserList, needForcePushUserList = splitUsers(item, needWsEventUserList, needPushUserList, needForcePushUserList)
	}
	return needWsEventUserList, needPushUserList, needForcePushUserList
}

// разделяем пользователей которым отправим пуши
func preparePushUserLists(needPushUserList []int64, sentConnectionList []structures.UserConnectionStruct) []int64 {

	// убираем из массива на отправку пушей пользователей которым успешно отправили события
	needPushUserList = deleteSameUserFromUserLists(needPushUserList, structures.ConvertUserConnectionListToUserStructList(sentConnectionList))

	// инициализируем массив пользователи которым отошлем пуши
	var pushUserList []int64

	for _, item := range needPushUserList {

		// добавим в массив на отправку
		pushUserList = append(pushUserList, item)
	}

	return pushUserList
}

// убираем одинаковых пользователей из массива
func deleteSameUserFromUserLists(userListFromDelete []int64, userListToDelete []int64) []int64 {

	// массив в который сложим оличающихся пользователей
	var responseUserList []int64

	// если нет юзеров, которых нужно удалять
	if len(userListToDelete) < 1 {
		return userListFromDelete
	}

	// пробегаемся по списку пользователей, которым нужно отправить пуш
	for _, item1 := range userListFromDelete {

		isExist, _ := functions.InArray(item1, userListToDelete)
		if !isExist {
			responseUserList = append(responseUserList, item1)
		}
	}

	return responseUserList
}

// заполняем три массива пользователей
func splitUsers(user structures.SendEventUserStruct, needWsEventUserList []int64, needPushUserList []int64, needForcePushUserList []int64) ([]int64, []int64, []int64) {

	// добавляем пользователя в массив пользователей на отправку пушей
	if user.NeedForcePush == 1 {
		needForcePushUserList = append(needForcePushUserList, user.UserId)
	} else if user.NeedPush == 1 {
		needPushUserList = append(needPushUserList, user.UserId)
	}

	needWsEventUserList = append(needWsEventUserList, user.UserId)

	return needWsEventUserList, needPushUserList, needForcePushUserList
}

// раскидываем из кеша пользователей на два массива, онлайн, оффлайн
func splitUsersByOnline(userList []int64) ([]int64, []int64) {

	onlineUserList := make([]int64, 0)
	offlineUserList := make([]int64, 0)

	// проходимся по массиву пользователей
	for _, item := range userList {

		// проверяем наличие информации об активных соединениях пользователя
		isExist := balancer.SenderCache.GetUserConnectionListByUserId(item)

		// если пользователь онлайн
		if isExist {
			onlineUserList = append(onlineUserList, item)
			continue
		}

		// если пользователь офлайн
		offlineUserList = append(offlineUserList, item)
	}

	return onlineUserList, offlineUserList
}

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

	// если такой wg есть он отдаст то он отдаст нам существующий
	wgTemp, exist := routineKeyStore.Load(routineKey)
	if !exist {
		return
	}

	// ждем пока существующий wg выполнится
	wgTemp.(*sync.WaitGroup).Wait()
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
