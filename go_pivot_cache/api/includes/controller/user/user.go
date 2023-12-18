package user

// пакет, в который вынесена вся бизнес-логика группы методов user
import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_pivot_cache/api/includes/structures"
	"go_pivot_cache/api/includes/type/usercache"
	errorStatus "google.golang.org/grpc/status"
)

// получаем информацию о пользователе
func GetInfo(userId int64) (structures.UserInfoStruct, error) {

	// проверяем пришедшие данные
	if userId < 1 {
		return structures.UserInfoStruct{}, errorStatus.Error(401, "passed bad user_id")
	}

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// если сессия авторизованна, то получаем дополнительные данные к ответу из таблицы пользователей
	userRow, err := usercache.GetUserInfoRow(ctx, userId)
	if err != nil {

		log.Errorf("Не получили пользователя %d из кэша после ожидания канала", userId)
		return structures.UserInfoStruct{}, errorStatus.Error(901, "user row not found")
	}

	userInfoItem := prepareUserInfoStruct(userId, userRow)
	return userInfoItem, nil
}

// получаем информацию о списке пользователей
func GetListInfo(userIdList []int64) (structures.UserInfoListStruct, error) {

	userInfoList := make([]structures.UserInfoStruct, 0)

	//проверяем валидность id-шников и удаляем некорректные
	for key, userId := range userIdList {
		if userId < 1 {
			userIdList[key] = userIdList[len(userIdList)-1]
			userIdList = userIdList[:len(userIdList)-1]
		}
	}

	//если список пуст, отдаем ошибку
	if len(userIdList) == 0 {

		log.Errorf("Передали пустой список пользователей - GetListInfo")
		return structures.UserInfoListStruct{}, errorStatus.Error(401, "passed bad user_ids")
	}

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// если сессия авторизованна, то получаем дополнительные данные к ответу из таблицы пользователей
	userRows, err := usercache.GetUserInfoRows(ctx, userIdList)
	if err != nil || len(userRows) == 0 {

		log.Errorf("Пустой список пользователей - GetListInfo")
		return structures.UserInfoListStruct{}, errorStatus.Error(903, "empty user list")
	}

	for _, userRow := range userRows {
		userId := functions.StringToInt64(userRow["user_id"])
		userInfoList = append(userInfoList, prepareUserInfoStruct(userId, userRow))
	}

	userInfoListStruct := prepareUserListInfoStruct(userInfoList)

	return userInfoListStruct, nil

}

// собираем объект UserInfoStruct из полученных записей бд
func prepareUserInfoStruct(userId int64, userRow map[string]string) structures.UserInfoStruct {

	return structures.UserInfoStruct{
		UserId:               userId,
		NpcType:              functions.StringToInt32(userRow["npc_type"]),
		InvitedByPartnerId:   functions.StringToInt64(userRow["invited_by_partner_id"]),
		LastActiveDayStartAt: functions.StringToInt64(userRow["last_active_day_start_at"]),
		InvitedByUserId:      functions.StringToInt64(userRow["invited_by_user_id"]),
		CreatedAt:            functions.StringToInt64(userRow["created_at"]),
		UpdatedAt:            functions.StringToInt64(userRow["updated_at"]),
		FullNameUpdatedAt:    functions.StringToInt64(userRow["full_name_updated_at"]),
		CountryCode:          userRow["country_code"],
		FullName:             userRow["full_name"],
		AvatarFileMap:        userRow["avatar_file_map"],
		Extra:                userRow["extra"],
	}
}

// составляем объект UserListInfoStruct из массива объектов UserInfoStruct
func prepareUserListInfoStruct(userList []structures.UserInfoStruct) structures.UserInfoListStruct {

	return structures.UserInfoListStruct{
		UserList: userList,
	}
}

// сбрасывает кэш пользователей
func ResetCache() {

	usercache.ResetCache()
}
