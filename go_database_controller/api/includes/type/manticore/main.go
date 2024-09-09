package manticore

import (
	"context"
	"database/sql"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/flags"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"go_database_controller/api/conf"
	"go_database_controller/api/includes/type/db/company"
	"go_database_controller/api/includes/type/logger"
	"go_database_controller/api/includes/type/migration"
	"go_database_controller/api/includes/type/port_registry"
	"go_database_controller/api/system/sharding"
	"io/ioutil"
	"strings"
)

const searchSchemaPath = "sql/manticore/"
const indexNamePrefix = "main"

// InitTable выполняем инициализацию таблицы поиска
// @long
func InitTable(ctx context.Context, spaceId int64) error {

	// получаем текст для инициализации таблицы поиска
	sc, err := ioutil.ReadFile(flags.ExecutableDir + "/" + searchSchemaPath + "install.sql")
	sqlQuery := string(sc)

	// подставляем space_id в запрос
	sqlQuery = strings.Replace(sqlQuery, "{space_id}", functions.Int64ToString(spaceId), 1)

	// устанавливаем соединение с мантикорой
	connection, err := sql.Open("mysql", fmt.Sprintf("tcp(%s:%s)/", conf.GetShardingConfig().Manticore.Host, conf.GetShardingConfig().Manticore.Port))
	if err != nil {
		return err
	}

	// после выполнения закрываем соединение
	defer func() {

		err = connection.Close()
		if err != nil {
			log.Errorf("%v", err)
		}
	}()

	// выполняем запрос
	_, err = connection.Query(sqlQuery)
	if err != nil {
		return err
	}

	registry, err := port_registry.GetByCompany(ctx, spaceId)
	if err != nil {

		// если не нашли порт у пространства - возвращаем ошибку
		return fmt.Errorf("could not get space port, error: %v", err)
	}
	if registry == nil {

		// если не нашли регистри у пространства - возвращаем ошибку
		return fmt.Errorf("could not get space registry, error: %v", err)
	}

	// расшифровываем пользователя и пароль от базы
	user, err := registry.GetDecryptedMysqlUser()
	pass, err := registry.GetDecryptedMysqlPass()

	credentials := &sharding.DbCredentials{
		Host: company.GetCompanyHost(registry.Port),
		User: user,
		Pass: pass,
		Port: registry.Port,
	}

	logItem := &logger.Log{}

	// накатываем актуальную миграцию для таблиц мантикоры
	errMessage := migration.DoMigrateManticore(ctx, credentials, connection, spaceId, logItem)
	if errMessage != "" {
		return fmt.Errorf("could not migrated database, error: %s", errMessage)
	}

	return nil
}

// DropTable удаляем поисковый индекс пространства
func DropTable(spaceId int64) error {

	// подготавливаем SQL запрос
	sqlQuery := "DROP TABLE {index_name_prefix}_{space_id}"

	// подставляем параметры в запрос
	sqlQuery = strings.Replace(sqlQuery, "{index_name_prefix}", indexNamePrefix, 1)
	sqlQuery = strings.Replace(sqlQuery, "{space_id}", functions.Int64ToString(spaceId), 1)

	// устанавливаем соединение с мантикорой
	connection, err := sql.Open("mysql", fmt.Sprintf("tcp(%s:%s)/", conf.GetShardingConfig().Manticore.Host, conf.GetShardingConfig().Manticore.Port))
	if err != nil {
		return err
	}

	// после выполнения закрываем соединение
	defer func() {

		err = connection.Close()
		if err != nil {
			log.Errorf("%v", err)
		}
	}()

	// выполняем запрос
	_, err = connection.Query(sqlQuery)
	if err != nil {
		return err
	}

	return nil
}
