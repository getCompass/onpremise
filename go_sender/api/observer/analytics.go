package observer

import (
	"context"
	"encoding/json"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	analyticsWs "go_sender/api/includes/type/analytics/ws"
	"go_sender/api/includes/type/curl"
	Isolation "go_sender/api/includes/type/isolation"
	"google.golang.org/grpc/status"
	"time"
)

// структура запроса с отправкой аналитики ws
type saveWsAnalyticsDataRequestStruct struct {
	Type      string                           `json:"type"`
	Data      map[string]*analyticsWs.WsStruct `json:"data"`
	Namespace string                           `json:"namespace"`
	CompanyId int64                            `json:"company_id"`
	Key       string                           `json:"key"`
	EventTime int64                            `json:"event_time"`
}

// структура запроса с отправкой счетчиков
type saveCounterAnalyticsDataRequestStruct struct {
	Type         string         `json:"type"`
	Namespace    string         `json:"namespace"`
	CompanyId    int64          `json:"company_id"`
	KeyValueList map[string]int `json:"key_value_list"`
	EventTime    int64          `json:"event_time"`
}

// запускаем observer который сбрасывает кеш аналитики в collector-agent
func goWorkAnalyticsObserver(ctx context.Context, isolation *Isolation.Isolation) {

	for {

		select {

		case <-time.After(analyticsGoroutineInterval):

			SaveAnalyticsCache(isolation)
		case <-ctx.Done():

			log.Infof("Закрыли обсервер таймера для компании %d", isolation.GetCompanyId())
			return

		}
	}
}

// сохраняем кеш
func SaveAnalyticsCache(isolation *Isolation.Isolation) {

	// получаем кэш
	cache, counterCache := isolation.AnalyticWsStore.GetAllFromUpdateCache()

	// если есть аналитика по ws
	if len(cache) > 0 {
		saveWsAnalyticsCache(isolation, cache)
	}

	// если есть счетчики
	if len(counterCache) > 0 {
		saveCountersAnalyticsCache(isolation, counterCache)
	}
}

// сохраняем аналитику по ws соединениям
func saveWsAnalyticsCache(isolation *Isolation.Isolation, cache map[string]*analyticsWs.WsStruct) {

	// собираем объект запроса
	request := saveWsAnalyticsDataRequestStruct{
		Type:      "TYPE_DEBUG_ADD",
		Data:      cache,
		Namespace: "go_sender",
		CompanyId: isolation.GetCompanyId(),
		EventTime: functions.GetCurrentTimeStamp(),
		Key:       "ws",
	}

	jsonRequest, _ := json.Marshal(request)

	conf := isolation.GetGlobalIsolation().GetShardingConfig()
	err := doSendRequest(
		"stat.add",
		conf.Go["collector_agent"].Protocol,
		conf.Go["collector_agent"].Host,
		conf.Go["collector_agent"].Port,
		jsonRequest,
		isolation.GetCompanyId(),
	)
	if err != nil {

		log.Errorf("Не смогли сохранить аналитику по ws соединениям")
		return
	}

	log.Infof("Аналитика по ws соединениям сохранена успешно")
}

// сохраняем аналитику со счетчиками
func saveCountersAnalyticsCache(isolation *Isolation.Isolation, counterCache map[string]int) {

	// собираем объект запроса
	request := saveCounterAnalyticsDataRequestStruct{
		Type:         "TYPE_STAT_INC",
		KeyValueList: counterCache,
		Namespace:    "go_sender",
		CompanyId:    isolation.GetCompanyId(),
		EventTime:    functions.GetCurrentTimeStamp(),
	}

	jsonRequest, _ := json.Marshal(request)

	conf := isolation.GetGlobalIsolation().GetShardingConfig()
	err := doSendRequest(
		"stat.incList",
		conf.Go["collector_agent"].Protocol,
		conf.Go["collector_agent"].Host,
		conf.Go["collector_agent"].Port,
		jsonRequest,
		isolation.GetCompanyId(),
	)
	if err != nil {

		log.Errorf("Не смогли сохранить аналитику со счетчиками")
		return
	}

	log.Infof("Аналитика со счетчиками сохранена успешно")
}

// методя для отправки запроса
func doSendRequest(methodName string, collectorAgentProtocol string, collectorAgentHost string, collectorAgentPort string, request []byte, companyId int64) error {

	// формируем запрос
	requestMap := map[string]string{
		"method":     methodName,
		"request":    string(request),
		"company_id": functions.Int64ToString(companyId),
	}

	apiUrl := fmt.Sprintf("%s://%s:%s", collectorAgentProtocol, collectorAgentHost, collectorAgentPort)

	// осуществляем запрос
	_, isSuccess := curl.SimplePost(apiUrl, requestMap)
	if !isSuccess {
		return status.Error(400, "post request failed")
	}

	return nil
}
