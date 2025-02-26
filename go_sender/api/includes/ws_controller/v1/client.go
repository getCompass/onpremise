package wsControllerV1

import (
	"crypto/sha1"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/conf"
	gatewayPhpPivot "go_sender/api/includes/gateway/php_pivot"
	"go_sender/api/includes/methods/sender"
	"go_sender/api/includes/type/activitycache"
	"go_sender/api/includes/type/event"
	Isolation "go_sender/api/includes/type/isolation"
	"go_sender/api/includes/type/push"
	"go_sender/api/includes/type/structures"
	"go_sender/api/includes/type/thread"
	"go_sender/api/includes/type/ws"
	"go_sender/api/includes/type/ws/event/method_config"
	"sort"
	"strconv"
	"strings"
	"time"
)

type client struct{}

// поддерживаемые методы
var clientMethods = methodMap{
	"connect":              client{}.Connect,
	"send_method_config":   client{}.SendMethodConfig,
	"typing":               client{}.Typing,
	"create_thread_typing": client{}.CreateThreadTyping,
	"ping":                 client{}.Ping,
	"focus":                client{}.Focus,
	"unfocus":              client{}.Unfocus,
	"ack":                  client{}.Ack,
	"verify_thread_opened": client{}.VerifyThreadOpened,
	"thread_close":         client{}.ThreadClose,
	"thread_typing":        client{}.ThreadTyping,
}

// request client.connect
type clientConnectRequest struct {
	WSData struct {
		Token            string `json:"token"`                        // токен
		UserID           int64  `json:"user_id"`                      // идентификатор пользователя
		Platform         string `json:"platform"`                     // платформа устройства
		MethodConfigHash string `json:"method_config_hash,omitempty"` // sha1 хэш-сумма от содержимого конфига поддерживаемых версий ws событий
		AppVersion       string `json:"app_version,omitempty"`        // версия приложения
		PivotSession     string `json:"pivot_session,omitempty"`      // идентификатор сессии
	} `json:"ws_data"`
}

// обработка подключения
func (client) Connect(data *dataStruct) {

	// если подключение уже авторизовано, то пропускаем
	if data.connection.UserId > 0 {
		return
	}

	// получаем переданные параметры
	requestData := clientConnectRequest{}
	err := json.Unmarshal(data.requestData, &requestData)
	if err != nil {
		Error(data.connection, 103, err)
		return
	}

	splitToken := strings.Split(requestData.WSData.Token, ":")
	companyId := int64(0)
	channel := ws.DefaultChannel

	if len(splitToken) == 2 {
		channel = splitToken[0]
	}

	if len(splitToken) == 3 {
		channel = splitToken[0]
		companyId, _ = strconv.ParseInt(splitToken[1], 10, 64)
	}

	isolation := data.companyEnvList.GetEnv(companyId)
	if isolation == nil {

		Error(data.connection, 404, fmt.Sprintf("company is not served"))
		return
	}

	// получаем дополнительную информацию о соединении, если оно прислало верный токен
	platform, deviceId, isValid := isolation.TokenStore.GetConnectionInfoIfTokenValid(requestData.WSData.Token, requestData.WSData.UserID)
	if !isValid {

		Error(data.connection, 101, fmt.Sprintf("Invalid token, deviceId: %s platform: %s", deviceId, platform))
		return
	}

	// если передана сессия, то проверяем её
	config, err := conf.GetConfig()
	if requestData.WSData.PivotSession != "" && (config.Role == "pivot" || config.CurrentServer == "monolith") {

		// делаем запрос на проверку сессии
		sessionUniq, err := gatewayPhpPivot.ValidateUserSession(requestData.WSData.UserID, requestData.WSData.PivotSession)
		if err != nil {

			Error(data.connection, 102, fmt.Sprintf("Invalid pivot_session"))
			return
		}

		// записываем sessionUniq в подключение
		data.connection.SessionUniq = sessionUniq
	}

	// сохраняем информацию о подключении
	ws.SaveConnectionInfo(data.connection, isolation.AnalyticWsStore, isolation.UserConnectionStore, isolation.AnalyticStore, isolation.ThreadKeyStore, isolation.ThreadUcStore, requestData.WSData.UserID, platform, deviceId, handlerVersion, companyId, requestData.WSData.MethodConfigHash, requestData.WSData.AppVersion, channel)

	// проверяем существование конфига поддерживаемых версий ws событий
	if data.connection.IsHaveSupportVersionedEvent() && !method_config.IsConfigExist(requestData.WSData.MethodConfigHash) {

		// просим приложение прислать нам конфиг
		sendRequestMethodConfig(data.connection)
		return
	}

	// при успешном подключении
	onSuccessConnection(data.connection, isolation)

	// записываем активность
	config, err = conf.GetConfig()
	if config.Role == "pivot" || config.CurrentServer == "monolith" {

		err := activitycache.AddActivity(data.connection.UserId, functions.GetCurrentTimeStamp(), data.connection.SessionUniq)
		if err != nil {
			return
		}
	}
}

