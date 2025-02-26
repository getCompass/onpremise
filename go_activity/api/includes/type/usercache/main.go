package usercache

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_activity/api/conf"
	"strconv"
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
func GetActivityRow(ctx context.Context, userId int64) (map[string]string, error) {

	// получаем инфу из основного хранилища
	userActivity, exist := mainUserStore.getUserFromCache(userId)
	if !exist || userActivity.err != nil {

		// подписываем на канал и ждем пока пользователь добавится в кэш
		err := waitUntilUserAddedToCache(ctx, userId)
		if err != nil {

			log.Errorf("error: %v", err)
			return nil, err
		}

		// если так и не появилась
		userActivity, exist = mainUserStore.getUserFromCache(userId)
		if !exist {
			return nil, fmt.Errorf("не смогли получить данные для userId: %d", userId)
		}
	}

	// обработка случая когда пользователь не найден
	if len(userActivity.userRow) < 1 {
		return nil, fmt.Errorf("не смогли получить данные для userId: %d", userId)
	}

	return userActivity.userRow, nil
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

		// если запись существует, обновляем поле last_ws_ping_at
		userActivity.userRow["last_ws_ping_at"] = strconv.FormatInt(activityTimestamp, 10)

		// перезаписываем обновленную запись в mainUserStore
		mainUserStore.doCacheUserItem(userId, userActivity.userRow, userActivity.err)
	}
}

// ResetCache сбрасываем кеш
func ResetCache() {

	mainUserStore.reset()
}
