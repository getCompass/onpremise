package observer

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/conf"
	Isolation "go_sender/api/includes/type/isolation"
	"time"
)

// оставил закомменченный пример, чтобы было проще потом и не искать как выглядит ;)
// var is1DayWork atomic.Value

var (
	analyticsGoroutineInterval     = time.Minute * 1
	tokenObserverGoroutineInterval = time.Minute * 1
	activityGoroutineInterval      = time.Millisecond * 500
)

// WorkCompanyObserver Work метод для выполнения работы в компаниях через время
func WorkCompanyObserver(ctx context.Context, isolation *Isolation.Isolation) {

	go goWorkAnalyticsObserver(ctx, isolation)
	go goWorkTokenObserver(ctx, isolation)

	// работаем с активностью
	config, _ := conf.GetConfig()
	if config.Role == "pivot" || config.CurrentServer == "monolith" {
		go goWorkPingObserver(ctx)
	}

}

// запускаем observer который сбрасывает кеш аналитики в collector-agent
func goWorkTokenObserver(ctx context.Context, isolation *Isolation.Isolation) {

	for {

		select {

		case <-time.After(tokenObserverGoroutineInterval):

			isolation.TokenStore.ClearOldToken()
		case <-ctx.Done():

			log.Infof("Закрыли обсервер таймера для компании %d", isolation.GetCompanyId())
			return

		}
	}
}