// отправляем событие для запроа у клиента отправить нам конфиг
func sendRequestMethodConfig(connection *ws.ConnectionStruct) {

	// считаем количество просьб на отправку конфига с методами
	connection.IncCounter("row0", 1)

	sendWsEvent(connection, "talking.request_method_config", struct{}{})
}

// request client.send_method_config
type clientSendMethodConfigRequest struct {
	WSData struct {
		MethodConfig     string `json:"method_config"`
		MethodConfigHash string `json:"method_config_hash"`
		Signature        string `json:"signature,omitempty"`
	} `json:"ws_data"`
}

// обрабатываем конфиг с версиями поддерживаемых событий от клиента
func (client) SendMethodConfig(data *dataStruct) {

	// если от подключения не ожидаем отправки конфига
	if false == data.connection.IsShouldSendEventConfig() {
		return
	}

	// получаем переданные параметры
	requestData := clientSendMethodConfigRequest{}
	err := json.Unmarshal(data.requestData, &requestData)
	if err != nil {

		data.connection.IncCounter("row1", 1)
		Error(data.connection, 103, err)
		return
	}

	// валидируем конфиг
	err = method_config.ValidateContent(requestData.WSData.MethodConfigHash, requestData.WSData.MethodConfig)
	if err != nil {

		data.connection.IncCounter("row2", 1)
		log.Errorf("method config content validation is failed, user_id: %d", data.connection.UserId)
		Error(data.connection, 104, err)
		return
	}

	// парсим содержимое конфига
	methodConfig, err := method_config.ParseContent(requestData.WSData.MethodConfig)
	if err != nil {

		data.connection.IncCounter("row3", 1)
		log.Errorf("method config content parsing is failed, user_id: %d", data.connection.UserId)
		Error(data.connection, 106, err)
		return
	}

	isolation := data.companyEnvList.GetEnv(data.connection.CompanyId)
	if isolation == nil {

		Error(data.connection, 404, fmt.Sprintf("company is not served"))
		return
	}

	// сохраняем конфиг
	eventConfig := method_config.MakeConfig(methodConfig, data.connection.GetPlatform(), data.connection.GetAppVersion())
	method_config.SaveConfig(data.connection.UserId, requestData.WSData.MethodConfigHash, eventConfig)

	// при успешном подключении
	onSuccessConnection(data.connection, isolation)
}

// при успешном подключении
func onSuccessConnection(connection *ws.ConnectionStruct, isolation *Isolation.Isolation) {

	// создаем новое подключение
	ws.NewConnection(connection, isolation.AnalyticWsStore, isolation.UserConnectionStore)
	isolation.GetGlobalIsolation().BalancerConn.AddConnectionToBalancer(connection.UserId)

	// сообщаем об успешном подключении
	sendWsEvent(connection, "talking.connected", struct {
		ServerTime int64 `json:"server_time"`
	}{
		ServerTime: functions.GetCurrentTimeStamp(),
	})
}

// request client.typing
type clientTypingRequest struct {
	WSData struct {
		ConversationKey string  `json:"conversation_key"`
		Type            int     `json:"type"`
		UserList        []int64 `json:"user_list"`
	} `json:"ws_data"`
}

// пользователь печатает текст
func (client) Typing(data *dataStruct) {

	// если подключение не авторизовано
	if data.connection.UserId == 0 {

		log.Error("user not authorized for this actions")
		return
	}

	isolation := data.companyEnvList.GetEnv(data.connection.CompanyId)
	if isolation == nil {

		Error(data.connection, 404, fmt.Sprintf("company is not served"))
		return
	}

	// получаем данные запроса
	requestData := clientTypingRequest{}
	err := json.Unmarshal(data.requestData, &requestData)
	if err != nil {

		// помечаем ошибку для соединения
		ws.AppendError(data.connection, err)
		Error(data.connection, 100, "bad json in request")
		return
	}

	// собираем список пользователей
	var userList []structures.SendEventUserStruct
	for _, userItem := range requestData.WSData.UserList {

		userList = append(userList, structures.SendEventUserStruct{
			UserId: userItem,
		})
	}

	// готовим структуру для запроса
	EventVersionList := event.MakeConversationTyping(data.connection.UserId, getTypingType(requestData.WSData.Type), requestData.WSData.ConversationKey)
	talking.SendEvent(isolation, userList, event.ConversationTypingEventName, EventVersionList, push.PushDataStruct{}, struct{}{}, "", "", data.connection.Channel)
}

