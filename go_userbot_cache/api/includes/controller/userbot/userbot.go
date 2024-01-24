package userbot

// пакет, в который вынесена вся бизнес-логика группы методов member
import (
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_userbot_cache/api/includes/structures"
	"go_userbot_cache/api/includes/type/userbot"
)

// GetOne получаем информацию о бота
func GetOne(token string) (structures.UserbotInfoStruct, bool) {

	// получаем бота и информацию о нем
	userbotRow, exist := userbot.GetOne(token)

	userbotInfoItem := prepareUserInfoStruct(token, userbotRow)

	return userbotInfoItem, exist
}

// собираем объект UserInfoStruct из полученных записей бд
func prepareUserInfoStruct(token string, userbotRow map[string]string) structures.UserbotInfoStruct {

	return structures.UserbotInfoStruct{
		UserbotId:        userbotRow["userbot_id"],
		Token:            token,
		Status:           functions.StringToInt64(userbotRow["status"]),
		CompanyId:        functions.StringToInt64(userbotRow["company_id"]),
		DominoEntrypoint: userbotRow["domino_entrypoint"],
		CompanyUrl:       userbotRow["company_url"],
		SecretKey:        userbotRow["secret_key"],
		IsReactCommand:   functions.StringToInt64(userbotRow["is_react_command"]),
		UserbotUserId:    functions.StringToInt64(userbotRow["userbot_user_id"]),
		Extra:            userbotRow["extra"],
	}
}

// очищаем из кэша по токену
func ClearFromCacheByToken(token string) {

	userbot.ClearFromCache(token)
}
