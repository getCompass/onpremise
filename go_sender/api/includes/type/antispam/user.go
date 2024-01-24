package antispam

/**
 * основной файл пакета, для лимитов по пользователю
 */

import "fmt"

// считаем блокировку по пользователю, метод ошибку, если поймали лимит
func IncByUserId(userID int64, key string) error {

	// проверяем существование лимита по ключу
	limit, exist := limitStructMapping[key]
	if !exist {
		panic(fmt.Sprintf("limit (%s) not found", key))
	}

	// подготавливаем ключ для хранилища
	storageKey := prepareStorageKeyForUser(userID, key)

	return inc(storageKey, limit)
}

// подготавливаем ключ для хранилища по user_id
func prepareStorageKeyForUser(userID int64, key string) string {

	return fmt.Sprintf("user_%d_key_%s", userID, key)
}
