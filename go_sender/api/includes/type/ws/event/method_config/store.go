package method_config

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/antispam"
	"sync"
)

/**
 * Файл пакета содежит логику по рабое с хранилищем конфигов с версиями событий, которые поддерживают клиентские приложения.
 * Каждая запись в хранилище – конфиг с версиями для конкретной версии и платформы приложения
 */

// хранилище конфигов
type ConfigStore struct {

	// словарь [configHash] => config
	store map[string]ConfigStruct

	// светофор
	mx sync.RWMutex
}

// переменная с хранилищем конфигов
var configStore *ConfigStore

// инициализируем хранилище конфигов с методами
func Init() {

	// инициализируем хранилище
	configStore = &ConfigStore{
		store: make(map[string]ConfigStruct),
		mx:    sync.RWMutex{},
	}
}

// сохраняем конфиг в хранилище
func SaveConfig(userID int64, hash string, config ConfigStruct) {

	// проверяем, что пользователь не достиг лимита
	err := antispam.IncByUserId(userID, antispam.LimitSaveWsMethodConfig)
	if err != nil {

		log.Errorf("Пользователь (user_id: %d) достиг лимита в сохранении конфига в хранилище", userID)
		return
	}

	configStore.mx.Lock()
	defer configStore.mx.Unlock()

	// если такой конфиг уже существует
	if _, exist := configStore.store[hash]; exist {

		// ничего не делаем, даже не перезаписываем
		return
	}

	// сохраняем
	configStore.store[hash] = config
}

// получаем конфиг из хранилища
func GetConfig(hash string) (ConfigStruct, bool) {

	configStore.mx.RLock()
	defer configStore.mx.RUnlock()

	// достаем из хранилища
	config, exist := configStore.store[hash]

	return config, exist
}

// проверяем существование конфига в хранилище по хэшу
func IsConfigExist(hash string) bool {

	_, exist := GetConfig(hash)
	return exist
}

// поддерживается ли версия события
func IsEventVersionSupported(hash string, eventName string, eventVersion int) (bool, bool) {

	configStore.mx.RLock()
	defer configStore.mx.RUnlock()

	// достаем из хранилища
	config, exist := configStore.store[hash]
	if !exist {
		return false, false
	}

	return config.isEventVersionSupported(eventName, eventVersion)
}
