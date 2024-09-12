package sender

import (
	"bufio"
	"bytes"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender_balancer/api/includes/type/balancer"
	"go_sender_balancer/api/includes/type/structures"
	"net"
	"strings"
)

// структура ответа со списком соедиениний пользователя
type _doCallGetOnlineConnectionsByUserIdResponseStruct struct {
	Status   string `json:"status"`
	Response struct {
		OnlineConnectionList []ConnectionInfoStruct `json:"online_connection_list"`
	} `json:"response"`
}

// метод для отправки запроса на получения соединений пользователя на go_sender ноды
func doCallSenderGetOnlineConnectionsByUserId(nodeId int64, userId int64) []ConnectionInfoStruct {

	request := struct {
		Method string `json:"method"`
		UserId int64  `json:"user_id"`
	}{
		Method: "sender.getOnlineConnectionsByUserId",
		UserId: userId,
	}
	request.Method = strings.ToLower(request.Method)

	senderResponse := _doCallGetOnlineConnectionsByUserIdResponseStruct{}
	err := doCall(nodeId, request, &senderResponse)
	if err != nil {

		log.Errorf("%v", err)
		return []ConnectionInfoStruct{}
	}

	// добавляем senderNodeId в ответ
	for item := range senderResponse.Response.OnlineConnectionList {
		senderResponse.Response.OnlineConnectionList[item].SenderNodeId = nodeId
	}

	return senderResponse.Response.OnlineConnectionList
}

// отправляем запрос
func doCallCloseConnectionsByUserId(nodeId int64, userId int64) error {

	request := struct {
		Method string `json:"method"`
		UserId int64  `json:"user_id"`
	}{
		Method: "sender.closeConnectionsByUserId",
		UserId: userId,
	}
	request.Method = strings.ToLower(request.Method)

	err := doCall(nodeId, request, nil)

	return err
}

// получаем список онлайн юзеров
func doCallGetOnlineUsers(nodeId int64, userList []int64) ([]int64, []int64) {

	request := struct {
		Method   string  `json:"method"`
		UserList []int64 `json:"user_list"`
	}{
		Method:   "sender.getOnlineUsers",
		UserList: userList,
	}
	request.Method = strings.ToLower(request.Method)

	response := struct {
		Status   string `json:"status"`
		Response struct {
			OnlineUserList  []int64 `json:"online_user_list"`
			OfflineUserList []int64 `json:"offline_user_list"`
		} `json:"response"`
	}{}

	err := doCall(nodeId, request, &response)
	if err != nil {
		return []int64{}, []int64{}
	}

	return response.Response.OnlineUserList, response.Response.OfflineUserList
}

type doCallSetTokenResponseStruct struct {
	Status   string      `json:"status"`
	Response interface{} `json:"response"`
}

// метод для установки токена пользователю
func doCallSetToken(nodeId int64, token string, userId int64, expire int64, platform string, deviceId string) (response doCallSetTokenResponseStruct, err error) {

	request := struct {
		Method   string `json:"method"`
		Token    string `json:"token"`
		UserId   int64  `json:"user_id"`
		Expire   int64  `json:"expire"`
		Platform string `json:"platform"`
		DeviceId string `json:"device_id"`
	}{
		Method:   "sender.setToken",
		Token:    token,
		UserId:   userId,
		Expire:   expire,
		Platform: platform,
		DeviceId: deviceId,
	}
	request.Method = strings.ToLower(request.Method)

	err = doCall(nodeId, request, &response)

	return response, err
}

// структура ответа запроса на ноду для получения списка онлайн пользователей
type SendGetOnlineUserListRequestResponseStruct struct {
	Status   string `json:"status"`
	Response struct {
		UserList []UserOnlineDevicesStruct `json:"user_list"`
		HasNext  bool                      `json:"has_next"`
	} `json:"response"`
}

// field user_list in response sender.getOnlineUserList
type UserOnlineDevicesStruct struct {
	UserId   int64  `json:"user_id"`
	Platform string `json:"platform"`
	DeviceId string `json:"device_id"`
}

// метод для получения списка онлайн пользователей ноды
func doCallGetOnlineUserList(nodeId int64, uuid string, limit int, offset int) (response SendGetOnlineUserListRequestResponseStruct, err error) {

	request := struct {
		Method string `json:"method"`
		Uuid   string `json:"uuid"`
		Limit  int    `json:"limit"`
		Offset int    `json:"offset"`
	}{
		Method: "sender.getOnlineUserList",
		Uuid:   uuid,
		Limit:  limit,
		Offset: offset,
	}
	request.Method = strings.ToLower(request.Method)

	err = doCall(nodeId, request, &response)
	if err != nil {
		return SendGetOnlineUserListRequestResponseStruct{}, err
	}

	return response, nil
}

