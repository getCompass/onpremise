package sender

import (
	"bufio"
	"bytes"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/conf"
	"go_company/api/includes/type/structures"
	"net"
)

// отправляем ивент о добавлении реакции в диалог
func SendActionConversationMessageReactionAdded(companyId int64, wsUserList interface{}, wsEventVersionList []structures.WsEventVersionItemStruct, reactionCount int, reactionIndex int, reactionsUpdatedVersion int, senderConfig conf.GoShardingStruct) {

	requestMap := makeConversationReactionActionRequestMap(wsUserList, wsEventVersionList, reactionCount, reactionIndex, reactionsUpdatedVersion)
	requestMap["event"] = "action.conversation_message_reaction_added"

	send(companyId, requestMap, senderConfig)
}

// отправляем ивент о добавлении реакции в тред
func SendActionThreadMessageReactionAdded(companyId int64, wsUserList interface{}, wsEventVersionList []structures.WsEventVersionItemStruct, reactionCount int, reactionIndex int, senderConfig conf.GoShardingStruct) {

	requestMap := makeThreadReactionActionRequestMap(wsUserList, wsEventVersionList, reactionCount, reactionIndex)
	requestMap["event"] = "action.thread_message_reaction_added"

	send(companyId, requestMap, senderConfig)
}

// отправляем ивент о удалении реакции из диалог
func SendActionConversationMessageReactionRemoved(companyId int64, wsUserList interface{}, wsEventVersionList []structures.WsEventVersionItemStruct, reactionCount int, reactionIndex int, reactionsUpdatedVersion int, senderConfig conf.GoShardingStruct) {

	requestMap := makeConversationReactionActionRequestMap(wsUserList, wsEventVersionList, reactionCount, reactionIndex, reactionsUpdatedVersion)
	requestMap["event"] = "action.conversation_message_reaction_removed"

	send(companyId, requestMap, senderConfig)
}

// отправляем ивент о добавлении реакции из треда
func SendActionThreadMessageReactionRemoved(companyId int64, wsUserList interface{}, wsEventVersionList []structures.WsEventVersionItemStruct, reactionCount int, reactionIndex int, senderConfig conf.GoShardingStruct) {

	requestMap := makeThreadReactionActionRequestMap(wsUserList, wsEventVersionList, reactionCount, reactionIndex)
	requestMap["event"] = "action.thread_message_reaction_removed"

	send(companyId, requestMap, senderConfig)
}

type eventVersionItemStruct struct {
	Version int         `json:"version"`
	Data    interface{} `json:"data"`
}

// формируем базовый requestMap
// @long
func makeConversationReactionActionRequestMap(wsUserList interface{}, wsEventVersionList []structures.WsEventVersionItemStruct, reactionCount int, reactionIndex int, reactionsUpdatedVersion int) map[string]interface{} {

	// пробегаемся по каждой версии события
	var modifiedEventVersionList = []eventVersionItemStruct{}
	for _, eventVersionItem := range wsEventVersionList {

		var parsedEventData interface{}
		err := json.Unmarshal(eventVersionItem.Data, &parsedEventData)
		if err != nil {

			log.Errorf("json unmarshal one of event version item is failed, err: %+v", err)
			continue
		}

		// добавляем в ws актуальное количество реакции
		parsedEventData.(map[string]interface{})["reaction_count"] = reactionCount
		parsedEventData.(map[string]interface{})["reaction_index"] = reactionIndex
		parsedEventData.(map[string]interface{})["reactions_updated_version"] = reactionsUpdatedVersion

		// запаковываем в структуру для отправки в sender
		modifiedEventVersionItem := eventVersionItemStruct{
			Version: eventVersionItem.Version,
			Data:    parsedEventData,
		}
		modifiedEventVersionList = append(modifiedEventVersionList, modifiedEventVersionItem)
	}

	requestMap := map[string]interface{}{
		"user_list":          wsUserList,
		"ws_users":           json.RawMessage("{}"),
		"push_data":          json.RawMessage("{}"),
		"uuid":               functions.GenerateUuid(),
		"event_version_list": modifiedEventVersionList,
	}

	return requestMap
}

