package observer

import (
	"go_pivot_cache/api/includes/type/session"
	"sync/atomic"
	"time"
)

var (
	is1HourWorker   atomic.Value
	is1MinuteWorker atomic.Value
)

// метод для выполнения работы через время
func Work() {

	go doWork1Hour()
	go doWork1Minute()
}

// каждый час
func doWork1Hour() {

	if is1HourWorker.Load() != nil && is1HourWorker.Load().(bool) {
		return
	}
	is1HourWorker.Store(true)

	for {

		// задержка 1 час
		time.Sleep(time.Hour)

		session.DeleteUnusedSessions()
	}
}

// каждую минуту
func doWork1Minute() {

	if is1MinuteWorker.Load() != nil && is1MinuteWorker.Load().(bool) {
		return
	}
	is1MinuteWorker.Store(true)

	for {

		// задержка 1 минута
		time.Sleep(time.Minute)

		session.UpdateLastOnlineAt()
	}
}