// структура запроса на отправку события
type publishRequestStruct struct {
	Method           string                  `json:"method"`
	UserList         []structures.UserStruct `json:"user_list"`
	Event            string                  `json:"event"`
	EventVersionList interface{}             `json:"event_version_list"`
	PushData         interface{}             `json:"push_data,omitempty"`
	WSUsers          interface{}             `json:"ws_users,omitempty"`
	Uuid             string                  `json:"uuid"`
	RoutineKey       string                  `json:"routine_key"`
}

// метод для отправки события на ноду go_sender
// @long много структур объявляется
func doCallPublish(nodeId int64, userList []int64, event string, eventVersionList interface{}, wsUsers interface{}, uuid string, routineKey string) []int64 {

	var userEventList []structures.UserStruct

	for _, userId := range userList {
		userEventList = append(userEventList, structures.UserStruct{UserId: userId})
	}

	request := publishRequestStruct{
		Method:           "sender.sendEvent",
		UserList:         userEventList,
		Event:            event,
		EventVersionList: eventVersionList,
		WSUsers:          wsUsers,
		Uuid:             uuid,
		RoutineKey:       routineKey,
	}

	response := struct {
		Status   string `json:"status"`
		Response struct {
			SentUserList []int64 `json:"sent_user_list"`
		} `json:"response"`
	}{}

	err := doCall(nodeId, request, &response)
	if err != nil {

		log.Errorf("%v", err)
		return []int64{}
	}

	return response.Response.SentUserList
}

// структура запроса на отправку события всем юзерам для go_sender
type broadcastRequestStruct struct {
	Method           string      `json:"method"`
	Event            string      `json:"event"`
	EventVersionList interface{} `json:"event_version_list"`
	PushData         interface{} `json:"push_data,omitempty"`
	WSUsers          interface{} `json:"ws_users,omitempty"`
	Uuid             string      `json:"uuid"`
	RoutineKey       string      `json:"routine_key"`
	Channel          string      `json:"channel"`
}

// выполняет рассылку всем подключенным пользователям
func Broadcast(nodeId int64, event string, EventVersionList interface{}, wsUsers interface{}, uuid string, routineKey string, channel string) {

	request := broadcastRequestStruct{
		Method:           "sender.sendEventToAll",
		Event:            event,
		EventVersionList: EventVersionList,
		WSUsers:          wsUsers,
		Uuid:             uuid,
		RoutineKey:       routineKey,
		Channel:          channel,
	}

	response := struct {
		Status   string      `json:"status"`
		Response interface{} `json:"response"`
	}{}

	_ = doCall(nodeId, request, &response)
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// метод для отправки запроса на ноду go_sender
func doCall(nodeId int64, request interface{}, response interface{}) error {

	// получаем конфигурацию ноды
	config, isExist := balancer.GetConfigNode(nodeId)
	if !isExist {
		return fmt.Errorf("не найдена конфигурация для go_sender node #%d", nodeId)
	}

	// отправляем запрос на ноду
	err := doTcpRequest(config.Host, config.Port, request, &response)
	if err != nil {
		return fmt.Errorf("не смог отправить запрос на go_sender node #%d Error: %v", nodeId, err)
	}

	return nil
}

// -------------------------------------------------------
// TCP
// -------------------------------------------------------

const connectionType = "tcp"

// метод отправляет request по tcp соединению
func doTcpRequest(host string, port string, request interface{}, response interface{}) (err error) {

	requestBytes, err := getFormattedRequest(request)
	if err != nil {
		return err
	}

	conn, err := getConnection(host, port)
	if err != nil {
		return err
	}

	defer func() {
		_ = conn.Close()
	}()

	_, err = conn.Write(requestBytes)
	if err != nil {
		return err
	}

	reader := bufio.NewReader(conn)
	err = getResponse(reader, response)
	if err != nil {
		return err
	}

	return nil
}

// получаем отформатированный запрос
func getFormattedRequest(request interface{}) ([]byte, error) {

	// форматируем под memcache text protocol
	requestBytes, err := json.Marshal(request)
	if err != nil {
		return []byte{}, err
	}

	return []byte(fmt.Sprintf("get %s\r\n", string(requestBytes))), nil

}

// функция для получения соединения
func getConnection(host string, port string) (*net.TCPConn, error) {

	tcpAddr, err := net.ResolveTCPAddr(connectionType, fmt.Sprintf("%s:%s", host, port))
	if err != nil {
		return nil, err
	}
	conn, err := net.DialTCP(connectionType, nil, tcpAddr)
	if err != nil {
		return nil, err
	}

	return conn, nil
}

// метод для разбора ответа по tcp
func getResponse(r *bufio.Reader, response interface{}) error {

	var responseBytes = make([]byte, 32768)

	_, err := r.Read(responseBytes)
	if err != nil {
		return fmt.Errorf("Не смогли прочитать response с запроса\r\nError: %v", err)
	}

	responseSlice := bytes.Split(responseBytes, []byte("\r\n"))
	if len(responseSlice) < 2 {
		return fmt.Errorf("Incorrect request from microservice ")
	}

	err = json.Unmarshal(responseSlice[1], &response)
	if err != nil {
		return err
	}

	return nil
}
