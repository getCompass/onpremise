package Isolation

/** Пакет описывает сущность изоляции исполнения внутри модуля
  Изоляция может быть связана с компанией или быть глобальной для сервиса **/

/** ЭТОТ ПАКЕТ НЕ МОЖЕТ ПОДКЛЮЧАТЬ ДРУГИЕ ПАКЕТЫ КАК ЗАВИСИМОСТИ,
  ИНАЧЕ ЭТО ВЫЗЫВАЕТ ПОРОЧНЫЙ КРУГ ЦИКЛИЧЕСКИХ ИМПОРТОВ **/

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"sync"
)

// хранилище с числом меток
var watcher = map[string]int{}
var watcherMx = sync.Mutex{}

// Inc инкрементит счетчик ключей
func Inc(key string) {

	watcherMx.Lock()
	defer watcherMx.Unlock()

	if _, exists := watcher[key]; !exists {
		watcher[key] = 0
	}

	watcher[key] = watcher[key] + 1
	log.Warningf("inc %s: %d", key, watcher[key])
}

// Dec инкрементит счетчик ключей
func Dec(key string) {

	watcherMx.Lock()
	defer watcherMx.Unlock()

	if val, exists := watcher[key]; !exists {

		log.Errorf("attempt to decrease non-existing key %s", key)
		return
	} else if val == 0 {

		log.Errorf("attempt to decrease zero-value key %s", key)
		return
	}

	watcher[key] = watcher[key] - 1
	log.Warningf("dec %s: %d", key, watcher[key])
}
