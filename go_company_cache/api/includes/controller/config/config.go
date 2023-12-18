package config

// пакет, в который вынесена вся бизнес-логика группы методов config
import (
	"go_company_cache/api/includes/type/db/company_data"
	Isolation "go_company_cache/api/includes/type/isolation"
)

// GetList получаем массив записей конфига по ключу
func GetList(isolation *Isolation.Isolation, keyList []string) ([]*company_data.KeyRow, []string) {

	return isolation.ConfigStore.GetList(isolation.Context, isolation.CompanyDataConn, keyList)
}

// GetOne получаем значение по ключу
func GetOne(isolation *Isolation.Isolation, key string) (*company_data.KeyRow, bool) {

	return isolation.ConfigStore.GetOne(isolation.Context, isolation.CompanyDataConn, key)
}

// DeleteFromCacheByKey удалить запить из кэша по его ключу
func DeleteFromCacheByKey(isolation *Isolation.Isolation, key string) {

	isolation.ConfigStore.DeleteKeyFromCache(key)
}

// ClearCache очистить конфиг компании
func ClearCache(isolation *Isolation.Isolation) {

	isolation.ConfigStore.ClearCache()
}
