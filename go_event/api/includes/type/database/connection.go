package Database

import (
	"context"
	"database/sql"
	"errors"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_event/api/conf"
	CompanyEnvironment "go_event/api/includes/type/company_config"
	Isolation "go_event/api/includes/type/isolation"
	"strings"
	"time"
)

type Connection struct {
	uniqueKey    string
	isActive     bool      // флаг состояния
	dbConnection *sql.DB   // само подключение
	dbName       string    // имя базы, для которой поднимается коннект
	lastPingAt   int64     // время последнего пинга
	stateCh      chan bool // канал состояния
}

const pingPeriod = 5     // в секунда
const pingTimeout = 1000 // в миллисекундах

// создает новое подключение к базе данных компании
func makeCompanyConnection(isolation *Isolation.Isolation, dbName string) (*Connection, error) {

	companyConf := CompanyEnvironment.LeaseCompanyConfig(isolation)
	if companyConf == nil {
		return nil, errors.New("there is no company config")
	}

	return makeConnection(isolation.GetContext(), isolation.GetUniq(), &companyConf.Mysql, dbName)
}

// создает новое подключение к глобальной базе данных
func makeGlobalConnection(isolation *Isolation.Isolation) (*Connection, error) {

	// получаем конфигурацию бд
	globalSystemShardingDbConf := conf.GetShardingConfig().Mysql["global"]
	dbConfig := conf.DbConfig{
		Host: globalSystemShardingDbConf.Mysql.Host,
		Port: globalSystemShardingDbConf.Mysql.Port,
		User: globalSystemShardingDbConf.Mysql.User,
		Pass: globalSystemShardingDbConf.Mysql.Pass,
	}

	return makeConnection(isolation.GetContext(), isolation.GetUniq(), &dbConfig, globalSystemShardingDbConf.Db)
}

// создает новое подключение к базе данных в указанном контексте
func makeConnection(ctx context.Context, uniqueKey string, config *conf.DbConfig, dbName string) (*Connection, error) {

	// создаем обертку для подключения
	c := &Connection{uniqueKey: uniqueKey, isActive: true, lastPingAt: 0, dbName: dbName, stateCh: make(chan bool)}

	dbHost := fmt.Sprintf("%s:%d", config.Host, config.Port)
	dbConnection, err := mysql.CreateMysqlConnection(ctx, c.dbName, dbHost, config.User, config.Pass, 1, false)

	if err != nil {
		return nil, err
	}

	c.dbConnection = dbConnection.ConnectionPool
	c.lastPingAt = time.Now().Unix()

	// запускаем жизненный цикл подключения
	go c.lifeRoutine(ctx)

	return c, nil
}

// пингует базу данных, чтобы удерживать коннект
func (c *Connection) ping(ctx context.Context) error {

	if !c.isActive {
		return errors.New("connection is dead")
	}

	if c.lastPingAt+pingPeriod > time.Now().Unix() {
		return nil
	}

	ctx, closeCtx := context.WithTimeout(ctx, pingTimeout*time.Millisecond)
	defer closeCtx()

	if err := c.dbConnection.PingContext(ctx); err != nil {
		return err
	}

	c.lastPingAt = time.Now().Unix()
	return nil
}

// жизненный цикл рутины подключения
func (c *Connection) lifeRoutine(ctx context.Context) {

	Isolation.Inc(fmt.Sprintf("db-connection-%s", c.dbName))
	defer Isolation.Dec(fmt.Sprintf("db-connection-%s", c.dbName))

	for {

		select {
		case <-c.stateCh:
			return
		case <-time.After(time.Duration(pingPeriod) * time.Second):

			// пингуем, чтобы коннект не падал раз в n времени
			if err := c.ping(ctx); err != nil {
				log.Errorf("db %s connection error: %s", c.dbName, err.Error())
			}
		}
	}

}

// закрывает подключение
func (c *Connection) close() {

	defer func() {

		if err := recover(); err != nil {
			fmt.Println("Recovered. Error:\n", err)
		}
	}()

	if !c.isActive {
		return
	}

	// отмечаем подключение закрытым
	c.isActive = false
	close(c.stateCh)

	go func() {

		_ = c.dbConnection.Close()
	}()
}

// Transact вызов функции в транзакции
func (c *Connection) Transact(callback func() error) (err error) {

	if !c.isActive {
		return errors.New("connection is dead")
	}

	tx, err := c.dbConnection.Begin()

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

// Query выполняет указанный запрос в указанном контексте,
// если контекста нет, то нужно передать nil
func (c *Connection) Query(ctx context.Context, query string, args ...interface{}) (*sql.Rows, error) {

	if err := c.ping(ctx); err != nil {
		return nil, err
	}

	if ctx == nil {
		return c.dbConnection.Query(query, args...)
	} else {
		return c.dbConnection.QueryContext(ctx, query, args...)
	}
}

// InsertIgnore Выполняет вставку записи в указанном контексте
// если контекста нет, то нужно передать nil
func (c *Connection) InsertIgnore(ctx context.Context, tableName string, insert map[string]interface{}) (sql.Result, error) {

	if err := c.ping(ctx); err != nil {
		return nil, err
	}

	query, args := FormatInsertOrUpdate(tableName, insert)

	if ctx == nil {
		return c.dbConnection.Exec(query, args...)
	} else {
		return c.dbConnection.ExecContext(ctx, query, args...)
	}
}

// Update выполняет обновление записи
func (c *Connection) Update(ctx context.Context, query string, update map[string]interface{}, values ...interface{}) (err error) {

	if err = c.ping(ctx); err != nil {
		return err
	}

	keyString, args := FormatUpdate(update)
	query = strings.Replace(query, "??", keyString, 1)

	args = append(args, values...)

	if ctx == nil {
		_, err = c.dbConnection.Exec(query, args...)
	} else {
		_, err = c.dbConnection.ExecContext(ctx, query, args...)
	}

	return err
}

// Delete удаляет записи
func (c *Connection) Delete(ctx context.Context, query string, args ...interface{}) (err error) {

	if err = c.ping(ctx); err != nil {
		return err
	}

	if ctx == nil {
		_, err = c.dbConnection.Exec(query, args...)
	} else {
		_, err = c.dbConnection.ExecContext(ctx, query, args...)
	}

	return err
}

// FormatInsertOrUpdate готовим запрос для InsertOrUpdate
func FormatInsertOrUpdate(tableName string, insert map[string]interface{}) (string, []interface{}) {

	var keys, valueKeys, updateKeys string
	var values []interface{}

	for k, v := range insert {

		keys += fmt.Sprintf("`%s` , ", k)
		valueKeys += "? , "
		updateKeys += fmt.Sprintf("`%s` = ? , ", k)
		values = append(values, v)
	}

	values = append(values, values...)

	keys = strings.TrimSuffix(keys, " , ")
	valueKeys = strings.TrimSuffix(valueKeys, " , ")
	updateKeys = strings.TrimSuffix(updateKeys, " , ")

	query := fmt.Sprintf("INSERT INTO `%s` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s;", tableName, keys, valueKeys, updateKeys)
	return query, values
}

// FormatUpdate готовим строку для обновления записи
func FormatUpdate(insert map[string]interface{}) (string, []interface{}) {

	var str string
	var values []interface{}

	for k, v := range insert {

		str += fmt.Sprintf("`%s` = ? , ", k)
		values = append(values, v)
	}

	return strings.TrimSuffix(str, " , "), values
}
