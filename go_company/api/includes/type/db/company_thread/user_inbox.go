package company_thread

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей непрочитанные пользователя в тредах
// -------------------------------------------------------

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
)

const tableKey = "user_inbox"

type UserInboxRow struct {
	UserId             int64
	MessageUnreadCount int
}

// GetOne получить запись
func (dbConn *DbConn) GetOne(ctx context.Context, userId int64) (*UserInboxRow, error) {

	// запрос проверен на EXPLAIN (INDEX=PRIMARY).
	query := fmt.Sprintf("SELECT user_id, message_unread_count FROM `%s` WHERE `user_id` = ? LIMIT %d", tableKey, 1)

	userInboxRow := &UserInboxRow{}

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := dbConn.Conn.QueryRowContext(queryCtx, query, userId).Scan(&userInboxRow.UserId, &userInboxRow.MessageUnreadCount)
	if err != nil {

		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}

	return userInboxRow, nil
}
