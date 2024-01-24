package company_data

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей уведомления
// -------------------------------------------------------

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
)

const tableKey = "member_menu"

type UserInboxRow struct {
	UserId int64
	Count  int
}

// GetOne получить запись
func (dbConn *DbConn) GetOne(ctx context.Context, userId int64) (*UserInboxRow, error) {

	// запрос проверен на EXPLAIN (INDEX=user_id.is_unread.type	)
	query := fmt.Sprintf("SELECT COUNT(*) as `count` FROM `%s` WHERE `user_id` = ? AND `is_unread` = ? LIMIT ?", tableKey)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	userInboxRow := &UserInboxRow{}

	err := dbConn.Conn.QueryRowContext(queryCtx, query, userId, 1, 1).Scan(&userInboxRow.Count)
	if err != nil {

		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}

	userInboxRow.UserId = userId

	return userInboxRow, nil
}
