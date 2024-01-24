package timer_worker

import (
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	gatewayPhpCompany "go_company/api/includes/gateway/php_company"
	gatewayPhpPivot "go_company/api/includes/gateway/php_pivot"
	Isolation "go_company/api/includes/type/isolation"
	"go_company/api/includes/type/timer"
)

// выполняем задачу
func DoWorkTask(isolation *Isolation.Isolation, taskData *timer.TaskItemStruct) {

	switch taskData.RequestName {

	// задача обновить бадж пользователя
	case "update_badge":

		err := onUpdateBadge(isolation, taskData.UserId, taskData.TaskList)
		if err != nil {
			log.Errorf("Не смогли обновить бадж %v", err)
		}
	default:
		return
	}
}

// делаем при обновлении баджа
func onUpdateBadge(isolation *Isolation.Isolation, userId int64, taskList []string) error {

	messagesUnreadCount := 0
	inboxUnreadCount := 0

	userInboxRow, err := isolation.CompanyConversationConn.GetUserInboxOne(isolation.Context, userId)
	if err != nil {
		return err
	}
	if userInboxRow != nil {

		messagesUnreadCount += userInboxRow.MessageUnreadCount
		inboxUnreadCount += userInboxRow.ConversationUnreadCount
	}

	threadRow, err := isolation.CompanyThreadConn.GetOne(isolation.Context, userId)
	if err != nil {
		return err
	}

	if threadRow != nil {

		messagesUnreadCount += threadRow.MessageUnreadCount

		if threadRow.MessageUnreadCount > 0 {
			inboxUnreadCount += 1
		}
	}

	notificationRow, err := isolation.CompanyDataConn.GetOne(isolation.Context, userId)
	if err != nil {
		return err
	}

	if notificationRow != nil {

		if notificationRow.Count > 0 {
			inboxUnreadCount += 1
		}
	}

	if isolation.SocketKeyToPivot == "" {
		isolation.SocketKeyToPivot = gatewayPhpCompany.GetPivotSocketKey(isolation.GetCompanyId(), isolation.GetGlobalIsolation().GetConfig().SocketKeyMe)
	}
	if isolation.SocketKeyToPivot == "" {
		return fmt.Errorf("[anomaly] bad socket key")
	}
	gatewayPhpPivot.UpdateBadge(messagesUnreadCount, inboxUnreadCount, taskList, userId, isolation.GetCompanyId(), isolation.SocketKeyToPivot)
	return nil
}
