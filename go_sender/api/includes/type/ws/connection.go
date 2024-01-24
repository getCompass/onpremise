package ws

// пакет для взаимодействия с соединением

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/gorilla/websocket"
	analyticsWs "go_sender/api/includes/type/analytics/ws"
	"go_sender/api/includes/type/thread"
	"net/http"
	"sync"
	"time"
)

const (
	readBufferSize  = 1024
	writeBufferSize = 1024
	maxMessageSize  = 78000 // Maximum message size allowed from peer.
)

var (
	writeWait  = 10 * time.Second    // Time allowed to write a message to the peer.
	pongWait   = 60 * time.Second    // Time allowed to read the next pong message from the peer.
	pingPeriod = (pongWait * 9) / 10 // Send pings to peer with this period. Must be less than pongWait.
	authWait   = 10 * time.Second    // Time allowed to client authorize.
	upgrader   = websocket.Upgrader{
		ReadBufferSize:  readBufferSize,
		WriteBufferSize: writeBufferSize,
		CheckOrigin: func(r *http.Request) bool {
			return true
		},
	}
)

type ackStore struct {
	store map[string]*ackStruct
	mx    sync.Mutex
}

// структура соединение
type ConnectionStruct struct {
	mx                          sync.RWMutex
	ConnId                      int64           // идентификатор соединения
	UserId                      int64           // идентификатор пользователя
	CompanyId                   int64           // id компании
	isFocused                   bool            // focus статус подключения
	HandlerVersion              int             // версия обработчика WebSocket Handler
	connection                  *websocket.Conn // объект с соединением
	platform                    string          // платформа соединения
	deviceId                    string          // device_id соединения
	broadcast                   chan []byte     // канал соединения
	closeConnectionError        error           // по которой было закрыто соединение
	close                       chan error      // канал, в который пишем, когда падает Unexpected error
	analyticsData               *analyticsRow   // объект с аналитической информацией о подключении
	ackList                     *ackStore       // хранилище ack запросов, необходимых для подтверждения с клиентской стороны
	userAgent                   string          // user-agent соединения
	ipAddress                   string          // ip-адрес соединения
	isBlock                     bool            // флаг, закрыт ли коннект для получения новых ws
	MethodConfigHash            string          // sha1 хэш-сумма от содержимого конфига поддерживаемых версий ws событий
	appVersion                  string          // версия приложения
	analyticStoreWs             *analyticsWs.AnalyticStore
	UserConnectionStore         *UserConnectionStore
	analyticStore               *AnalyticStore
	ThreadKStore                *thread.KeyStore
	ThreadUserConnectionStore   *thread.UserConnectionStore
	isHaveSupportVersionedEvent bool // имеется ли поддержка версионных событий
}

// сохраняем данные о соединении
func SaveConnectionInfo(
	c *ConnectionStruct,
	analyticStoreWs *analyticsWs.AnalyticStore,
	userConnectionStore *UserConnectionStore,
	analyticStore *AnalyticStore,
	ThreadKStore *thread.KeyStore,
	ThreadUserConnectionStore *thread.UserConnectionStore,
	userId int64,
	platform string,
	deviceId string,
	HandlerVersion int,
	companyId int64,
	methodConfigHash string,
	appVersion string) {

	// временная метка, когда свершилась авторизация подключения
	connectedAt := functions.GetCurrentTimeStamp()

	// записываем данные в объект соединения
	c.mx.Lock()
	c.HandlerVersion = HandlerVersion
	c.UserId = userId
	c.platform = platform
	c.deviceId = deviceId
	c.analyticStoreWs = analyticStoreWs
	c.UserConnectionStore = userConnectionStore
	c.CompanyId = companyId
	c.isFocused = true
	c.analyticsData.ConnectedAt = connectedAt
	c.analyticsData.UserID = userId
	c.analyticsData.Platform = platform
	c.analyticStore = analyticStore
	c.ThreadKStore = ThreadKStore
	c.ThreadUserConnectionStore = ThreadUserConnectionStore
	c.MethodConfigHash = methodConfigHash
	c.appVersion = appVersion
	c.isHaveSupportVersionedEvent = true

	// если не прислали app_version && method_config_hash
	// то считаем что клиент не поддерживает версионные события
	if appVersion == "" && methodConfigHash == "" {
		c.isHaveSupportVersionedEvent = false
	}

	c.mx.Unlock()
}

