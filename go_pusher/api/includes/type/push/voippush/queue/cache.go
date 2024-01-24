package voipPushQueue

import (
	"container/list"
	"go_pusher/api/includes/type/push"
	"go_pusher/api/includes/type/usernotification"
	"sync"
)

// -------------------------------------------------------
// данный файл содержит всю логику хранения задач на отправку воип пуша
// -------------------------------------------------------

type VoipPushStruct struct {
	UserNotification usernotification.UserNotificationStruct `json:"user_notification"`
	Uuid             string                                  `json:"uuid"`
	PushData         push.PushDataStruct                     `json:"push_data"`
	SentDeviceList   []string                                `json:"sent_device_list,omitempty"`
}

// структура переменной с хранилищем
type queueStorage struct {
	mu    sync.Mutex
	cache *list.List
}

// переменные хранилищ - всего два между которыми переключаются указатели
var (
	first = queueStorage{
		cache: list.New(),
	}
	second = queueStorage{
		cache: list.New(),
	}
)

// метод добавляет задачу
func AddTask(voipPush VoipPushStruct) {

	// берем указатель на кэш, так как указатель может измениться по ходу исполнения метода
	cache := cachePointer

	// добавляем идентификатор пользователя с его токенами
	cache.mu.Lock()
	cachePointer.cache.PushBack(voipPush)
	cache.mu.Unlock()
}

// получаем все записи из update кэша
func GetAllFromUpdateCache() *list.List {

	// обязательно обычный лок, так как нам нужно чтобы кэш был полностью свободен и к нему точно никто не ждет доступа
	updatePointer.mu.Lock()
	cache := updatePointer.cache
	updatePointer.mu.Unlock()

	return cache
}

// чистим update кэш
func ClearUpdateCacheAndSwap() {

	// здесь блокировка не имеет особого смысл, но мы ее ставим, чтобы избежать всех непредвиденных случаев наверняка
	updatePointer.mu.Lock()
	updatePointer.cache = list.New()
	updatePointer.mu.Unlock()

	swapPointers()
}
