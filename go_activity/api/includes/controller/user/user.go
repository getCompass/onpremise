package user

// пакет, в который вынесена вся бизнес-логика группы методов user
import (
	"context"
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

	userActivityData, err := usercache.GetActivityRow(ctx, userId)
	if err != nil {

		log.Errorf("Не получили пользователя %d из кэша после ожидания канала", userId)
		return usercache.UserActivityStruct{}, errorStatus.Error(901, "user row not found")
	}

	userActivityItem := prepareUserActivityStruct(userId, userActivityData)
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
		userActivityData, err := usercache.GetActivityRow(ctx, userId)
		if err != nil {
			userActivityData = usercache.UserActivityData{}
		}

		// собираем структуру активности пользователя
		userActivityItem := prepareUserActivityStruct(userId, userActivityData)
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
func prepareUserActivityStruct(userId int64, userActivityData usercache.UserActivityData) usercache.UserActivityStruct {

	return usercache.UserActivityStruct{
		UserId:       userId,
		Status:       userActivityData.Status,
		CreatedAt:    userActivityData.CreatedAt,
		UpdatedAt:    userActivityData.UpdatedAt,
		LastPingWsAt: userActivityData.LastPingWsAt,
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
