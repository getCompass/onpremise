package user

// пакет, в который вынесена вся бизнес-логика группы методов user
import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_activity/api/includes/type/activitycache"
	"go_activity/api/includes/type/usercache"
	errorStatus "google.golang.org/grpc/status"
)

// GetActivity получаем информацию об активности пользователя
func GetActivity(ctx context.Context, userId int64) (usercache.UserActivityStruct, error) {

	// проверяем пришедшие данные
	if userId < 1 {
		return usercache.UserActivityStruct{}, errorStatus.Error(401, "passed bad user_id")
	}

	userRow, err := usercache.GetActivityRow(ctx, userId)
	if err != nil {

		log.Errorf("Не получили пользователя %d из кэша после ожидания канала", userId)
		return usercache.UserActivityStruct{}, errorStatus.Error(901, "user row not found")
	}

	userActivityItem := prepareUserActivityStruct(userId, userRow)
	return userActivityItem, nil
}

// GetActivityList получает информацию об активности списка пользователей
func GetActivityList(ctx context.Context, userIdList map[string]int64) (*usercache.UserActivityListStruct, error) {

	// проверяем входные данные
	if len(userIdList) == 0 {
		return nil, errorStatus.Error(400, "empty user ID list")
	}

	var activities []usercache.UserActivityStruct

	// проходим по каждому ID пользователя
	for _, userId := range userIdList {
		if userId < 1 {
			continue // Пропускаем некорректные ID
		}

		// получаем информацию из кэша
		userRow, err := usercache.GetActivityRow(ctx, userId)
		if err != nil {

			userRow = map[string]string{
				"status":          "0",
				"created_at":      "0",
				"updated_at":      "0",
				"last_ws_ping_at": "0",
			}
		}

		// собираем структуру активности пользователя
		userActivityItem := prepareUserActivityStruct(userId, userRow)
		activities = append(activities, userActivityItem)
	}

	if len(activities) == 0 {
		return nil, errorStatus.Error(404, "no users' activities found")
	}

	// возвращаем результат
	return &usercache.UserActivityListStruct{
		ActivityList: activities,
	}, nil
}

// собираем объект UserActivityStruct из полученных записей бд
func prepareUserActivityStruct(userId int64, userRow map[string]string) usercache.UserActivityStruct {

	return usercache.UserActivityStruct{
		UserId:       userId,
		Status:       functions.StringToInt32(userRow["status"]),
		CreatedAt:    functions.StringToInt64(userRow["created_at"]),
		UpdatedAt:    functions.StringToInt64(userRow["updated_at"]),
		LastPingWsAt: functions.StringToInt64(userRow["last_ws_ping_at"]),
	}
}

// AddActivityList добавление списка активностей пользователей в кэш
func AddActivityList(users []usercache.UserActivityStruct) error {

	if len(users) == 0 {
		return errorStatus.Error(400, "empty users list")
	}

	for _, user := range users {

		// проверяем корректность данных
		if user.UserId < 1 || user.LastPingWsAt <= 0 {
			continue
		}

		// добавляем активность в кэш
		err := activitycache.AddActivity(user.UserId, user.LastPingWsAt, user.SessionUniq)
		if err != nil {
			log.Errorf("Ошибка при добавлении активности пользователя %d: %v", user.UserId, err)
			continue
		}

		// обновляем запись в кэше пользователя
		usercache.UpdateActivityRow(user.UserId, user.LastPingWsAt)
	}

	return nil
}

// ResetCache сбрасывает кэш
func ResetCache() {

	usercache.ResetCache()
}
