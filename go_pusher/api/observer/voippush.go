package observer

import (
	"container/list"
	"context"
	"go_pusher/api/includes/controller/pusher"
	jitsiVoipPushQueue "go_pusher/api/includes/type/push/jitsivoippush/queue"
	voipPushQueue "go_pusher/api/includes/type/push/voippush/queue"
	"time"
)

// запускаем observer который работает с задачами по отправке воип пуша
func goWorkVoipPushWorker(ctx context.Context) {

	if isVoipPushWork.Load() != nil && isVoipPushWork.Load().(bool) {
		return
	}
	isVoipPushWork.Store(true)

	voipPushQueue.InitVariables()
	jitsiVoipPushQueue.InitVariables()
	for {

		// получаем кэш
		cache := voipPushQueue.GetAllFromUpdateCache()

		// работаем с задачами по отправке воип пуша пользователям и потом его чистим и меняем
		workWithVoipPushCache(ctx, cache)
		voipPushQueue.ClearUpdateCacheAndSwap()

		// работаем с задачами воип пуша Jitsi, и затем его чистим
		cache = jitsiVoipPushQueue.GetAllFromUpdateCache()
		workWithJitsiVoipPushCache(ctx, cache)
		jitsiVoipPushQueue.ClearUpdateCacheAndSwap()

		// спим
		time.Sleep(voipPushGoroutineInterval)
	}
}

// функция работает с задачами по отправке воип пуша
func workWithVoipPushCache(ctx context.Context, cache *list.List) {

	for cache.Len() > 0 {

		cacheItem := cache.Front()
		voipPush := cacheItem.Value.(voipPushQueue.VoipPushStruct)

		pusher.SendVoipPush(ctx, voipPush.UserNotification, voipPush.Uuid, voipPush.PushData, voipPush.SentDeviceList)

		cache.Remove(cacheItem)
	}
}

// функция работает с задачами по отправке воип пуша Jitsi
func workWithJitsiVoipPushCache(ctx context.Context, cache *list.List) {

	for cache.Len() > 0 {

		cacheItem := cache.Front()
		voipPush := cacheItem.Value.(jitsiVoipPushQueue.VoIPJitsiStruct)

		pusher.SendJitsiVoipPush(ctx, []int64{voipPush.UserId}, voipPush.Uuid, voipPush.PushData, voipPush.SentDeviceList)

		cache.Remove(cacheItem)
	}
}