// request client.create_thread_typing
type clientCreateThreadTypingRequest struct {
	WSData struct {
		ConversationKey string  `json:"conversation_key"`
		MessageKey      string  `json:"message_key"`
		Type            int     `json:"type"`
		UserList        []int64 `json:"user_list"`
	} `json:"ws_data"`
}

// пользователь печатает текст перед созданием треда
func (client) CreateThreadTyping(data *dataStruct) {

	// если подключение не авторизовано
	if data.connection.UserId == 0 {

		log.Error("user not authorized for this actions")
		return
	}

	isolation := data.companyEnvList.GetEnv(data.connection.CompanyId)
	if isolation == nil {

		Error(data.connection, 404, fmt.Sprintf("company is not served"))
		return
	}

	// получаем данные запроса
	requestData := clientCreateThreadTypingRequest{}
	err := json.Unmarshal(data.requestData, &requestData)
	if err != nil {

		// помечаем ошибку для соединения
		ws.AppendError(data.connection, err)
		Error(data.connection, 100, "bad json in request")
		return
	}

	// готовим структуру для запроса
	EventVersionList := event.MakeConversationCreateThreadTyping(
		data.connection.UserId,
		getTypingType(requestData.WSData.Type),
		requestData.WSData.MessageKey,
		requestData.WSData.ConversationKey,
	)

	talking.SendTypingEvent(isolation, requestData.WSData.UserList, event.ConversationCreateThreadTypingEventName, EventVersionList, functions.GenerateUuid(), data.connection.Channel)
}

// request client.ping
type clientPingRequest struct {
	WSData struct {
		Uid string `json:"uid"`
	} `json:"ws_data"`
}

// Ping/Pong (Heartbeat)
func (client) Ping(data *dataStruct) {

	// если подключение не авторизовано просто выходим
	if data.connection.UserId == 0 {
		return
	}

	// Получаем данные запроса
	requestData := clientPingRequest{}
	err := json.Unmarshal(data.requestData, &requestData)
	if err != nil {

		// помечаем ошибку для соединения
		ws.AppendError(data.connection, err)
		Error(data.connection, 100, "bad json in request")
		return
	}

	sendWsEvent(data.connection, "talking.pong", struct {
		Uid string `json:"uid"`
	}{
		Uid: requestData.WSData.Uid,
	})

	// записываем активность
	config, err := conf.GetConfig()
	if config.Role == "pivot" || config.CurrentServer == "monolith" {

		err := activitycache.AddActivity(data.connection.UserId, functions.GetCurrentTimeStamp(), data.connection.SessionUniq)
		if err != nil {
			return
		}
	}
}

// ставим фокус на подключение
func (client) Focus(data *dataStruct) {

	// если подключение не авторизовано
	if data.connection.UserId == 0 {

		log.Error("user not authorized for this actions")
		return
	}

	// переключаем флаг isFocused
	ws.SetFocus(data.connection, true)

	sendWsEvent(data.connection, "talking.focus", struct{}{})
}

// убираем фокус с подключения
func (client) Unfocus(data *dataStruct) {

	// если подключение не авторизовано
	if data.connection.UserId == 0 {

		log.Error("user not authorized for this actions")
		return
	}

	// переключаем флаг isFocused
	ws.SetFocus(data.connection, false)

	sendWsEvent(data.connection, "talking.unfocus", struct{}{})
}

// request client.ack
type clientAckRequest struct {
	WSData struct {
		WSUniqueID string `json:"ws_unique_id"`
	} `json:"ws_data"`
}

// подтверждаем действия
func (client) Ack(data *dataStruct) {

	// если подключение не авторизовано
	if data.connection.UserId == 0 {

		log.Error("user not authorized for this actions")
		return
	}

	// получаем данные запроса
	requestData := clientAckRequest{}
	err := json.Unmarshal(data.requestData, &requestData)
	if err != nil {

		// помечаем ошибку для соединения
		ws.AppendError(data.connection, err)
		Error(data.connection, 100, "bad json in request")
		return
	}

	// подтверждаем получение websocket события
	delay, err := ws.ConfirmAck(data.connection, requestData.WSData.WSUniqueID)
	if err != nil {

		// помечаем ошибку для соединения
		ws.AppendError(data.connection, err)
		return
	}

	// подсчитываем delay для соединения
	ws.AckRequest(data.connection, delay.Nanoseconds()/int64(time.Millisecond))
}

// request client.opened
type clientOpenedRequest struct {
	WSData struct {
		ThreadKey string `json:"thread_key"`
	} `json:"ws_data"`
}

