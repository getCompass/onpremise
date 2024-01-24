package observer

import (
	"context"
	CompanyEnvironment "go_event/api/includes/type/company_config"
	"sync/atomic"
	"time"
)

var (
	isSecondWorker atomic.Value
)

// метод для выполнения работы через время
func Work(ctx context.Context) {

	go doWorkInfinite(ctx)
}

// каждую секунду
func doWorkInfinite(ctx context.Context) {

	if isSecondWorker.Load() != nil && isSecondWorker.Load().(bool) == true {
		return
	}

	isSecondWorker.Store(true)

	for {

		if ctx.Err() != nil {
			break
		}

		// нон-стоп обновляем конф
		CompanyEnvironment.UpdateWorldConfig()
		time.Sleep(100 * time.Millisecond)
	}
}
