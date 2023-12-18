package company_data

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
)

const sessionActiveListTableKey = "session_active_list"

type SessionRow struct {
	UserId    int64
	UserAgent string
	IpAddress string
	Extra     string
}

// GetActiveSessionRow получаем сессию по ее uniq
func (dbConn *DbConn) GetActiveSessionRow(ctx context.Context, sessionUniq string) (*SessionRow, error) {

	var row SessionRow
	query := fmt.Sprintf("SELECT user_id, user_agent, ip_address, extra FROM `%s` WHERE `session_uniq` = ? LIMIT ?", sessionActiveListTableKey)

	queryCtx, cancel := context.WithTimeout(ctx, mysql.QueryTimeout)
	defer cancel()

	err := dbConn.Conn.QueryRowContext(queryCtx, query, sessionUniq, 1).Scan(&row.UserId, &row.UserAgent, &row.IpAddress, &row.Extra)
	if err != nil {

		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}

	return &row, nil
}
