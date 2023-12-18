package gatewayPhpPivot

import (
	"github.com/getCompassUtils/go_base_frame"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_company/api/includes/type/socket"
	socketAuthKey "go_company/api/includes/type/socket/auth"
	"go_company/api/includes/type/structures"
)

// UpdateBadge обновляем бадж пользователя
func UpdateBadge(messagesUnreadCount int, inboxUnreadCount int, taskList []string, userId int64, companyId int64, socketKey string) {

	request := structures.RequestUpdateBadgeDataStruct{
		MessagesUnreadCount: messagesUnreadCount,
		InboxUnreadCount:    inboxUnreadCount,
		TaskList:            taskList,
	}

	jsonParams, err := go_base_frame.Json.Marshal(request)

	signature := socketAuthKey.GetPivotSignature(socketKey, jsonParams)

	response, err := socket.DoCall("php_pivot", "company.notifications.updateBadgeCount", signature, string(jsonParams), userId, companyId)
	if err != nil || response.Status != "ok" {
		log.Errorf("Не смогли выполнить запрос %v", err)
	}
}
