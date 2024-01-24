package member

// пакет, в который вынесена вся бизнес-логика группы методов member
import (
	"go_company_cache/api/includes/type/db/company_data"
	Isolation "go_company_cache/api/includes/type/isolation"
)

// GetList получаем информацию
func GetList(isolation *Isolation.Isolation, userIdList []int64) ([]*company_data.MemberRow, []int64) {

	// получаем пользователя и информацию о нем
	return isolation.MemberStore.GetList(isolation.Context, isolation.CompanyDataConn, userIdList)
}

// GetOne получаем информацию о пользователе
func GetOne(isolation *Isolation.Isolation, userId int64) (*company_data.MemberRow, bool) {

	// получаем пользователя и информацию о нем
	return isolation.MemberStore.GetOne(isolation.Context, isolation.CompanyDataConn, userId)
}

// DeleteFromCacheByUserId удалить пользователя из кэша по его userID
func DeleteFromCacheByUserId(isolation *Isolation.Isolation, userId int64) {

	isolation.MemberStore.DeleteMemberFromCache(userId)
}

// DeleteFromCacheByUserIdList удалить пользователей из кэша по их user_id
func DeleteFromCacheByUserIdList(isolation *Isolation.Isolation, userIdList []int64) {

	isolation.MemberStore.DeleteMemberListFromCache(userIdList)
}

// очистить кэш пользователей
func ClearCache(isolation *Isolation.Isolation) {

	isolation.MemberStore.ClearCache()
}
