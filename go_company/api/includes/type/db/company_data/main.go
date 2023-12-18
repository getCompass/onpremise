package company_data

// пакет для работы с rating базами

import (
	"context"
	"database/sql"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
)

const dbKey = "company_data"

type DbConn struct {
	Db   string
	Conn *sql.DB
}

// создаем коннект к базе
func MakeCompanyData(ctx context.Context, host string, user string, pass string, mysqlMaxConn int) (*DbConn, error) {

	conn, err := mysql.CreateMysqlConnection(ctx, dbKey, host, user, pass, mysqlMaxConn, false)
	if err != nil {
		return nil, err
	}

	return &DbConn{
		Db:   dbKey,
		Conn: conn.ConnectionPool,
	}, nil
}

// открываем транзакцию
func (conn *DbConn) BeginTransaction() (*sql.Tx, error) {

	return conn.Conn.Begin()
}

// коммитим транзакцию
func CommitTransaction(transactionItem *sql.Tx) {

	err := transactionItem.Commit()
	if err != nil {

		log.Errorf("Откатили транзакцию %v", err)
		_ = transactionItem.Rollback()
	}
}

// получить имя базы данных
func GetDbName() (string, error) {

	return dbKey, nil
}
