package userbot

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"time"
)

// получаем информацию о боте из кэша
func GetOne(token string) (map[string]string, bool) {

	// пытаемся получить бота из кэша
	userbotItem, exist := store.getUserbotItemFromCache(token)

	// если нашли бота в кэше, просто его возвращаем
	if exist {
		return userbotItem.userbotRow, exist
	}

	// подписываем бота на канал и ждем, пока получим его с базы
	subChannel := doSubUserOnChan(token)

	_ = waitUntilUserbotAddedToCache(subChannel, token)

	userbotItem, exist = store.getUserbotItemFromCache(token)
	if !exist {

		log.Errorf("Не получили бота %s из кэша после ожидания канала", token)
		return nil, false
	}

	return userbotItem.userbotRow, exist
}

// получаем ждем пока в кэше появится инфа
func waitUntilUserbotAddedToCache(sub chan bool, userbotId string) error {

	select {
	case <-sub:

		return nil

		// добавляем timeout для прослушки
	case <-time.After(time.Millisecond * 1500):

		err := fmt.Errorf("не смогли получить из канала: %v для userbotId: %s за 2 секунды", sub, userbotId)
		log.Errorf("%v", err)
		return err
	}
}

// очищаем кэш по токену
func ClearFromCache(token string) {

	store.deleteFromStore(token)
}
