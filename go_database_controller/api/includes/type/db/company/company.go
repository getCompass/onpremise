package company

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/system/sharding"
	"io/ioutil"
	"strings"
)

type Database struct {
	Database string `json:"Database"`
}

func GetRootPassword(port *port_registry.PortRegistryStruct) (string, error) {

	if conf.GetConfig().DatabaseDriver == "host" {

		dbKey := fmt.Sprintf("%s:%d", port.Host, port.Port)

		if _, ok := conf.GetPredefinedDbConfig()[dbKey]; !ok {
			return "", fmt.Errorf("cant find db in predefined config")
		}

		return conf.GetPredefinedDbConfig()[dbKey].RootPassword, nil
	}

	return "root", nil
}

// IsUserExists проверить, суещствует ли пользователь
func IsUserExists(port *port_registry.PortRegistryStruct) (bool, error) {

	// тут меняем пароль и создавать пользователя
	dbUser, dbPass, err := port.GetCredentials()
	if err != nil {
		return false, err
	}

	// получаем параметры подключения к базе данных
	connection, err := sql.Open("mysql", fmt.Sprintf("%s:%s@tcp(%s:%d)/", dbUser, dbPass, GetCompanyHost(port), port.Port))
	if err != nil {
		return false, err
	}

	err = connection.Ping()
	if err != nil {
		return false, nil
	}

	return true, nil
}

// CreateUserOnEmptyDb создает пользователя на пустой базе данных
func CreateUserOnEmptyDb(port *port_registry.PortRegistryStruct) error {

	rootPassword, err := GetRootPassword(port)
	if err != nil {
		return err
	}

	// тут меняем пароль и создавать пользователя
	dbUser, dbPass, err := port.GetCredentials()
	if err != nil {
		return err
	}

	// получаем параметры подключения к базе данных
	connection, err := sql.Open("mysql", fmt.Sprintf("root:%s@tcp(%s:%d)/", rootPassword, GetCompanyHost(port), port.Port))
	if err != nil {
		return err
	}

	defer func() {

		err := connection.Close()
		if err != nil {
			log.Errorf("%v", err)
		}
	}()

	// nosemgrep
	_, err = connection.Exec(fmt.Sprintf("CREATE USER '%s'@'%%' IDENTIFIED BY '%s';", dbUser, dbPass))
	if err != nil {
		return err
	}

	// добавляем пользователя для бэкапа данных при релокации
	// nosemgrep
	_, err = connection.Exec(fmt.Sprintf("CREATE USER '%s'@'127.0.0.1' IDENTIFIED BY '%s';", conf.GetConfig().BackupUser, conf.GetConfig().BackupUserPassword))
	if err != nil {
		return err
	}

	// для predefined драйвера НИ В КОЕМ СЛУЧАЕ НЕ УДАЛЯТЬ НИКАКИХ ПОЛЬЗОВАТЕЛЕЙ И НЕ УПРАВЛЯТЬ ИХ ПРАВАМИ
	if conf.GetConfig().DatabaseDriver != "host" {

		// nosemgrep
		_, err = connection.Exec(fmt.Sprintf("GRANT ALTER, ALTER ROUTINE, CREATE, CREATE ROUTINE, CREATE TABLESPACE, CREATE TEMPORARY TABLES, CREATE USER, CREATE VIEW, DELETE, DROP, EVENT, GRANT OPTION, INDEX, INSERT, PROCESS, LOCK TABLES, EXECUTE, REFERENCES, RELOAD, SELECT, SHOW DATABASES, TRIGGER, SHOW VIEW, UPDATE, SYSTEM_VARIABLES_ADMIN ON *.* TO '%s'@'%%' WITH GRANT OPTION;", dbUser))
		if err != nil {
			return err
		}

		_, err = connection.Exec("DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');")
		if err != nil {
			return err
		}
	}

	_, err = connection.Exec(fmt.Sprintf("FLUSH PRIVILEGES"))
	if err != nil {
		return err
	}

	return nil
}

func GetTableInformationSchema(ctx context.Context, credentials *sharding.DbCredentials, dbKey string, tableName string) (map[string]string, error) {

	conn := sharding.MysqlWithCredentials(ctx, "information_schema", credentials)
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `TABLE_SCHEMA` = ? AND `TABLE_NAME` = ? LIMIT ?", "tables")
	row, err := conn.FetchQuery(ctx, query, dbKey, tableName, 1)
	if err != nil {
		return map[string]string{}, err
	}
	return row, nil
}

