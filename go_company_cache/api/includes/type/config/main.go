package config

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company_cache/api/includes/type/db/company_data"
)

// GetList получаем информацию о нескольких записях конфига из кэша
func (store *Storage) GetList(ctx context.Context, companyDataConn *company_data.DbConn, keyList []string) ([]*company_data.KeyRow, []string) {

	var keyListStruct []*company_data.KeyRow

	// делим записи конфига на тех которых надо получить из базы и получили из кэша
	needFetchKeyList, keyListStruct := store.splitKeyList(keyList)
	subChannelList := store.doSubKeyListOnChanList(ctx, needFetchKeyList, companyDataConn)
	notFoundKeyList := make([]string, 0)
	for k, v := range subChannelList {

		err := waitUntilKeyAddedToCache(v, k)
		if err != nil {

			notFoundKeyList = append(notFoundKeyList, k)
			continue
		}

		keyInfo, isExist := store.mainStorage.getKeyItemFromCache(k)
		if !isExist {

			log.Errorf("Не получили ключ %d из кэша после ожидания канала", k)
			notFoundKeyList = append(notFoundKeyList, k)
			continue
		}

		keyListStruct = append(keyListStruct, keyInfo.keyRow)
	}
	return keyListStruct, notFoundKeyList
}

// GetOne получаем информацию об одном значении конфига из кеша
func (store *Storage) GetOne(ctx context.Context, companyDataConn *company_data.DbConn, key string) (*company_data.KeyRow, bool) {

	// пытаемся получить запись из кэша
	keyItem, exist := store.mainStorage.getKeyItemFromCache(key)

	// если нашли запись в кэше, просто отдаем
	if exist {

		return keyItem.keyRow, exist
	}

	// подписываем значение конфига на канал и ждем, пока получим его с базы
	subChannel := store.doSubKeyOnChan(ctx, key, companyDataConn)

	_ = waitUntilKeyAddedToCache(subChannel, key)

	keyItem, exist = store.mainStorage.getKeyItemFromCache(key)
	if !exist {

		log.Errorf("Не получили значение конфига %s из кэша после ожидания канала", key)
		return nil, false
	}

	return keyItem.keyRow, exist
}

// Делим записи конфига на тех которых надо получить из базы и получили из кэша
func (store *Storage) splitKeyList(keyList []string) ([]string, []*company_data.KeyRow) {

	needFetchKeyList := make([]string, 0)
	var keyListStruct []*company_data.KeyRow
	for _, key := range keyList {

		keyItem, exist := store.mainStorage.getKeyItemFromCache(key)
		if !exist {

			needFetchKeyList = append(needFetchKeyList, key)
			continue
		}

		keyListStruct = append(keyListStruct, keyItem.keyRow)
	}

	return needFetchKeyList, keyListStruct
}

// DeleteKeyFromCache удаляем из кэша по key
func (store *Storage) DeleteKeyFromCache(key string) {

	store.mainStorage.deleteFromStore(key)
}

// ClearCache очистить кэш конфига
func (store *Storage) ClearCache() {

	store.mainStorage.clear()
}
