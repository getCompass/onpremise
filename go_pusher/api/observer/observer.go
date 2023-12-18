package observer

import (
	"context"
	"go_pusher/api/conf"
	"sync"
	"sync/atomic"
	"time"
)

// оставил закомменченный пример, чтобы было проще потом и не искать как выглядит ;)
// var _is1DayWork atomic.Value

var (
	textPushGoroutineInterval         = time.Millisecond * 500
	voipPushGoroutineInterval         = time.Millisecond * 500
	cleanerGoroutineInterval          = time.Minute * 5
	analyticsGoroutineInterval        = time.Millisecond * 500
	invalidTokensGoroutineInterval    = time.Minute * 10
	isInvalidTokensWork               atomic.Value
	isTextPushWork                    atomic.Value
	isVoipPushWork                    atomic.Value
	isCleanerUnusedPivotSocketKeyWork atomic.Value
	isAnalyticsObserverWork           atomic.Value
)

// метод для выполнения работы через время
func Work(ctx context.Context) {

	if conf.GetTestConfig().IsPushMockEnable {

		textPushGoroutineInterval = time.Millisecond * 50
		voipPushGoroutineInterval = time.Millisecond * 50
		cleanerGoroutineInterval = time.Minute * 60
	}

	// конструкция с waitgroup нужна здесь, чтобы дождаться запуска горутин ниже
	// поскольку запуск горутин может занимать какое-то время
	wg := sync.WaitGroup{}

	// число задаваемое здесь равняется кол-ву функций, запускаемых ниже
	// выглядит не гибко, но больше идей нет :c
	wg.Add(4)

	go goWorkFunc(&wg, goClearUnusedPivotSocketKey)
	go goWorkFuncWithContext(&wg, ctx, goWorkTextPushWorker)
	go goWorkFuncWithContext(&wg, ctx, goWorkVoipPushWorker)
	go goWorkFunc(&wg, goWorkAnalyticsObserver)

	wg.Wait()
}

// запускаем функцию
func goWorkFunc(wg *sync.WaitGroup, startFunc func()) {

	wg.Done()
	startFunc()
}

// запускаем функцию с контекстом
func goWorkFuncWithContext(wg *sync.WaitGroup, ctx context.Context, startFunc func(ctx context.Context)) {

	wg.Done()
	startFunc(ctx)
}
