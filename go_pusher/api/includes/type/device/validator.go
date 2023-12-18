package device

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"sync"
	"time"
)

var (
	// инициализируем объект с хранилищем
	invalidTokenCache = sync.Map{}
)

// функция для добавления некорректного токена в кэш
func AddInvalidToken(deviceId string, token string) {

	// получаем текущее значение из кэша
	value, exist := invalidTokenCache.Load(deviceId)

	// если для переданного ключа ничего не нашли
	if !exist {

		// добавляем некорректный токен
		invalidTokenCache.Store(deviceId, []string{token})
		return
	}

	// проверяем, если такой токен уже есть в кэше, то завершаем работу функции
	if functions.StringSliceContains(value.([]string), token) {
		return
	}

	// добавляем в имеющийся кэш
	value = append(value.([]string), token)
	invalidTokenCache.Store(deviceId, value)
}

// функция для удаления протухших токенов
func doWork(ctx context.Context) {

	for {

		// проходимся по всем не валидным токенам
		invalidTokenCache.Range(func(key, value interface{}) bool {

			// обновляем токены пользователя
			result := updateTokenList(ctx, key.(string), value.([]string))

			// если успешно обновили, то удаляем из кэша
			if result {

				log.Infof("Обновили токены для пользователя: %d, удалили: %d токенов", key.(string), len(value.([]string)))
				invalidTokenCache.Delete(key)
			}

			return true
		})

		time.Sleep(10 * time.Second)
	}
}
