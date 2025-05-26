package observer

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	gatewayGoActivity "go_sender/api/includes/gateway/go_activity"
	"go_sender/api/includes/type/activitycache"
	"go_sender/api/includes/type/structures"
	"time"
)

const (
	batchSize = 1000 // размер пачки
)

// запускаем observer который сбрасывает кеш активность пользователей
func goWorkPingObserver(ctx context.Context) {

	for {

		select {

		case <-time.After(activityGoroutineInterval):

			SendPingActivity()
		case <-ctx.Done():

			log.Infof("Закрыли обсервер таймера")
			return

		}
	}
}

// SendPingActivity отправляем пинги пользователей в активность
func SendPingActivity() {

	// получаем все, что есть в переменную
	activities := activitycache.GetAllActivity()
	if len(activities) <= 0 {
		return
	}

	// чистим кеш, чтобы сразу наполнялись только новые записи
	activitycache.ResetCache()

	// формируем и отправляем данные пакетами
	userPingData := make([]structures.UserPingData, 0, batchSize)
	for i, a := range activities {
		userPingData = append(userPingData, structures.UserPingData{
			UserID:       a[0].(int64),
			SessionUniq:  a[1].(string),
			LastPingTime: a[2].(int64),
		})

		// разбиваем на пачки
		if len(userPingData) == batchSize || i == len(activities)-1 {
			gatewayGoActivity.SendPingBatching(userPingData)
			userPingData = userPingData[:0] // очищаем пачку
		}
	}
}
