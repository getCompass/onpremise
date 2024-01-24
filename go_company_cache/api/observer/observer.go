package observer

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	Isolation "go_company_cache/api/includes/type/isolation"
	"time"
)

// WorkCompanyObserver Work метод для выполнения работы в компаниях через время
func WorkCompanyObserver(ctx context.Context, companyIsolation *Isolation.Isolation) {

	go doWork1Hour(ctx, companyIsolation)
}

// каждый час
func doWork1Hour(ctx context.Context, companyIsolation *Isolation.Isolation) {

	for {

		select {

		case <-time.After(time.Hour):

			companyIsolation.SessionStorage.DeleteUnusedSessions()
		case <-ctx.Done():

			log.Infof("Закрыли обсервер таймера для компании %d", companyIsolation.GetCompanyId())
			return

		}
	}
}
