package observer

import (
	"encoding/json"
	"fmt"
	"go_pusher/api/conf"
	"go_pusher/api/includes/type/analyticspush"
	"go_pusher/api/includes/type/curl"
	"google.golang.org/grpc/status"
	"time"
)

// структура запроса с отправкой аналитики pusher
type savePusherAnalyticsDataRequestStruct struct {
	PushData map[string]analyticspush.PushStruct `json:"push_data"`
}

// запускаем observer который сбрасывает кеш аналитики в collector-agent
func goWorkAnalyticsObserver() {

	if isAnalyticsObserverWork.Load() != nil && isAnalyticsObserverWork.Load().(bool) {
		return
	}
	isAnalyticsObserverWork.Store(true)

	for {

		SaveAnalyticsCache()

		// спим
		time.Sleep(analyticsGoroutineInterval)
	}
}

// сохраняем кеш
func SaveAnalyticsCache() {

	// получаем кэш
	cache := analyticspush.GetAllFromUpdateCache()

	if len(cache) == 0 {
		return
	}

	// сливаем кеш в коллектор сервер
	go func() {

		// собираем объект запроса
		request := savePusherAnalyticsDataRequestStruct{
			PushData: cache,
		}

		jsonRequest, _ := json.Marshal(request)

		err := doSendRequest(jsonRequest)
		if err != nil {
			return
		}
	}()
	analyticspush.ClearUpdateCacheAndSwap()
}

// методя для отправки запроса
func doSendRequest(request []byte) error {

	// получаем адрес
	config := conf.GetShardingConfig()

	// формируем запрос
	requestMap := map[string]string{
		"method":  "analyticspush.savePush",
		"request": string(request),
	}

	// формируем ссылку
	apiUrl := fmt.Sprintf(config.Go["collector_agent"].Protocol + "://" + config.Go["collector_agent"].Host + ":" + config.Go["collector_agent"].Port)

	// осуществляем запрос
	_, isSuccess := curl.SimplePost(apiUrl, requestMap)
	if !isSuccess {
		return status.Error(400, "post request failed")
	}

	return nil
}
