package Database

import (
	"context"
	"database/sql"
	"errors"
	"fmt"

	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"github.com/getCompassUtils/go_base_frame/api/system/server"

	"go_event/api/conf"
	CompanyEnvironment "go_event/api/includes/type/company_config"
	Isolation "go_event/api/includes/type/isolation"
	"reflect"
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

// GetAll выполняет указанный запрос в указанном контексте,
// если контекста нет, то нужно передать nil
func (c *Connection) GetAll(ctx context.Context, query string, args ...interface{}) (*sql.Rows, error) {

	if err := c.ping(ctx); err != nil {
		return nil, err
	}

	// если попытались выполнить не селект - отбрасываем
	if !strings.HasPrefix(query, "SELECT") {
		return nil, fmt.Errorf("tried to execute not SELECT query")
	}

	// считаем количество плейсхолдеров и аргументов
	pc := strings.Count(query, "?")

	if pc != len(args) {
		return nil, fmt.Errorf("placeholder count doesn't match to args count")
	}

	if ctx == nil {
		return c.dbConnection.Query(query, args...)
	} else {
		return c.dbConnection.QueryContext(ctx, query, args...)
	}
}

// GetOne выполняет указанный запрос в указанном контексте,
// если контекста нет, то нужно передать nil
func (c *Connection) GetOne(ctx context.Context, query string, args ...interface{}) *sql.Row {

	if err := c.ping(ctx); err != nil {
		return nil
	}

	// если попытались выполнить не селект - отбрасываем
	if !strings.HasPrefix(query, "SELECT") {
		return nil
	}
	// считаем количество плейсхолдеров и аргументов
	pc := strings.Count(query, "?")

	if pc != len(args) {
		return nil
	}

	if ctx == nil {
		return c.dbConnection.QueryRow(query, args...)
	} else {
		return c.dbConnection.QueryRowContext(ctx, query, args...)
	}
}

type emptyResult struct {
	lastInsertID int64
	rowsAffected int64
}

func (r emptyResult) LastInsertId() (int64, error) {
	return r.lastInsertID, nil
}

func (r emptyResult) RowsAffected() (int64, error) {
	return r.rowsAffected, nil
}

// InsertIgnore Выполняет вставку записи в указанном контексте
// если контекста нет, то нужно передать nil
func (c *Connection) InsertIgnore(ctx context.Context, tableName string, insert interface{}) (sql.Result, error) {

	if err := c.ping(ctx); err != nil {
		return nil, err
	}

	if server.IsReserveServer() {
		return emptyResult{
			lastInsertID: 0,
			rowsAffected: 0,
		}, nil
	}

	query, args := FormatInsertOrUpdate(tableName, insert)

	if ctx == nil {
		return c.dbConnection.Exec(query, args...)
	} else {
		return c.dbConnection.ExecContext(ctx, query, args...)
	}
}

// Update выполняет обновление записи
func (c *Connection) Update(ctx context.Context, query string, update interface{}, values ...interface{}) (err error) {

	if err = c.ping(ctx); err != nil {
		return err
	}

	if server.IsReserveServer() {
		return nil
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

	if server.IsReserveServer() {
		return nil
	}

	if ctx == nil {
		_, err = c.dbConnection.Exec(query, args...)
	} else {
		_, err = c.dbConnection.ExecContext(ctx, query, args...)
	}

	return err
}

// FormatInsertOrUpdate готовим запрос для InsertOrUpdate
func FormatInsertOrUpdate(tableName string, insert interface{}) (string, []interface{}) {

	var keys, valueKeys, updateKeys string
	var values []interface{}

	v := reflect.ValueOf(insert)

	if v.Kind() == reflect.Ptr {
		v = v.Elem()
	}

	for i := 0; i < v.NumField(); i++ {

		// получаем имя для вставки использования в mysql
		k, ok := v.Type().Field(i).Tag.Lookup("sqlname")

		// если не нашли - пропускаем
		if !ok || k == "-" {
			continue
		}

		keys += fmt.Sprintf("`%s` , ", k)
		valueKeys += "? , "
		updateKeys += fmt.Sprintf("`%s` = ? , ", k)
		values = append(values, v.Field(i).Interface())
	}

	values = append(values, values...)

	keys = strings.TrimSuffix(keys, " , ")
	valueKeys = strings.TrimSuffix(valueKeys, " , ")
	updateKeys = strings.TrimSuffix(updateKeys, " , ")

	query := fmt.Sprintf("INSERT INTO `%s` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s;", tableName, keys, valueKeys, updateKeys)
	return query, values
}

// FormatUpdate готовим строку для обновления записи
func FormatUpdate(update interface{}) (string, []interface{}) {

	var str string
	var values []interface{}

	v := reflect.ValueOf(update)

	if v.Kind() == reflect.Ptr {
		v = v.Elem()
	}

	for i := 0; i < v.NumField(); i++ {

		f := v.Field(i)

		// получаем имя для вставки использования в mysql
		k, ok := v.Type().Field(i).Tag.Lookup("sqlname")

		// если не нашли - пропускаем
		if !ok || k == "-" {
			continue
		}

		str += fmt.Sprintf("`%s` = ? , ", k)
		values = append(values, f.Interface())
	}

	return strings.TrimSuffix(str, " , "), values
}