// сохраняем новое подключение
func NewConnection(
	c *ConnectionStruct,
	analyticStoreWs *analyticsWs.AnalyticStore,
	userConnectionStore *UserConnectionStore) {

	// снимаем оганичение для получения ws событий
	c.mx.Lock()
	c.isBlock = false
	c.mx.Unlock()

	// создадим и запишем обьект аналитики
	analyticItem := &analyticsWs.WsStruct{
		Uuid:      functions.GenerateUuid(),
		UserId:    c.UserId,
		CreatedAt: c.analyticsData.ConnectedAt,
		EndAt:     0,
		EventName: "open_ws",
		Platform:  c.platform,
	}

	analyticItem.OnOpenWsConnect()
	analyticStoreWs.Add(analyticItem, functions.GenerateUuid())

	// сохраняем объект подключения
	userConnectionStore.saveUserConnection(c.UserId, c)
}

// сохраняем пользовательское соединение
func (ucStore *UserConnectionStore) saveUserConnection(userID int64, userConnectionObj *ConnectionStruct) {

	ucStore.mx.Lock()
	defer ucStore.mx.Unlock()
	userConnections, exist := ucStore.store[userID]

	// если не существует, то добавляем и сохраняем
	if !exist {

		// создаем объект с подключениями
		userConnections = &connectionList{
			connList: make(map[int64]*ConnectionStruct),
			mx:       sync.RWMutex{},
		}
		ucStore.store[userID] = userConnections
	}

	// иначе добавляем к существующему объекту новое подключение
	userConnections.mx.Lock()
	userConnections.connList[userConnectionObj.ConnId] = userConnectionObj
	userConnections.mx.Unlock()
}

// устанавливаем isFocused статус
func SetFocus(connection *ConnectionStruct, isFocused bool) {

	connection.isFocused = isFocused
}

// слушаем мультиплексор
func Listen(writerItem http.ResponseWriter, requestItem *http.Request, callback func(connection *ConnectionStruct, requestBytes []byte) error) {

	// получаем соединение
	connectionItem, err := upgrader.Upgrade(writerItem, requestItem, nil)
	if err != nil {
		panic(err)
	}

	connId := time.Now().UnixNano()
	ip := getIpAddress(requestItem)
	ua := requestItem.UserAgent()

	// создаем объект пользовательского подключения
	objConnection := ConnectionStruct{
		connection:    connectionItem,
		ConnId:        connId,
		broadcast:     make(chan []byte, 1000),
		close:         make(chan error, 1),
		userAgent:     ua,
		ipAddress:     ip,
		analyticsData: initAnalyticsData(connId, 0, "", ua, ip),

		// изначально соединение заблокировано на получение ws событий
		// НО это не блокирует получений internal событий,
		// отправляемых через функцию sendInternalEventToBroadcast
		isBlock: true,
	}

	// PING
	go objConnection.setWriteHandler()

	// слушаем сообщения от клиента в соединении
	go objConnection.readMessage(callback)
}

// получаем ip адрес, который создает соединение
func getIpAddress(requestItem *http.Request) string {

	// проверям наличие ip в заголовке
	if ipAddressHeader, isExist := requestItem.Header["X-Forwarded-For"]; isExist {

		// проверяем длину среза
		if len(ipAddressHeader) != 0 {
			return ipAddressHeader[0]
		}
	}

	return ""
}

// вешаем Ping Handler
func (connection *ConnectionStruct) setWriteHandler() {

	ticker := time.NewTicker(pingPeriod)
	defer connection.afterWriteHandler(ticker)

	for {
		err := connection.doWorkWriteHandler(ticker)
		if err != nil {

			log.Errorf("log err %s", err.Error())
			connection.onErrorPingHandler(fmt.Sprintf("%v", err))
			return
		}
	}
}

// после выполнение пинг хендлера
func (connection *ConnectionStruct) afterWriteHandler(ticker *time.Ticker) {

	ticker.Stop()

	if connection.UserId > 0 {

		connection.UserConnectionStore.CloseConnectionsByDeviceID(connection.UserId, connection.deviceId)
	} else {
		closeConnection(connection)
	}
}

// работа самого воркера ping handler
func (connection *ConnectionStruct) doWorkWriteHandler(ticker *time.Ticker) error {

	var err error = nil

	select {
	case message, ok := <-connection.broadcast:
		err = connection.onGetMessageInWriteHandler(message, ok)
	case <-ticker.C:

		err = connection.onTickerInWriteHandler()

	case <-time.After(authWait):

		if connection.UserId == 0 {
			err = fmt.Errorf("соединение не было авторизовано: Error: connection: client not logged in")
		}

	case err = <-connection.close:
		connection.closeConnectionError = err
	}
	if err != nil {
		log.Errorf("err %s", err.Error())
	}
	return err
}

