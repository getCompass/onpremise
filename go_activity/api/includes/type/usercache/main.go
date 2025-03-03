package usercache

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_activity/api/conf"
	"time"
)

// UserActivityStruct структура для ответа в методе user.getActivity
type UserActivityStruct struct {
	UserId       int64  `json:"user_id"`
	SessionUniq  string `json:"session_uniq"`
	Status       int32  `json:"status"`
	CreatedAt    int64  `json:"created_at"`
	UpdatedAt    int64  `json:"updated_at"`
	LastPingWsAt int64  `json:"last_ws_ping_at"`
}

// UserActivityListStruct структура для ответа с массивом активностей пользователей
type UserActivityListStruct struct {
	ActivityList []UserActivityStruct `json:"activity_list"`
}

// ---------------------------------------------
// PUBLIC
// ---------------------------------------------

// GetActivityRow получение активности пользователя
func GetActivityRow(ctx context.Context, userId int64) (UserActivityData, error) {

	// получаем данные из основного хранилища
	userActivity, exist := mainUserStore.getUserFromCache(userId)
	if !exist || userActivity.err != nil {

		// подписываем на канал и ждем пока пользователь добавится в кэш
		err := waitUntilUserAddedToCache(ctx, userId)
		if err != nil {

			log.Errorf("Ошибка при ожидании пользователя %d в кэше: %v", userId, err)
			return UserActivityData{}, err
		}

		// если так и не появилась
		userActivity, exist = mainUserStore.getUserFromCache(userId)
		if !exist {
			return UserActivityData{}, fmt.Errorf("не смогли получить данные для userId: %d", userId)
		}
	}

	// проверяем, что данные пользователя не пустые
	if userActivity.userActivityData == (UserActivityData{}) {
		return UserActivityData{}, fmt.Errorf("данные пользователя %d отсутствуют", userId)
	}

	return userActivity.userActivityData, nil
}

// ждем пока в кэше появится информация
func waitUntilUserAddedToCache(ctx context.Context, userId int64) error {

	sub := doSubOnChan(ctx, userId)
	select {
	case <-sub:

		return nil

		// добавляем timeout
	case <-time.After(time.Millisecond * conf.GetConfig().GetUserTimeoutMs):

		return fmt.Errorf("не смогли получить из канала: %v для userId: %d", sub, userId)
	}
}

// UpdateActivityRow обновление записи активности в кеше пользователя
func UpdateActivityRow(userId int64, activityTimestamp int64) {

	// проверяем, есть ли запись в mainUserStore для данного userId
	userActivity, exist := mainUserStore.getUserFromCache(userId)
	if exist {

		// если запись существует, обновляем last_ws_ping_at
		userActivity.userActivityData.LastPingWsAt = activityTimestamp

		// перезаписываем обновленную запись в mainUserStore
		mainUserStore.doCacheUserItem(userId, userActivity.userActivityData, userActivity.err)
	}
}

// ResetCache сбрасываем кеш
func ResetCache() {

	mainUserStore.reset()
}
