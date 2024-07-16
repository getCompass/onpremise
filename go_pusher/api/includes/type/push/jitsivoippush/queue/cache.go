package jitsiVoipPushQueue

import (
	"container/list"
	"go_pusher/api/includes/type/push"
	"sync"
)

// -------------------------------------------------------
// данный файл содержит всю логику хранения задач на отправку воип пуша
// -------------------------------------------------------

type VoIPJitsiStruct struct {
	UserId         int64               `json:"user_id"`
	Uuid           string              `json:"uuid"`
	PushData       push.PushDataStruct `json:"push_data"`
	TimeToLive     int64               `json:"time_to_live"`
	SentDeviceList []string            `json:"sent_device_list"`
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
func AddTask(voipPush VoIPJitsiStruct) {

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
