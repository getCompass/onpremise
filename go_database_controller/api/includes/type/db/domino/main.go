package domino

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"go_database_controller/api/conf"
	"go_database_controller/api/system/sharding"
	"io/ioutil"
	"strings"
)

// MigrateInit исполняем все миграции для приложения
func MigrateInit(ctx context.Context, dir string) error {

	mysqlConf := conf.GetShardingConfig().Mysql["domino_service"].Mysql
	credentials := &sharding.DbCredentials{
		Host: strings.Split(mysqlConf.Host, ":")[0],
		Port: functions.StringToInt32(strings.Split(mysqlConf.Host, ":")[1]),
		User: mysqlConf.User,
		Pass: mysqlConf.Pass,
	}

	// читаем все файлы из sql папки
	sqlDir := dir + "/sql/"
	files, err := ioutil.ReadDir(sqlDir)

	if err != nil {
		return err
	}

	// для каждого файла считываем содержимое файла и исполняем из него запросы
	for _, file := range files {

		if file.IsDir() || !strings.HasSuffix(file.Name(), ".sql") {
			continue
		}

		err = execSql(ctx, credentials, "", sqlDir+file.Name())
		if err != nil {
			return err
		}
	}

	return nil
}

// выполняем sql запросы
func execSql(ctx context.Context, credentials *sharding.DbCredentials, dbKey string, sqlFile string) (err error) {

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
