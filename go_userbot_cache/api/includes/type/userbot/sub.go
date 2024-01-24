package userbot

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_userbot_cache/api/includes/type/socket"
	"sync"
)

// структура хранилища с подписчиками
type subStorage struct {
	mu    sync.RWMutex
	cache map[string]chan bool
}

// инициализируем кэш подписчиков
var userbotSubStore = subStorage{
	cache: make(map[string]chan bool),
}

// функция для подписки на канал бота
func doSubUserOnChan(token string) chan bool {

	// если уже существует то добавляем в массив канал иначем созадем
	userbotSubStore.mu.Lock()
	subChannel, isExist := userbotSubStore.cache[token]
	if isExist {

		userbotSubStore.mu.Unlock()
		return subChannel
	}

	// создаем канал
	subChannel = make(chan bool, 1)
	userbotSubStore.cache[token] = subChannel
	userbotSubStore.mu.Unlock()

	go addUserbotToMainStore(token)
	return subChannel
}

// добавляем одного бота в основное хранилище
func addUserbotToMainStore(token string) {

	userbot, err := socket.GetUserbotInfo(token)

	defer closeChannel(token)

	if err != nil {

		log.Errorf("Userbot not found %d", token)
		return
	}

	userbotRow := make(map[string]string)
	userbotRow["userbot_id"] = userbot.Response.UserbotId
	userbotRow["status"] = functions.IntToString(userbot.Response.Status)
	userbotRow["domino_entrypoint"] = userbot.Response.DominoEntrypoint
	userbotRow["company_url"] = userbot.Response.CompanyUrl
	userbotRow["company_id"] = functions.Int64ToString(userbot.Response.CompanyId)
	userbotRow["secret_key"] = userbot.Response.SecretKey
	userbotRow["is_react_command"] = functions.IntToString(userbot.Response.IsReactCommand)
	userbotRow["userbot_user_id"] = functions.IntToString(userbot.Response.UserbotUserId)
	userbotRow["extra"] = userbot.Response.Extra

	store.doCacheUserbotItem(token, userbotRow, err)
}

// закрываем канал для юзера
func closeChannel(token string) {

	userbotSubStore.mu.Lock()
	defer userbotSubStore.mu.Unlock()

	// достаем канал из кэша и закрываем его
	subChannel, exist := userbotSubStore.cache[token]
	if !exist {
		return
	}

	close(subChannel)
	delete(userbotSubStore.cache, token)
}
