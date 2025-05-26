package gatewayGoActivity

import (
	"encoding/json"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_sender/api/includes/type/structures"
)

// UserPingDataWS структура для отправки данных пользователей через user.addActivityList
type UserPingDataWS struct {
	UserID       int64  `json:"user_id"`
	SessionUniq  string `json:"session_uniq"`
	LastPingWsAt int64  `json:"last_ping_ws_at"`
}

// SendPingBatching отправляет список активности пользователей батчингом
func SendPingBatching(data []structures.UserPingData) {

	// формируем список пользователей для отправки
	users := make([]UserPingDataWS, 0, len(data))
	for _, user := range data {
		users = append(users, UserPingDataWS{
			UserID:       user.UserID,
			SessionUniq:  user.SessionUniq,
			LastPingWsAt: user.LastPingTime,
		})
	}

	// формируем запрос
	request := struct {
		Users []UserPingDataWS `json:"users"`
	}{
		Users: users,
	}
	jsonRequest, _ := json.Marshal(request)

	// выполняем вызов
	err := doSendRequest(jsonRequest, "user.addActivityList")
	if err != nil {
		log.Errorf("Ошибка отправки данных для списка пользователей: %v", err)
	}
}
