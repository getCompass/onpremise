package fileauthz

import (
	"fmt"
	"github.com/service/go_base_frame/api/system/log"
	"strings"
	"sync"
	"time"
)

// Пакет проверки авторизации доступа к файлам.
// Файл описывает кэширующие хранилища ранее авторизованных сессий.

// хранилище токенов авторизации
type сaDataCache struct {
	store map[string]time.Time
	mx    sync.RWMutex
}

// получает значение хранилища
func (caStorage *сaDataCache) get(key string) (time.Time, bool) {

	caStorage.mx.RLock()
	defer caStorage.mx.RUnlock()

	if value, ok := caStorage.store[key]; ok {

		log.Infof("got cached value")
		return value, true
	}

	log.Infof("cache value miss")
	return time.Unix(0, 0), false
}

// записывает значение в хранилище
func (caStorage *сaDataCache) set(key string, value time.Time) {

	caStorage.mx.Lock()
	defer caStorage.mx.Unlock()

	caStorage.store[key] = value
}

// рутина очистки кэша
func (caStorage *сaDataCache) routine() {

	for {

		caStorage.mx.Lock()
		currTime := time.Now()

		for index, value := range caStorage.store {

			if value.Before(currTime) {

				log.Infof("cache key invalidated")
				delete(caStorage.store, index)
			}
		}

		caStorage.mx.Unlock()
		time.Sleep(30 * time.Second)
	}
}

// хранилище точек входа для проверки сессий
type entrypointStorage struct {
	store   map[string]*сaDataCache
	trusted map[string]bool
	mx      sync.RWMutex
}

// создает экземпляр entrypointStorage
func makeEntrypointStorage(trustedString string) *entrypointStorage {

	trustedString = strings.TrimSpace(strings.Trim(trustedString, ","))
	trusted := map[string]bool{}

	for _, item := range strings.Split(trustedString, ",") {

		// чистим пробелы и фильтруем пустые строки
		if item = strings.TrimSpace(item); item == "" {
			continue
		}

		log.Infof("init trusted entrypoint %s", item)
		trusted[item] = true
	}

	return &entrypointStorage{store: map[string]*сaDataCache{}, trusted: trusted, mx: sync.RWMutex{}}
}

// получает значение хранилища
func (eStorage *entrypointStorage) get(key string) *сaDataCache {

	eStorage.mx.RLock()
	defer eStorage.mx.RUnlock()

	if value, ok := eStorage.store[key]; ok {
		return value
	}

	return nil
}

// записывает значение в хранилище
func (eStorage *entrypointStorage) set(key string, value *сaDataCache) error {

	trusted := false

	for item := range eStorage.trusted {

		if strings.HasPrefix(key, item) {

			trusted = true
			break
		}
	}

	if trusted == false {
		return fmt.Errorf("passed auth endpoint %q is not trusted", key)
	}

	eStorage.mx.Lock()
	defer eStorage.mx.Unlock()

	eStorage.store[key] = value
	go value.routine()

	return nil
}