// подтверждаем что тред открыт
func (client) VerifyThreadOpened(data *dataStruct) {

	// если подключение не авторизовано
	if data.connection.UserId == 0 {

		log.Error("user not authorized for this actions")
		return
	}

	// получаем данные запроса
	requestData := clientOpenedRequest{}
	err := json.Unmarshal(data.requestData, &requestData)
	if err != nil {

		// помечаем ошибку для соединения
		ws.AppendError(data.connection, err)
		Error(data.connection, 100, "bad json in request")
		return
	}

	isolation := data.companyEnvList.GetEnv(data.connection.CompanyId)
	if isolation == nil {

		Error(data.connection, 404, fmt.Sprintf("company is not served"))
		return
	}

	// добавляем соединение в хранилище
	ok := thread.AddConnectionToThread(data.connection.ThreadKStore, isolation.ThreadUcStore, isolation.ThreadAStore, requestData.WSData.ThreadKey, data.connection.UserId, data.connection.ConnId)
	if !ok {

		log.Error("failed to add the connection to the store")
		return
	}
}

// request client.close
type clientCloseRequest struct {
	WSData struct {
		ThreadKey string `json:"thread_key"`
	} `json:"ws_data"`
}

// закрываем тред
func (client) ThreadClose(data *dataStruct) {

	// если подключение не авторизовано
	if data.connection.UserId == 0 {

		log.Error("user not authorized for this actions")
		return
	}

	// получаем данные запроса
	requestData := clientCloseRequest{}
	err := json.Unmarshal(data.requestData, &requestData)
	if err != nil {

		// помечаем ошибку для соединения
		ws.AppendError(data.connection, err)
		Error(data.connection, 100, "bad json in request")
		return
	}

	// удаляем соединение из хранилища
	data.connection.ThreadKStore.DeleteConnectionFromThread(data.connection.ThreadUserConnectionStore, data.connection.UserId, data.connection.ConnId)

	// отправляем событие о закрытии треда
	sendWsEvent(data.connection, "event.thread_close", struct {
		ThreadKey string `json:"thread_key"`
	}{
		ThreadKey: requestData.WSData.ThreadKey,
	})
}

// request client.threadTyping
type clientThreadTypingRequest struct {
	Data struct {
		ThreadKey string `json:"thread_key"`
		Type      int    `json:"type"`
	} `json:"ws_data"`
}

// ThreadTyping пишем в тред
func (client) ThreadTyping(data *dataStruct) {

	// если подключение не авторизовано
	if data.connection.UserId == 0 {

		log.Error("user not authorized for this actions")
		return
	}

	// получаем данные запроса
	requestData := clientThreadTypingRequest{}
	err := json.Unmarshal(data.requestData, &requestData)
	if err != nil {

		// помечаем ошибку для соединения
		ws.AppendError(data.connection, err)
		Error(data.connection, 100, "bad json in request")
		return
	}

	if !data.connection.ThreadUserConnectionStore.IsUserThreadListener(requestData.Data.ThreadKey, data.connection.UserId) {

		log.Error("user not thread member")
		return
	}

	// готовим структуру для запроса
	EventVersionList := event.MakeThreadTyping(
		data.connection.UserId,
		getTypingType(requestData.Data.Type),
		requestData.Data.ThreadKey,
	)

	// вызываем метод
	talking.SendThreadTypingEvent(
		data.connection.UserConnectionStore, data.connection.ThreadUserConnectionStore, event.ThreadTypingEventName, requestData.Data.ThreadKey, EventVersionList,
	)
}

// функция для генерации typing hash
func getTypingHash(customSalt string, userList []int64) string {

	// Упорядочиваем идентификаторы участников диалога (по возрастанию)
	var temp []int
	for _, userID := range userList {
		temp = append(temp, int(userID))
	}
	sort.Ints(temp)

	// Формируем строку с идентификаторами участников
	userListString := functions.ArrayToString(temp, ",")

	// Хэшируем conversation_hash_salt
	// nosemgrep
	h := sha1.New()
	_, _ = h.Write([]byte(customSalt))

	// Конкатенируем хэш от строки с солью и строку с идентификаторами участников
	conversationString := fmt.Sprintf("%x%s", h.Sum(nil), userListString)

	h.Reset()
	_, _ = h.Write([]byte(conversationString))

	// Хэшируем полученную строку
	return fmt.Sprintf("%x", h.Sum(nil))
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// определяем тип тайпинга
func getTypingType(typingType int) int {

	// в зависимости от типа тайпинга
	switch typingType {

	case 0: // текстовый тайпинг
	case 1: // голосовой тайпинг
	case 2: // тайпинг при отправке картинки
	case 3: // тайпинг при отправке видео
	case 4: // тайпинг при загрузке файла(не видео и не картинка)
		break

		// если пришел тайпинг с неизвестным значением - присваиваем ему текстовый тип
	default:
		typingType = 0
	}

	return typingType
}

// возвращает ответ
func sendWsEvent(connection *ws.ConnectionStruct, Type string, Data interface{}) {

	wsUniqueID := functions.GenerateUuid()

	// пользователь имеет активное подключение, отправляем событие
	connection.SendInternalEventViaConnection(connection.UserId, Type, Data, wsUniqueID)
}
