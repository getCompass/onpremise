package token

import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"sync"
)

// токен
type tokenStruct struct {
	Token    string
	UserID   int64
	Expire   int64
	Platform string
	DeviceId string
}

type Store struct {
	store map[string]*tokenStruct
	mx    sync.Mutex
}

func MakeStore() *Store {

	return &Store{
		store: make(map[string]*tokenStruct),
		mx:    sync.Mutex{},
	}
}

// получаем дополнительную информацию о соединении, если оно прислало верный токен
func (store *Store) GetConnectionInfoIfTokenValid(TokenID string, UserID int64) (platform string, deviceId string, isValid bool) {

	// получаем объект с токеном
	store.mx.Lock()
	defer store.mx.Unlock()
	token, exist := store.store[TokenID]
	if !exist {
		return "", "", false
	}

	// если токен принадлежит не нашему пользователю
	if token.UserID != UserID {

		log.Infof("Not %s user id %d != %d", TokenID, token.UserID, UserID)
		return "", "", false
	}

	// если токен протух
	if token.Expire < functions.GetCurrentTimeStamp() {
		return "", "", false
	}

	// удаляем токен
	delete(store.store, TokenID)

	return token.Platform, token.DeviceId, true
}

// добавляем токен в хранилище
func (store *Store) AddToken(tokenID string, userID int64, expire int64, platform string, deviceId string) {

	// создаем объект для токена
	tokenItem := &tokenStruct{
		Token:    tokenID,
		UserID:   userID,
		Expire:   expire,
		Platform: platform,
		DeviceId: deviceId,
	}

	// пишем в хранилище
	store.mx.Lock()
	store.store[tokenID] = tokenItem
	store.mx.Unlock()
}

// очищаем старые токены
func (store *Store) ClearOldToken() {

	store.mx.Lock()
	defer store.mx.Unlock()

	for k, v := range store.store {

		// если время хранения токена истекло то удаляем его
		if v.Expire < functions.GetCurrentTimeStamp() {
			delete(store.store, k)
		}

	}
}