func GetFieldsFromTable(ctx context.Context, credentials *sharding.DbCredentials, dbKey string, tableName string) (map[int]map[string]string, error) {

	conn := sharding.MysqlWithCredentials(ctx, dbKey, credentials)
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	query := fmt.Sprintf("SHOW COLUMNS FROM `%s`;", tableName)
	result, err := conn.GetAll(ctx, query)
	if err != nil {
		return map[int]map[string]string{}, err
	}
	return result, nil
}

func GetIndexesFromTable(ctx context.Context, credentials *sharding.DbCredentials, dbKey string, tableName string) (map[int]map[string]string, error) {

	conn := sharding.MysqlWithCredentials(ctx, dbKey, credentials)
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	query := fmt.Sprintf("SHOW INDEX FROM `%s`;", tableName)
	result, err := conn.GetAll(ctx, query)
	if err != nil {
		return map[int]map[string]string{}, err
	}
	return result, nil
}

// выполняем sql запросы
func ExecSql(ctx context.Context, credentials *sharding.DbCredentials, dbKey string, sqlFile string) (err error) {

	sc, err := ioutil.ReadFile(sqlFile)
	sqlContent := string(sc)

	conn := sharding.MysqlWithCredentials(ctx, dbKey, credentials)
	if conn == nil {
		return fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	// делим запросы по ; и убираем последнйи элемент - он пустой
	sqlQueries := strings.Split(sqlContent, ";\n")

	if sqlQueries[len(sqlQueries)-1] == "" {
		sqlQueries = sqlQueries[:len(sqlQueries)-1]
	}

	// выполняем запросы
	for _, query := range sqlQueries {

		err = conn.Query(ctx, query)
		if err != nil {
			return err
		}
	}

	return nil
}

// выдать права на базу
func GrantPermissionsOnDatabase(ctx context.Context, credentials *sharding.DbCredentials, rootCredentials *sharding.DbCredentials, dbKey string) error {

	if conf.GetConfig().DatabaseDriver != "host" {
		return nil
	}

	// пустой ключ, так как мы просто хотим подключиться к хосту
	conn := sharding.MysqlWithCredentials(ctx, "", rootCredentials)
	if conn == nil {

		return fmt.Errorf("не удалось установить соединение с портом %d", rootCredentials.Port)
	}

	// nosemgrep
	_, err := conn.ConnectionPool.Exec(fmt.Sprintf("GRANT ALL ON %s.* TO '%s'@'%%' WITH GRANT OPTION;", dbKey, credentials.User))
	if err != nil {
		return err
	}

	_, err = conn.ConnectionPool.Exec(fmt.Sprintf("FLUSH PRIVILEGES"))
	if err != nil {
		return err
	}

	return nil
}

// инициализировать базу
func CreateIfNotExistDatabase(ctx context.Context, credentials *sharding.DbCredentials, rootCredentials *sharding.DbCredentials, dbKey string) (bool, error) {

	// проверяем входные параметры
	if credentials == nil {
		return false, fmt.Errorf("credentials cannot be nil")
	}

	// выдаем права на базу
	err := GrantPermissionsOnDatabase(ctx, credentials, rootCredentials, dbKey)
	if err != nil {
		return false, err
	}

	// пустой ключ, так как мы просто хотим подключиться к хосту
	conn := sharding.MysqlWithCredentials(ctx, "", credentials)
	if conn == nil {
		return false, fmt.Errorf("не удалось установить соединение с портом %d", credentials.Port)
	}

	dbList, err := conn.GetAll(ctx, "SHOW DATABASES;")

	for _, db := range dbList {

		if db["Database"] == dbKey {
			return false, nil
		}
	}

	err = conn.Query(ctx, fmt.Sprintf("CREATE SCHEMA IF NOT EXISTS %s DEFAULT CHARACTER SET utf8;", dbKey))
	if err != nil {
		return false, err
	}

	return true, nil
}

// инициализировать базу company_system
func CreateDatabase(ctx context.Context, credentials *sharding.DbCredentials, rootCredentials *sharding.DbCredentials, dbKey string) error {

	// выдаем права на базу
	err := GrantPermissionsOnDatabase(ctx, credentials, rootCredentials, dbKey)
	if err != nil {
		return err
	}

	// пустой ключ, так как мы просто хотим подключиться к хосту
	conn := sharding.MysqlWithCredentials(ctx, "", credentials)
	if conn == nil {
		return fmt.Errorf("не удалось установить соединение с портом %d", credentials.Port)
	}

	err = conn.Query(ctx, fmt.Sprintf("CREATE SCHEMA IF NOT EXISTS %s DEFAULT CHARACTER SET utf8;", dbKey))
	if err != nil {
		return err
	}

	return nil
}

func GetCompanyHost(port *port_registry.PortRegistryStruct) string {

	if port.Host != "" {
		return port.Host
	}
	return fmt.Sprintf("%s-%d", conf.GetConfig().DominoId, port.Port)
}
