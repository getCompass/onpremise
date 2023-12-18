package controller

import (
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/tests/tester"
	"go_sender_balancer/api/conf"
	"go_sender_balancer/api/includes/type/balancer"
	"go_sender_balancer/api/includes/type/structures"
	"net"
	"os"
	"testing"
	"time"
)

func TestMain(m *testing.M) {

	flags.Parse()
	balancer.UpdateConfig()
	os.Exit(m.Run())
}

// -------------------------------------------------------
// setToken
// -------------------------------------------------------

// проверяем удачный вариант исполнения метода
func TestOkTalkingSetToken(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.setToken успешно выполняется")

	// вызываем метод
	requestMap := map[string]interface{}{
		"user_id": 1,
		"token":   "talking;",
		"expire":  functions.GetCurrentTimeStamp() + 60,
	}
	requestBytes, _ := json.Marshal(requestMap)
	response := talkingController{}.SetToken(requestBytes)

	// проверяем успешное выполнение
	I.AssertEqual("ok", response.Status)
	I.CheckJsonStruct(response, &struct {
		Status   string `json:"status"`
		Response struct {
			Node int64 `json:"node"`
		} `json:"response"`
	}{})
}

// проверяем, что метод вернет ошибку, если передать некорректный user_id
func TestErrorTalkingSetTokenIfBadUserId(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.setToken вернет ошибку если передать некорректный user_id")

	requestMap := map[string]interface{}{
		"user_id": 0,
		"token":   "talking;",
		"expire":  functions.GetCurrentTimeStamp() + 60,
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.SetToken(byteRequest)

	// проверяем, что вернулась ошибка
	I.AssertError(response.Response, 401)
}

// проверяем, что метод вернет ошибку, если передать некорректный токен
func TestErrorTalkingSetTokenIfBadToken(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.setToken вернет ошибку если передать некорректный токен")

	requestMap := map[string]interface{}{
		"user_id": 1,
		"token":   "",
		"expire":  functions.GetCurrentTimeStamp() + 60,
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.SetToken(byteRequest)

	// проверяем, что вернулась ошибка
	I.AssertError(response.Response, 407)
}

// проверяем, что метод вернет ошибку, если передать невалидный запрос
func TestErrorTalkingSetTokenIfBadJson(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.setToken вернет ошибку при получении неверного запроса")

	// формируем невалидный запрос
	requestMap := map[string]interface{}{
		"user_id": 1,
		"token":   1,
		"expire":  functions.GetCurrentTimeStamp() + 60,
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.SetToken(byteRequest)

	// проверяем, что вернулась ошибка
	I.AssertError(response.Response, 105)
}

// -------------------------------------------------------
// sendEvent
// -------------------------------------------------------

// проверяем удачный вариант исполнения метода talking.sendEvent
func TestOkTalkingSendEvent(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.sendEvent успешно выполняется")

	// мокаем ноду сендера и начинаем ее слушать
	var userId int64 = 1
	var senderId int64 = 14
	senderPort := _getPortForTalkingMethods()
	err := MockGoSenderNode(senderId, senderPort, 0, userId, true)
	if err != nil {
		I.Fail("port not opened")
	}

	// вызываем метод
	requestMap := _formatRequestForSendSendEvent()
	requestBytes, _ := json.Marshal(requestMap)
	response := talkingController{}.SendEvent(requestBytes)

	// проверяем успешное выполнение
	I.AssertEqual("ok", response.Status)
	WaitForMethod(&I, "sender.sendEvent")

	// возвращаем все на место
	_ = conf.UpdateShardingConfig()
	DoListenDefaultPort()
}

// формируем запрос для talking.sendEvent
func _formatRequestForSendSendEvent() interface{} {

	return struct {
		UserList         []structures.UserStruct `json:"user_list"`
		Event            string                  `json:"event"`
		EventVersionList interface{}             `json:"event_version_List"`
		PushData         interface{}             `json:"push_data,omitempty"`
		WSUsers          interface{}             `json:"ws_users,omitempty"`
		Uuid             string                  `json:"uuid"`
	}{
		UserList: []structures.UserStruct{
			{
				UserId: 1,
			},
			{
				UserId: 3,
			},
		},
		Event:            "event",
		EventVersionList: struct{}{},
		Uuid:             functions.GenerateUuid(),
	}
}

// проверяем, что метод вернет ошибку, если передать невалидный запрос
func TestErrorTalkingSendEventIfBadJson(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.sendEvent вернет ошибку при получении неверного запроса")

	// формируем невалидный запрос
	requestMap := map[string]interface{}{
		"event":      1,
		"event_data": map[string]interface{}{},
		"uuid":       2,
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.SendEvent(byteRequest)

	// проверяем, что вернулась ошибка
	I.AssertError(response.Response, 105)
}

// -------------------------------------------------------
// sendEventBatching
// -------------------------------------------------------

// проверяем удачный вариант исполнения метода talking.sendEvent batching-методом
func TestOkTalkingSendEventBatching(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.sendEventBatching успешно выполняется")

	// мокаем ноду сендера и начинаем ее слушать
	var userId int64 = 1
	var senderId int64 = 14
	senderPort := _getPortForTalkingMethods()
	err := MockGoSenderNode(senderId, senderPort, 0, userId, true)
	if err != nil {
		I.Fail("port not opened")
	}

	// вызываем метод
	requestMap := _formatRequestForSendSendEventBatching()
	requestBytes, _ := json.Marshal(requestMap)
	response := talkingController{}.SendEventBatching(requestBytes)

	// проверяем успешное выполнение
	I.AssertEqual("ok", response.Status)
	WaitForMethod(&I, "sender.sendEvent")

	// возвращаем все на место
	_ = conf.UpdateShardingConfig()
	DoListenDefaultPort()
}

// формируем запрос для talking.sendEventBatching
func _formatRequestForSendSendEventBatching() structures.SendEventBatchingRequestStruct {

	sendEventRequestStruct := structures.SendEventRequestStruct{
		UserList: []structures.UserStruct{
			{
				UserId: 1,
			},
			{
				UserId: 3,
			},
		},
		Event:            "event",
		EventVersionList: struct{}{},
		PushData:         "data",
		RoutineKey:       "key",
		Uuid:             functions.GenerateUuid(),
		IsNeedPush:       1,
	}

	return structures.SendEventBatchingRequestStruct{
		BatchingData: []structures.SendEventRequestStruct{sendEventRequestStruct},
	}
}

// проверяем, что метод вернет ошибку, если передать невалидный запрос
func TestErrorTalkingSendEventBatchingIfBadJson(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.sendEventBatching вернет ошибку при получении неверного запроса")

	// формируем невалидный запрос
	requestMap := map[string]interface{}{
		"batching_data": 1,
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.SendEventBatching(byteRequest)

	// проверяем, что вернулась ошибка
	I.AssertError(response.Response, 105)
}

// -------------------------------------------------------
// getOnlineUsers
// -------------------------------------------------------

// проверяем, что метод talking.getOnlineUsers успешно отправит запросы на go_sender ноды для получения онлайна
func TestOkTalkingGetOnlineUsers(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод getOnlineUsers отправит запросы на go_sender ноды для получения онлайна")

	var userId int64 = 26
	var senderId int64 = 30
	senderPort := _getPortForTalkingMethods()

	// поднимаем ноду go_sender
	err := MockGoSenderNode(senderId, senderPort, 0, userId, true)
	if err != nil {
		I.Fail("port not opened")
	}

	// авторизуем соединению юзеру
	SetToken(userId)
	err = AddUserInStart(userId, 1)
	if err != nil {

		I.Fail("unable create mock connection")
		return
	}

	// асинхронно потому что метод ждет ответа от сервера когда шлет запросы
	go func() {
		_getOnlineUserRequest(I, userId)
	}()

	// убеждаемся что получили нужный запрос от sender и возвращаем все на место
	WaitForMethod(&I, "sender.getonlineusers")
	_ = conf.UpdateShardingConfig()
	DoListenDefaultPort()
}

// вызываем метод для получения онлайн юзеров
func _getOnlineUserRequest(I tester.IStruct, userId int64) {

	// структура запроса
	requestMap := map[string]interface{}{
		"user_list": []int64{
			userId,
		},
	}
	requestBytes, _ := json.Marshal(requestMap)

	response := talkingController{}.GetOnlineUsers(requestBytes)

	// проверяем успешное выполнение
	I.AssertEqual("ok", response.Status)
	I.CheckJsonStruct(response.Response, &struct {
		OnlineUserList  []int64 `json:"online_user_list"`
		OfflineUserList []int64 `json:"offline_user_list"`
	}{[]int64{}, []int64{userId}})
}

// проверяем, что метод упешно выполнится, если передать пустой userList
func TestOkTalkingGetOnlineUsersIfEmptyUserList(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод getOnlineUsers что метод упешно выполнится, если передать пустой userList")

	// выполняем запрос
	requestMap := map[string]interface{}{
		"user_list": []int64{},
		"method":    "talking.getOnlineUsers",
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.GetOnlineUsers(byteRequest)

	// проверяем
	I.AssertEqual("ok", response.Status)
}

// проверяем, что метод вернет ошибку, если передать неправильный json
func TestErrorTalkingGetOnlineUsersIfBadJson(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод getOnlineUsers вернет ошибку, если передать некорректный json")

	// выполняем запрос
	requestMap := map[string]interface{}{
		"user_list": 13,
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.GetOnlineUsers(byteRequest)

	// проверяем, что вернулась ошибка
	I.AssertError(response.Response, 105)
}

// -------------------------------------------------------
// getOnlineConnectionsByUserId
// -------------------------------------------------------

// проверяем,что метод упешно выполнится, если передать пользователя с соединением
func TestOkTalkingGetOnlineConnectionsByUserId(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.getOnlineConnectionsByUserId вернет соединение пользователя")

	var userId int64 = 31
	var senderId int64 = 17
	senderPort := _getPortForTalkingMethods()

	// мокаем ноду сендера и начинаем ее слушать
	err := MockGoSenderNode(senderId, senderPort, 0, userId, true)
	if err != nil {
		I.Fail("port not opened")
	}

	// авторизуем соединение юзеру
	SetToken(userId)
	err = AddUserInStart(userId, 1)
	if err != nil {

		I.Fail("unable create connection")
		return
	}

	// асинхронно потому что метод ждет ответа от сервера когда шлет запросы
	go func() {
		_sendTalkingGetOnlineConnectionsByUserId(&I, userId, senderId)
	}()

	// проверяем, что все ок
	WaitForMethod(&I, "sender.getonlineconnectionsbyuserid")

	// возвращаем все на место
	_ = conf.UpdateShardingConfig()
	DoListenDefaultPort()
	tcpResponse = ""
}

// отправляем запрос на получения онлайна по userId
func _sendTalkingGetOnlineConnectionsByUserId(I *tester.IStruct, userId int64, senderId int64) {

	// хардкодим респонс который будет отдавать наша нода
	onlineConnectionList := fmt.Sprintf("\"online_connection_list\":["+
		"{\"sender_node_id\":%d,"+
		"\"connection_id\":1,"+
		"\"user_id\":%d,"+
		"\"ip_address\":\"192.168.0.1\","+
		"\"connected_at\":1,"+
		"\"user_agent\":\"32\","+
		"\"platform\":\"android\","+
		"\"is_focused\":1}]", senderId, userId)
	tcpResponse = onlineConnectionList

	requestMap := map[string]interface{}{
		"user_id": userId,
	}
	requestBytes, _ := json.Marshal(requestMap)

	response := talkingController{}.GetOnlineConnectionsByUserId(requestBytes)
	responseBytes, _ := json.Marshal(response)

	// проверяем, что метод выполнился успешно
	I.AssertEqual([]byte(fmt.Sprintf("{\"status\":\"ok\",\"response\":{%s}}", onlineConnectionList)), responseBytes)
}

// проверяем, что метод упешно выполнится, если соединение не было открыто
func TestOkTalkingGetOnlineConnectionsByUserIdIfConnectionClose(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.getOnlineConnectionsByUserId упешно выполнится, если соединение не было открыто")

	var userId int64 = 100

	// формируем запрос
	requestMap := map[string]interface{}{
		"user_id": userId,
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.GetOnlineConnectionsByUserId(byteRequest)
	responseBytes, _ := json.Marshal(response)

	I.AssertEqual("{\"status\":\"ok\",\"response\":{\"online_connection_list\":[]}}", string(responseBytes))
}

// проверяем, что метод вернет ошибку, если передать некорректный user_id
func TestErrorTalkingGetOnlineConnectionsByUserIdIfBadUserId(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.getOnlineConnectionsByUserId вернет ошибку если передать некорректный user_id")

	requestMap := map[string]interface{}{
		"user_id": 0,
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.GetOnlineConnectionsByUserId(byteRequest)

	// проверяем, что вернулась ошибка
	I.AssertError(response.Response, 401)
}

// проверяем, что метод вернет ошибку, если передать неверный json
func TestErrorTalkingGetOnlineConnectionsByUserIdIfBadJson(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.getOnlineConnectionsByUserId вернет ошибку если передать неверный json")

	// формируем запрос
	requestMap := map[string]interface{}{
		"user_id": ":sob:",
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.GetOnlineConnectionsByUserId(byteRequest)

	// проверяем, что вернулась ошибка
	I.AssertError(response.Response, 105)
}

// -------------------------------------------------------
// closeConnectionsByUserId
// -------------------------------------------------------

// проверяем, что метод успешно закроет все соединения пользователя
func TestOkTalkingCloseConnectionsByUserId(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.closeConnectionsByUserId успешно закроет все соединения пользователя")

	var userId int64 = 31
	var senderId int64 = 17
	senderPort := _getPortForTalkingMethods()

	// добавляем пользователя к тесту
	SetToken(userId)
	err := AddUserInStart(userId, 1)
	if err != nil {

		I.Fail("unable create connection")
		return
	}

	// мокаем ноду сендера и начинаем ее слушать
	err = MockGoSenderNode(senderId, senderPort, 0, userId, true)
	if err != nil {
		I.Fail("port not opened")
	}

	// асинхронно потому что метод ждет ответа от сервера когда шлет запросы
	go func() {
		_sendCloseConnectionsByUserId(I, userId)
	}()

	// проверяем, что все ок и возвращаем все на место
	WaitForMethod(&I, "sender.closeconnectionsbyuserid")
	_ = conf.UpdateShardingConfig()
	DoListenDefaultPort()
	tcpResponse = ""
}

// проверяем, что метод вернет ошибку, если передать неверный user_id
func TestErrorTalkingCloseConnectionsByUserIdIfIncorrectUserId(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.closeConnectionsByUserId вернет ошибку, если передать неокорректный user_id")

	// формируем запрос
	requestMap := map[string]interface{}{
		"user_id": 0,
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.CloseConnectionsByUserId(byteRequest)

	I.AssertEqual("error", response.Status)
}

// проверяем, что метод вернет ошибку, если передать неверный json
func TestErrorTalkingCloseConnectionsByUserIdIfBadJson(testItem *testing.T) {

	I := tester.StartTest(testItem)
	I.WantToTest("метод talking.closeConnectionsByUserId вернет ошибку, если передать неверный json")

	// формируем запрос
	requestMap := map[string]interface{}{
		"user_id": "",
	}
	byteRequest, _ := json.Marshal(requestMap)

	// вызываем метод
	response := talkingController{}.CloseConnectionsByUserId(byteRequest)

	I.AssertEqual("error", response.Status)
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// последний выданный порт
var lastTalkingPort int64 = 19100

// функция для получения не занятого порта
func _getPortForTalkingMethods() int64 {

	lastTalkingPort++
	conn, err := net.Listen("tcp", fmt.Sprintf("0.0.0.0:%d", lastTalkingPort))
	if err != nil || conn == nil {

		log.Errorf("Не смогли забиндить порт %d %v %v", lastTalkingPort, err, conn)
		time.Sleep(time.Second)
		return _getPortForTalkingMethods()
	}
	_ = conn.Close()
	return lastTalkingPort
}

// отправляем запрос на закрытие соединений
func _sendCloseConnectionsByUserId(I tester.IStruct, userId int64) {

	// формируем запрос
	requestMap := map[string]interface{}{
		"user_id": userId,
	}
	requestBytes, _ := json.Marshal(requestMap)

	// отправляем запрос
	response := talkingController{}.CloseConnectionsByUserId(requestBytes)

	// проверяем, что метод успешно выполнился
	I.AssertEqual(response.Status, "ok")
}
