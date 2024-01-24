package antispam

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"sync"
	"time"
)

/**
 * Файл пакета для хранения всех лимитов
 */

// структура – как храним лимит в хранилище
type limitStorageStruct struct {
	count     int64
	startAt   int64
	expiresAt int64
}

// период (в секундах) с которым cleaner чистит хранилище
const cleanerPeriod = 60 * time.Second

// структура хранилища
type storageStruct struct {
	mu sync.Mutex

	// сама мапа с хранилищем
	store map[string]limitStorageStruct

	// флаг, запущен ли cleaner хранилища
	isCleanerRun bool
}

// переменная с хранилищем
var limitStorage = &storageStruct{
	store:        make(map[string]limitStorageStruct),
	isCleanerRun: false,
}

// инкрементим блокировку
func inc(storageKey string, limit limitStruct) error {

	limitStorage.mu.Lock()
	defer limitStorage.mu.Unlock()

	// проверяем, запущен ли cleaner
	// запускаем, если нужно
	if !limitStorage.isCleanerRun {

		go startStorageCleaner()
		limitStorage.isCleanerRun = true
	}

	// текущее время
	currentTime := functions.GetCurrentTimeStamp()

	// лезем в хранилище
	limitFromStorage, exist := limitStorage.store[storageKey]

	// если такой лимит ранее не фиксировался или существующий лимит уже истек
	if !exist || limitFromStorage.expiresAt <= currentTime {

		// создаем лимит
		limitStorage.store[storageKey] = limitStorageStruct{count: 1, startAt: currentTime, expiresAt: currentTime + limit.expire}

		// возвращаем, что все окей
		return nil
	}

	// если существующий в хранилище лимит превысил значение
	if limitFromStorage.count >= limit.maxCount {

		return fmt.Errorf("limit exceeded")
	}

	// инкрементим лимит
	limitFromStorage.count += 1
	limitStorage.store[storageKey] = limitFromStorage

	return nil
}

// запускаем cleaner хранилища
func startStorageCleaner() {

	ticker := time.NewTicker(cleanerPeriod)

	for {
		select {

		// если настало время, то чистим
		case <-ticker.C:
			clearStorage()
		}
	}
}

// очистка хранилища
func clearStorage() {

	log.Info("Начинаем чистить antispam хранилище")

	// получаем текущее время
	currentTime := functions.GetCurrentTimeStamp()

	limitStorage.mu.Lock()

	// пробегаемся по хранилищу и удаляем протухшие
	for storageKey, limit := range limitStorage.store {

		// если протух
		if limit.expiresAt < currentTime {

			delete(limitStorage.store, storageKey)

			log.Infof("Удалил лимит из хранилища по ключу: %s", storageKey)
		}
	}

	limitStorage.mu.Unlock()

	log.Info("Закончили чистить antispam хранилище")
}
