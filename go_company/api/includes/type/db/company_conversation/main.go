package company_conversation

import (
	"context"
	"database/sql"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
)

// -------------------------------------------------------
// пакет, содержащий интерфейсы для работы с таблицей
// содержащей непрочитанные пользователя в диалогах
// -------------------------------------------------------

const dbKey = "company_conversation"

type DbConn struct {
	Db   string
	Conn *sql.DB
}

func MakeCompanyConversation(ctx context.Context, host string, user string, pass string, mysqlMaxConn int) (*DbConn, error) {

	conn, err := mysql.CreateMysqlConnection(ctx, dbKey, host, user, pass, mysqlMaxConn, false)
	if err != nil {
		return nil, err
	}

	return &DbConn{
		Db:   dbKey,
		Conn: conn.ConnectionPool,
	}, nil
}
