package proxy

import (
	"go_api_gateway/internal/analytics"
	"net"
	"net/http"
	"sync/atomic"
	"time"
)

// трекер соединений
type ConnTracker struct {
	numOpen int64
}

// InitConnTracker инициализировать трекер соединений
func InitConnTracker() *ConnTracker {

	tracker := &ConnTracker{}

	// запускаем мониторинг соединений
	go monitorConn(tracker)

	return tracker
}

// функция, запускаемая при изменении статуса соединения
func (ct *ConnTracker) OnStateChange(conn net.Conn, state http.ConnState) {
	switch state {
	case http.StateNew:
		atomic.AddInt64(&ct.numOpen, 1)
	case http.StateClosed, http.StateHijacked:
		atomic.AddInt64(&ct.numOpen, -1)
	}
}

// получить количество активных соединений
func (ct *ConnTracker) GetCount() int64 {
	return atomic.LoadInt64(&ct.numOpen)
}

// мониторинг соединений
func monitorConn(ct *ConnTracker) {

	t := time.NewTicker(5 * time.Second)
	defer t.Stop()

	for range t.C {
		analytics.HttpConnectionsCount.Set(float64(ct.GetCount()))
	}

}
