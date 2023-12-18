package session

// пакет, в который вынесена вся бизнес-логика группы методов session
import (
	"go_company_cache/api/includes/controller/member"
	"go_company_cache/api/includes/structures"
	"go_company_cache/api/includes/type/db/company_data"
	Isolation "go_company_cache/api/includes/type/isolation"
	"google.golang.org/grpc/status"
)

// получаем информацию по сессии
func GetInfo(isolation *Isolation.Isolation, sessionUniq string) (structures.SessionInfoStruct, error) {

	// получаем пользовательскую сессию и информацию о ней
	userSessionRow, userId, err := isolation.SessionStorage.GetUserSessionRow(isolation.Context, sessionUniq, isolation.CompanyDataConn)
	if err != nil {
		return structures.SessionInfoStruct{}, status.Error(500, "database error")
	}
	if userSessionRow == nil {
		return structures.SessionInfoStruct{}, status.Error(902, "session not found")
	}

	memberItem, exist := member.GetOne(isolation, userId)
	if !exist {
		return structures.SessionInfoStruct{}, status.Error(902, "member info not found")
	}

	sessionInfoItem := prepareSessionInfoStruct(userId, userSessionRow.UserAgent, userSessionRow.IpAddress, userSessionRow.Extra, memberItem)
	return sessionInfoItem, nil
}

// собираем объект SessionInfoStruct из полученных записей бд
func prepareSessionInfoStruct(userId int64, userAgent string, ipAddress string, extra string, member *company_data.MemberRow) structures.SessionInfoStruct {

	return structures.SessionInfoStruct{
		UserID:    userId,
		UserAgent: userAgent,
		IpAddress: ipAddress,
		Extra:     extra,
		Member:    member,
	}
}

// удалить все сессии пользователя по его userID
func DeleteByUserId(isolation *Isolation.Isolation, userId int64) {

	isolation.SessionStorage.DeleteUserSessionList(userId)
}

// получить сессии пользователя по его userID
func GetListByUserId(isolation *Isolation.Isolation, userId int64) []string {

	return isolation.SessionStorage.GetListByUserId(userId)
}

// удалить конкретную сессию из кэша
func DeleteBySessionUniq(isolation *Isolation.Isolation, sessionUniq string) {

	// удаляем сессию из кеша по sessionUniq
	isolation.SessionStorage.DeleteSessionItem(sessionUniq)
}

// очистить кэш сессий
func ClearCache(isolation *Isolation.Isolation) {

	isolation.SessionStorage.ClearCache()
}
