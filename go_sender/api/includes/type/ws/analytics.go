package ws

/*
 * модель для работы с аналитикой по websocket соединениям
 */

import (
	"sync"
)

type ackEventStore struct {
	store map[string]*ackEventStruct
	mx    sync.RWMutex
}

// структура записи с аналитикой
type analyticsRow struct {
	mu                    sync.Mutex
	ConnectionID          int64                     `json:"connection_id"`
	UserID                int64                     `json:"user_id"`
	ConnectedAt           int64                     `json:"connected_at"`
	Platform              string                    `json:"platform"`
	UserAgent             string                    `json:"user_agent"`
	IpAddress             string                    `json:"ip_address"`
	MinPing               int64                     `json:"min_ping"`
	AvgPing               int64                     `json:"avg_ping"`
	MaxPing               int64                     `json:"max_ping"`
	AckRequested          int64                     `json:"ack_requested"`
	AckConfirmed          int64                     `json:"ack_confirmed"`
	ClosedAt              int64                     `json:"closed_at"`
	CloseConnectionReason error                     `json:"close_connection_reason"`
	ErrorList             []error                   `json:"error_list"`
	AckEventList          map[string]ackEventStruct `json:"ack_event_list"`
	AckEventListStore     *ackEventStore
}

type AnalyticStore struct {
	store map[int64]*analyticsRow
	mx    sync.RWMutex
}

func MakeAnalyticStore() *AnalyticStore {

	return &AnalyticStore{
		store: make(map[int64]*analyticsRow),
		mx:    sync.RWMutex{},
	}
}

// получаем аналитику из хранилища
func (aStore *AnalyticStore) GetAnalyticsFromStore() map[int64]interface{} {

	aStore.mx.RLock()
	defer aStore.mx.RUnlock()

	analyticsDataObj := make(map[int64]interface{})
	for k, v := range aStore.store {

		AckEventList := make(map[string]ackEventStruct)
		v.AckEventListStore.mx.Lock()
		for k2, v2 := range v.AckEventListStore.store {

			AckEventList[k2] = ackEventStruct{
				AckConfirmed: v2.AckConfirmed,
				AckRequested: v2.AckRequested,
			}
		}
		v.AckEventListStore.mx.Unlock()

		// добавляем аналитику в обьект для отдачи
		v.AckEventList = AckEventList
		analyticsDataObj[k] = v
	}

	return analyticsDataObj
}

// удаляем аналитику из хранилища
func (aStore *AnalyticStore) DeleteAnalyticsFromStore(key int64) {

	aStore.mx.Lock()
	defer aStore.mx.Unlock()

	delete(aStore.store, key)
}

// пересчитываем аналитические данные с пингом
func AckRequest(c *ConnectionStruct, ping int64) {

	c.analyticsData.mu.Lock()
	if c.analyticsData.MinPing > ping || c.analyticsData.MinPing == 0 {
		c.analyticsData.MinPing = ping
	}

	if c.analyticsData.MaxPing < ping {
		c.analyticsData.MaxPing = ping
	}

	if c.analyticsData.AckRequested == 1 {
		c.analyticsData.AvgPing = ping
	} else {
		c.analyticsData.AvgPing = (c.analyticsData.AvgPing*c.analyticsData.AckRequested + ping) / (c.analyticsData.AckRequested + 1)
	}

	c.analyticsData.AckConfirmed++
	c.analyticsData.mu.Unlock()
}

// добавляем ошибку к подключению
func AppendError(c *ConnectionStruct, Error error) {

	c.analyticsData.mu.Lock()
	c.analyticsData.ErrorList = append(c.analyticsData.ErrorList, Error)
	c.analyticsData.mu.Unlock()
}

// -------------------------------------------------------
// PROTECTED
// -------------------------------------------------------

// создаем объект с аналитикой по соединению
func initAnalyticsData(connId int64, userId int64, platform string, userAgent string, ipAddress string) *analyticsRow {

	return &analyticsRow{
		mu:           sync.Mutex{},
		ConnectionID: connId,
		UserID:       userId,
		Platform:     platform,
		UserAgent:    userAgent,
		IpAddress:    ipAddress,
		AckEventListStore: &ackEventStore{
			store: make(map[string]*ackEventStruct),
			mx:    sync.RWMutex{},
		},
		ErrorList: []error{},
	}
}

// сохраняем объект с аналитикой на закрытие соединения
func (aStore *AnalyticStore) storeAnalyticsData(analyticsRowObj *analyticsRow, closedAt int64, closeError error) {

	// дополняем данные
	analyticsRowObj.ClosedAt = closedAt
	analyticsRowObj.CloseConnectionReason = closeError
	analyticsRowObj.ErrorList = append(analyticsRowObj.ErrorList, closeError)

	// сохраняем в хранилище
	aStore.mx.Lock()
	aStore.store[analyticsRowObj.ConnectionID] = analyticsRowObj
	aStore.mx.Unlock()
}

// инкрементим количество необходимых к подтверждению запросов
func (connection *ConnectionStruct) incAckRequested() {

	connection.analyticsData.mu.Lock()
	connection.analyticsData.AckRequested++

	connection.analyticsData.mu.Unlock()
}
