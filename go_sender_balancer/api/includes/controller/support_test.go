package controller

import (
	"encoding/json"
	"fmt"
	"github.com/DATA-DOG/go-sqlmock"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"github.com/getCompassUtils/go_base_frame/api/system/tcp"
	"github.com/getCompassUtils/go_base_frame/tests/tester"
	"go_sender_balancer/api/conf"
	"go_sender_balancer/api/includes/type/balancer"
	"go_sender_balancer/api/system/sharding"
	"net"
	"sync"
	"time"
)

// -------------------------------------------------------
// вспомогательный файл для тестов
// -------------------------------------------------------

// используемые переменные
var (
	// замоканный кэш
	requestChan *sync.Map

	// ответ от tcp
	tcpResponse = ""

	// таймаут для tcp соединений
	tcpTimeout int64 = 5
)

// функция для ожидания нужного эвента на замоканный порт
func WaitForMethod(I *tester.IStruct, expectedMethod string) {

	expireAt := time.Now().Unix() + tcpTimeout

	for {

		var ok = false
		requestChan.Range(func(_, value interface{}) bool {

			// если не совпали — ждем следующий
			method := _getMethodFromRequest(I, value.([]byte))
			if method != expectedMethod {

				log.Errorf("пришел запрос: %v, ожидали: %s", value.([]byte), method)
				return true
			}
			ok = true
			return false
		})

		if ok {
			break
		}
		if time.Now().Unix() > expireAt {
			I.Fail("timeout for expected request %v", expectedMethod)
		}
		time.Sleep(time.Millisecond * 30)
	}
}

// функция достает ожидаемый method из запроса
func _getMethodFromRequest(I *tester.IStruct, expectedRequest []byte) string {

	type requestUuid struct {
		Method string `json:"method"`
	}

	request := requestUuid{}
	err := json.Unmarshal(expectedRequest, &request)
	if err != nil {
		I.Fail("convert to JSON error. Error: %v", err)
	}

	return request.Method
}

// функция для мока go_sender ноды
func MockGoSenderNode(senderId int64, senderPort int64, senderLimit int64, userId int64, isNeedAddUserConnection bool) error {

	// мокаем ноду сендера
	conf.AddSenderNode(senderId, senderPort, senderLimit)
	balancer.UpdateConfig()

	if isNeedAddUserConnection {
		balancer.AddUserConnection(userId, senderId)
	}
	host := "0.0.0.0"

	// начинаем ее слушать
	requestChan = &sync.Map{}
	go func() {
		tcp.Listen(host, senderPort, _listenAndFullRequestChan)
	}()

	return checkPort(host, senderPort)
}

// проверяем открыт ли порт
func checkPort(host string, port int64) error {

	// время когда нужно прекратить чекать порт
	expireAt := time.Now().Unix() + tcpTimeout
	for {

		conn, err := net.DialTimeout("tcp", fmt.Sprintf("%s:%d", host, port), time.Millisecond*30)
		if err == nil && conn != nil {
			_ = conn.Close()
			break
		}
		if time.Now().Unix() > expireAt {
			return fmt.Errorf("port not openned")
		}

		// чекаем его каждые 50 ms
		time.Sleep(time.Millisecond * 50)
	}

	return nil
}

// функция для отката на стандартный порт
func DoListenDefaultPort() {

	balancer.UpdateConfig()
}

// функция для установки токена
func SetToken(userId int64) {

	// выполняем запрос на установку токена
	requestMap := map[string]interface{}{
		"user_id": userId,
		"token":   "talking;",
		"expire":  functions.GetCurrentTimeStamp() + 60,
	}
	requestBytes, _ := json.Marshal(requestMap)
	talkingController{}.SetToken(requestBytes)
}

// функция для добавления пользователя в базу start
func AddUserInStart(userId int64, userDpc int64) error {

	// получаем текущее время
	currentTime := functions.GetCurrentTimeStamp()

	// подменяем соединение с базой на заглушку
	connection, mock, err := sqlmock.New()
	if err != nil {

		return err
	}
	sharding.Mysql("start")
	mysql.ReplaceConnection("start", connection)

	// подставляем значение заглушки
	rows := sqlmock.NewRows([]string{"user_id", "work_dpc", "need_work", "created_at"}).AddRow(userId,
		userDpc, currentTime, currentTime)
	mock.ExpectQuery(fmt.Sprintf("^SELECT (.+) FROM `%s`", "start_user_in_dpc")).WillReturnRows(rows)

	return nil
}

// функция слушает rabbit/tcp и наполняет кэш входящими запросами
func _listenAndFullRequestChan(request []byte) []byte {

	go func() {
		requestChan.Store(functions.GenerateUuid(), request)
	}()

	return []byte(fmt.Sprintf("{\"status\":\"ok\",\"response\":{%s}}", tcpResponse))
}