// формируем базовый requestMap
func makeThreadReactionActionRequestMap(wsUserList interface{}, wsEventVersionList []structures.WsEventVersionItemStruct, reactionCount int, reactionIndex int) map[string]interface{} {

	// пробегаемся по каждой версии события
	var modifiedEventVersionList = []eventVersionItemStruct{}
	for _, eventVersionItem := range wsEventVersionList {

		var parsedEventData interface{}
		err := json.Unmarshal(eventVersionItem.Data, &parsedEventData)
		if err != nil {

			log.Errorf("json unmarshal one of event version item is failed, err: %+v", err)
			continue
		}

		// добавляем в ws актуальное количество реакции
		parsedEventData.(map[string]interface{})["reaction_count"] = reactionCount
		parsedEventData.(map[string]interface{})["reaction_index"] = reactionIndex

		// запаковываем в структуру для отправки в sender
		modifiedEventVersionItem := eventVersionItemStruct{
			Version: eventVersionItem.Version,
			Data:    parsedEventData,
		}
		modifiedEventVersionList = append(modifiedEventVersionList, modifiedEventVersionItem)
	}

	requestMap := map[string]interface{}{
		"user_list":          wsUserList,
		"ws_users":           json.RawMessage("{}"),
		"push_data":          json.RawMessage("{}"),
		"uuid":               functions.GenerateUuid(),
		"event_version_list": modifiedEventVersionList,
	}

	return requestMap
}

// функция для отправки события в talking
func send(companyId int64, requestMap map[string]interface{}, config conf.GoShardingStruct) {

	requestMap["method"] = "sender.sendEvent"
	requestMap["company_id"] = companyId
	_, err := call(requestMap, config)

	if err != nil {
		log.Errorf("failed to send tcp request, error: %v", err)
	}
}

// отправить запрос в go_sender для отправки события
func call(requestMap map[string]interface{}, config conf.GoShardingStruct) (result bool, err error) {

	tcpAddr, err := net.ResolveTCPAddr("tcp", fmt.Sprintf("%s:%s", config.Host, config.Port))
	if err != nil {
		return false, fmt.Errorf("failed to establish connection with go_sender")
	}

	request, err := go_base_frame.Json.Marshal(requestMap)
	if err != nil {
		return false, err
	}

	// форматируем запрос под memcache протокол
	request = []byte(fmt.Sprintf("get %s\r\n", string(request)))

	// устанавливаем соединение

	log.Infof("%s", request)
	conn, err := net.DialTCP("tcp", nil, tcpAddr)
	if err != nil {
		return false, err
	}

	// получаем ответ
	_, err = doPrepareResponse(conn, request)
	if err != nil {
		return false, err
	}

	err = conn.Close()
	if err != nil {
		return false, err
	}

	return true, nil
}

// получаем ответ
func doPrepareResponse(conn *net.TCPConn, request []byte) ([]byte, error) {

	// инициаилизируем объект для чтения
	reader := bufio.NewReader(conn)

	// отправляем request
	_, err := conn.Write(request)
	if err != nil {
		return nil, err
	}

	// получаем response
	response, err := getResponse(reader)
	if err != nil {
		_ = conn.Close()
		return nil, err
	}
	return response, nil
}

// метод для разбора ответа по tcp
func getResponse(r *bufio.Reader) ([]byte, error) {

	var response = make([]byte, 8192)

	_, err := r.Read(response)
	if err != nil {
		return nil, err
	}

	responseSlice := bytes.Split(response, []byte("\r\n"))

	if len(responseSlice) < 2 {
		return nil, fmt.Errorf("incorrect request from microservice")
	}

	return responseSlice[1], nil
}
