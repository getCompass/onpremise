package observer

import (
	socketAuthKey "go_pusher/api/includes/type/socket/auth"
	"time"
)

// запускаем observer который работает с задачами по обновлению бейджа
func goClearUnusedPivotSocketKey() {

	if isCleanerUnusedPivotSocketKeyWork.Load() != nil && isCleanerUnusedPivotSocketKeyWork.Load().(bool) {
		return
	}
	isCleanerUnusedPivotSocketKeyWork.Store(true)

	for {

		socketAuthKey.DeleteUnusedKey()

		// спим
		time.Sleep(cleanerGoroutineInterval)
	}
}
