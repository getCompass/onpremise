package observer

import (
	"go_pusher/api/includes/type/device"
	"go_pusher/api/includes/type/gwsolution"
	"time"
)

// запускаем observer который работает с задачей по инвалидированию токенов
func goDeleteInvalidTokens() {

	if isInvalidTokensWork.Load() != nil && isInvalidTokensWork.Load().(bool) {
		return
	}
	isInvalidTokensWork.Store(true)

	for {

		invalidTokens := gwsolution.GetInvalidTokens()

		for _, v := range invalidTokens {
			device.AddInvalidToken(v.Device, v.Token)
		}

		// спим
		time.Sleep(invalidTokensGoroutineInterval)
	}
}
