package observer

import (
	socketAuthKey "go_pusher/api/includes/type/socket/auth"
	"time"
)

// запускаем observer который работает с задачами по обновлению бейджа
func goDeleteInvalidTokens() {

	if isInvalidTokensWork.Load() != nil && isInvalidTokensWork.Load().(bool) {
		return
	}
	isInvalidTokensWork.Store(true)

	for {

		socketAuthKey.DeleteUnusedKey()

		// спим
		time.Sleep(invalidTokensGoroutineInterval)
	}
}
