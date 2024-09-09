package company

import (
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

// CreateUserOnEmptyDb создает пользователя на пустой базе данных
func CreateUserOnEmptyDb(port *port_registry.PortRegistryStruct) error {

	// тут меняем пароль и создавать пользователя
	dbUser, dbPass, err := port.GetCredentials()
	if err != nil {
		return err
	}

	// получаем параметры подключения к базе данных
	connection, err := sql.Open("mysql", fmt.Sprintf("root:root@tcp(%s:%d)/", GetCompanyHost(port.Port), port.Port))
	if err != nil {
		return err
	}

	defer func() {

		err := connection.Close()
		if err != nil {
			log.Errorf("%v", err)
		}
	}()

	_, err = connection.Exec(fmt.Sprintf("CREATE USER '%s'@'127.0.0.1' IDENTIFIED BY '%s';", dbUser, dbPass))
	if err != nil {
		return err
	}

	_, err = connection.Exec(fmt.Sprintf("GRANT ALL ON *.* TO '%s'@'127.0.0.1' WITH GRANT OPTION;", dbUser))
	if err != nil {
		return err
	}

	_, err = connection.Exec(fmt.Sprintf("CREATE USER '%s'@'%%' IDENTIFIED BY '%s';", dbUser, dbPass))
	if err != nil {
		return err
	}

	_, err = connection.Exec(fmt.Sprintf("GRANT ALTER, ALTER ROUTINE, CREATE, CREATE ROUTINE, CREATE TABLESPACE, CREATE TEMPORARY TABLES, CREATE USER, CREATE VIEW, DELETE, DROP, EVENT, GRANT OPTION, INDEX, INSERT, PROCESS, LOCK TABLES, EXECUTE, REFERENCES, RELOAD, SELECT, SHOW DATABASES, TRIGGER, SHOW VIEW, UPDATE, SYSTEM_VARIABLES_ADMIN ON *.* TO '%s'@'%%' WITH GRANT OPTION;", dbUser))
	if err != nil {
		return err
	}

	// добавляем пользователя для бэкапа данных при релокации
	_, err = connection.Exec(fmt.Sprintf("CREATE USER '%s'@localhost IDENTIFIED BY '%s';", conf.GetConfig().BackupUser, conf.GetConfig().BackupUserPassword))
	if err != nil {
		return err
	}

	_, err = connection.Exec(fmt.Sprintf("GRANT RELOAD, BACKUP_ADMIN, LOCK TABLES, REPLICATION CLIENT, CREATE TABLESPACE, PROCESS, SUPER, CREATE, INSERT, SELECT ON * . * TO '%s'@localhost;", conf.GetConfig().BackupUser))
	if err != nil {
		return err
	}

	_, err = connection.Exec("DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');")
	if err != nil {
		return err
	}

	_, err = connection.Exec(fmt.Sprintf("FLUSH PRIVILEGES"))
	if err != nil {
		return err
	}

	return nil
}
func GetTableInformationSchema(credentials *sharding.DbCredentials, dbKey string, tableName string) (map[string]string, error) {

	conn := sharding.MysqlWithCredentials("information_schema", credentials)
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	query := fmt.Sprintf("SELECT * FROM `%s` WHERE `TABLE_SCHEMA` = ? AND `TABLE_NAME` = ? LIMIT ?", "tables")
	row, err := conn.FetchQuery(query, dbKey, tableName, 1)
	if err != nil {
		return map[string]string{}, err
	}
	return row, nil
}

func GetFieldsFromTable(credentials *sharding.DbCredentials, dbKey string, tableName string) (map[int]map[string]string, error) {

	conn := sharding.MysqlWithCredentials(dbKey, credentials)
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	query := fmt.Sprintf("SHOW COLUMNS FROM `%s`;", tableName)
	result, err := conn.GetAll(query)
	if err != nil {
		return map[int]map[string]string{}, err
	}
	return result, nil
}

func GetIndexesFromTable(credentials *sharding.DbCredentials, dbKey string, tableName string) (map[int]map[string]string, error) {

	conn := sharding.MysqlWithCredentials(dbKey, credentials)
	if conn == nil {
		return nil, fmt.Errorf("пришел DbName: %s для которого не найдено подключение", dbKey)
	}

	query := fmt.Sprintf("SHOW INDEX FROM `%s`;", tableName)
	result, err := conn.GetAll(query)
	if err != nil {
		return map[int]map[string]string{}, err
	}
	return result, nil
}

// выполняем sql запросы
func ExecSql(credentials *sharding.DbCredentials, dbKey string, sqlFile string) (err error) {

	sc, err := ioutil.ReadFile(sqlFile)
	sqlContent := string(sc)

	conn := sharding.MysqlWithCredentials(dbKey, credentials)
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

		err = conn.Query(query)
		if err != nil {
			return err
		}
	}

	return nil
}

// инициализировать базу
func CreateIfNotExistDatabase(credentials *sharding.DbCredentials, dbKey string) (bool, error) {

	// пустой ключ, так как мы просто хотим подключиться к хосту
	conn := sharding.MysqlWithCredentials("", credentials)
	if conn == nil {
		return false, fmt.Errorf("не удалось установить соединение с портом %d", credentials.Port)
	}

	dbList, err := conn.GetAll("SHOW DATABASES;")

	for _, db := range dbList {

		if db["Database"] == dbKey {
			return false, nil
		}
	}

	err = conn.Query(fmt.Sprintf("CREATE SCHEMA IF NOT EXISTS %s DEFAULT CHARACTER SET utf8;", dbKey))
	if err != nil {
		return false, err
	}

	return true, nil
}

// инициализировать базу company_system
func CreateDatabase(credentials *sharding.DbCredentials, dbKey string) error {

	// пустой ключ, так как мы просто хотим подключиться к хосту
	conn := sharding.MysqlWithCredentials("", credentials)
	if conn == nil {
		return fmt.Errorf("не удалось установить соединение с портом %d", credentials.Port)
	}

	err := conn.Query(fmt.Sprintf("CREATE SCHEMA IF NOT EXISTS %s DEFAULT CHARACTER SET utf8;", dbKey))
	if err != nil {
		return err
	}

	return nil
}

func GetCompanyHost(port int32) string {

	return fmt.Sprintf("%s-%d", conf.GetConfig().DominoId, port)
}
