package observer

import (
	"container/list"
	"context"
	"go_pusher/api/includes/controller/pusher"
	"go_pusher/api/includes/type/push/textpush/queue"
	"time"
)

// запускаем observer который работает с задачами по отправке текстовых пушей
func goWorkTextPushWorker(ctx context.Context) {

	if isTextPushWork.Load() != nil && isTextPushWork.Load().(bool) {
		return
	}
	isTextPushWork.Store(true)

	textPushQueue.InitVariables()
	for {

		// получаем кэш
		cache := textPushQueue.GetAllFromUpdateCache()

		// работаем с задачами по обновлению бейджа пользователям и потом его чистим и меняем
		workWithTextPushCache(ctx, cache)
		textPushQueue.ClearUpdateCacheAndSwap()

		// спим
		time.Sleep(textPushGoroutineInterval)
	}
}

// функция работает с задачами по обновлению бейджа пользователям
func workWithTextPushCache(ctx context.Context, cache *list.List) {

	for cache.Len() > 0 {

		cacheItem := cache.Front()
		textPush := cacheItem.Value.(textPushQueue.TextPushStruct)

		pusher.SendPush(ctx, textPush.Uuid, textPush.UserNotificationList, textPush.PushData)

		cache.Remove(cacheItem)
	}

}
