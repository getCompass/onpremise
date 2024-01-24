package tcp

import (
	"bufio"
	"bytes"
	"encoding/json"
	"fmt"
	"go_sender_balancer/api/conf"
	"go_sender_balancer/api/includes/type/balancer"
	"net"
)

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// метод для отправки запроса на ноду go_sender
func doCallSender(nodeId int64, request interface{}, response interface{}) error {

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

// метод для отправки запроса в go_pusher
func doCallPusher(request interface{}, response interface{}) error {

	// получаем конфигурацию ноды
	config, isExist := conf.GetShardingConfig().Go["pusher"]
	if !isExist {
		return fmt.Errorf("не найдена конфигурация для go_pusher")
	}

	// отправляем запрос на ноду
	err := doTcpRequest(config.Host, config.Port, request, &response)
	if err != nil {
		return fmt.Errorf("не смог отправить запрос на go_pusher Error: %v", err)
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
	defer conn.Close()

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
