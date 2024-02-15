package company_data

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"strings"
)

const memberNotificationListTableKey = "member_notification_list"

type NotificationRow struct {
	UserId       int64
	SnoozedUntil int64
	Token        string
	DeviceList   string
	Extra        string
}

// получаем токены пользователей по id пользователей
func (dbConn *DbConn) GetMemberNotificationList(ctx context.Context, userIdList []int64) ([]*NotificationRow, error) {

	var plugForIn []string
	var queryArgs []interface{}
	for _, v := range userIdList {

		plugForIn = append(plugForIn, "?")
		queryArgs = append(queryArgs, v)
	}

	queryArgs = append(queryArgs, len(plugForIn))

	query := fmt.Sprintf("SELECT user_id, snoozed_until, token, device_list, extra FROM `%s` WHERE `user_id` IN (%s) LIMIT ?", memberNotificationListTableKey, strings.Join(plugForIn, ","))

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)

	results, err := dbConn.Conn.QueryContext(queryCtx, query, queryArgs...)

	defer func() {

		if results != nil {
			_ = results.Close()
		}

		cancel()
	}()

	if err != nil {
		return nil, fmt.Errorf("неудачный запрос: %s в базу %s Error: %v", query, dbKey, err)
	}

	list := make([]*NotificationRow, 0, len(plugForIn))
	for results.Next() {

		var row NotificationRow

		err = results.Scan(&row.UserId, &row.SnoozedUntil, &row.Token, &row.DeviceList, &row.Extra)
		if err != nil {
			return nil, err
		}

		list = append(list, &row)
	}

	return list, nil
}
