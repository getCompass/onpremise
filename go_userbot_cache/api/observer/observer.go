package observer

import (
	"sync/atomic"
	"time"
)

var (
	_isSecondWorker atomic.Value
)

// метод для выполнения работы через время
func Work() {

	go _doWorkSecond()
}

// каждую секунду
func _doWorkSecond() {

	if _isSecondWorker.Load() != nil && _isSecondWorker.Load().(bool) == true {
		return
	}
	_isSecondWorker.Store(true)

	for {

		time.Sleep(time.Second * 1)
	}
}