// слушаем канал broadcast
func (connection *ConnectionStruct) onGetMessageInWriteHandler(message []byte, ok bool) error {

	err := connection.connection.SetWriteDeadline(time.Now().Add(writeWait))
	if err != nil {
		return err
	}

	if !ok {
		err := connection.write(websocket.CloseMessage, []byte{})
		if err != nil {
			return fmt.Errorf("брякнулись #3: Error: %v", err)
		}
		return fmt.Errorf("брякнулись #1: не получили собщение из канала")
	}

	err = connection.write(websocket.TextMessage, message)
	if err != nil {
		return fmt.Errorf("брякнулись #4: Error: %v", err)
	}
	return nil
}

// при срабатывании тикера
func (connection *ConnectionStruct) onTickerInWriteHandler() error {

	err := connection.connection.SetWriteDeadline(time.Now().Add(writeWait))
	if err != nil {

		connection.onErrorPingHandler(fmt.Sprintf("Error: %v", err))
		return err
	}

	err = connection.write(websocket.PingMessage, []byte{})
	if err != nil {

		connection.onErrorPingHandler(fmt.Sprintf("Брякнулись #2: Error: %v", err))
		return err
	}

	// очищаем ackList
	connection.cleanStorage()
	return nil
}

// выполняется при возникновении ошибки в пинг хенделере
func (connection *ConnectionStruct) onErrorPingHandler(message string) {

	AppendError(connection, fmt.Errorf(message))
}

// слушаем первое сообщение
func (connection *ConnectionStruct) readMessage(callback func(connection *ConnectionStruct, requestBytes []byte) error) {

	// устанавливаем опции
	connection.setOptions()

	for {

		// читаем request
		messageType, requestBytes, err := connection.connection.ReadMessage()

		// закрываем соединение
		if err != nil {

			log.Errorf("%s", err.Error())
			connection.closeConnection(messageType, err)
			return
		}

		// выполняем метод
		err = callback(connection, requestBytes)
		if err != nil {
			log.Errorf("%v %s", err, requestBytes)
		}
	}
}

// устанавливаем опции для WS соединения
func (connection *ConnectionStruct) setOptions() {

	log.Debug(fmt.Sprintf("Устанавливаем опции на соединение. %v", pingPeriod))

	store := &ackStore{
		store: make(map[string]*ackStruct),
		mx:    sync.Mutex{},
	}

	//
	connection.connection.SetReadLimit(maxMessageSize)
	_ = connection.connection.SetReadDeadline(time.Now().Add(pongWait))
	connection.connection.SetPongHandler(func(string) error { _ = connection.connection.SetReadDeadline(time.Now().Add(pongWait)); return nil })
	connection.ackList = store
}

// закрываем соедиенение
func (connection *ConnectionStruct) closeConnection(messageType int, err error) {

	select {

	// закрываем соединение
	case connection.close <- err:
		log.Info(fmt.Sprintf("Брякнулись messageType: %d Error: %v", messageType, err))

	default:
		log.Error(fmt.Sprintf("Не смогли отправить ошибку в канал err: messageType: %d %v", messageType, err))
	}

	endAt := functions.GetCurrentTimeStamp()
	timeMs := endAt - connection.analyticsData.ConnectedAt

	// создадим и запишем обьект аналитики
	analyticItem := &analyticsWs.WsStruct{
		Uuid:      functions.GenerateUuid(),
		UserId:    connection.analyticsData.UserID,
		CreatedAt: connection.analyticsData.ConnectedAt,
		EndAt:     endAt,
		TimeMs:    timeMs,
		EventName: "close_ws",
		Platform:  connection.analyticsData.Platform,
	}

	analyticItem.OnCloseWsConnect()
	if connection.analyticStoreWs != nil {
		connection.analyticStoreWs.Add(analyticItem, functions.GenerateUuid())
	}
}

// пишем сообщение в соединение
func (connection *ConnectionStruct) write(messageType int, msg []byte) error {

	//
	err := connection.connection.WriteMessage(messageType, msg)

	return err
}

// должно ли подключение прислать конфиг с версиями поддерживаемых событий от клиента
func (connection *ConnectionStruct) IsShouldSendEventConfig() bool {

	return connection.UserId > 0 && connection.isBlock
}

// получаем платформу
func (connection *ConnectionStruct) GetPlatform() string {

	return connection.platform
}

// получаем версию приложения
func (connection *ConnectionStruct) GetAppVersion() string {

	return connection.appVersion
}

// поддерживает ли соединение версионные события
func (connection *ConnectionStruct) IsHaveSupportVersionedEvent() bool {

	return connection.isHaveSupportVersionedEvent
}

// инкрементим счетчик
func (connection *ConnectionStruct) IncCounter(row string, incValue int) {

	connection.analyticStoreWs.IncCounter(row, incValue)
}
