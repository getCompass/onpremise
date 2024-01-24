package company_conversation

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей непрочитанные пользователя в диалогах
// -------------------------------------------------------

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
)

const tableKeyUserInbox = "user_inbox"

type UserInboxRow struct {
	UserId                  int64
	MessageUnreadCount      int
	ConversationUnreadCount int
}

// GetUserInboxOne получить запись
func (dbConn *DbConn) GetUserInboxOne(ctx context.Context, userId int64) (*UserInboxRow, error) {

	// запрос проверен на EXPLAIN (INDEX=PRIMARY).
	query := fmt.Sprintf("SELECT user_id, message_unread_count, conversation_unread_count FROM `%s` WHERE `user_id` = ? LIMIT %d", tableKeyUserInbox, 1)

	userInboxRow := &UserInboxRow{}

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := dbConn.Conn.QueryRowContext(queryCtx, query, userId).
		Scan(&userInboxRow.UserId, &userInboxRow.MessageUnreadCount, &userInboxRow.ConversationUnreadCount)

	if err != nil {

		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}

	return userInboxRow, nil
}
