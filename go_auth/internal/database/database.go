package database

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	"go_auth/internal/config"
	"time"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
)

type Database struct {
	isActive     bool      // флаг состояния
	dbConnection *sql.DB   // само подключение
	dbName       string    // имя базы, для которой поднимается коннект
	lastPingAt   int64     // время последнего пинга
	stateCh      chan bool // канал состояния
}

const pingPeriod = 5     // в секунда
const pingTimeout = 1000 // в миллисекундах

// создает новое подключение к базе данных в указанном контексте
func InitConnection(ctx context.Context, config *config.DatabaseConfigStruct, dbName string) (*Database, error) {

	// создаем обертку для подключения
	d := &Database{isActive: true, lastPingAt: 0, dbName: dbName, stateCh: make(chan bool)}

	dbHost := fmt.Sprintf("%s:%d", config.Host, config.Port)
	dbConnection, err := mysql.CreateMysqlConnection(ctx, d.dbName, dbHost, config.User, config.Password, 1, false)

	if err != nil {
		return nil, err
	}

	d.dbConnection = dbConnection.ConnectionPool
	d.lastPingAt = time.Now().Unix()

	// запускаем жизненный цикл подключения
	go d.lifeRoutine(ctx)

	return d, nil
}

// пингует базу данных, чтобы удерживать коннект
func (d *Database) ping(ctx context.Context) error {

	if !d.isActive {
		return errors.New("connection is dead")
	}

	if d.lastPingAt+pingPeriod > time.Now().Unix() {
		return nil
	}

	ctx, closeCtx := context.WithTimeout(ctx, pingTimeout*time.Millisecond)
	defer closeCtx()

	if err := d.dbConnection.PingContext(ctx); err != nil {
		return err
	}

	d.lastPingAt = time.Now().Unix()
	return nil
}

// жизненный цикл рутины подключения
func (d *Database) lifeRoutine(ctx context.Context) {

	for {

		select {
		case <-d.stateCh:
			return
		case <-time.After(time.Duration(pingPeriod) * time.Second):

			// пингуем, чтобы коннект не падал раз в n времени
			if err := d.ping(ctx); err != nil {
				log.Errorf("db %s connection error: %s", d.dbName, err.Error())
			}
		}
	}

}

// закрывает подключение
func (d *Database) close() {

	defer func() {

		if err := recover(); err != nil {
			fmt.Println("Recovered. Error:\n", err)
		}
	}()

	if !d.isActive {
		return
	}

	// отмечаем подключение закрытым
	d.isActive = false
	close(d.stateCh)

	go func() {

		_ = d.dbConnection.Close()
	}()
}

// Transact вызов функции в транзакции
func (d *Database) Transact(callback func() error) (err error) {

	if !d.isActive {
		return errors.New("connection is dead")
	}

	tx, err := d.dbConnection.Begin()

	defer func() {

		if p := recover(); p != nil {

			// паника, откатываем и паникуем дальше
			_ = tx.Rollback()
			panic(p)
		} else if err != nil {

			// случилась ошибка, откатываемся
			_ = tx.Rollback()
		} else {

			// all good, commit
			err = tx.Commit()
		}
	}()

	err = callback()
	return err
}
