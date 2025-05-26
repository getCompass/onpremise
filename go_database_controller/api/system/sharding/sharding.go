package sharding

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/functions"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_database_controller/api/conf"
)

type DbCredentials struct {
	Host string `json:"host"`
	Port int32  `json:"port"`
	User string `json:"user"`
	Pass string `json:"pass"`
}

// получаем mysql подключение из хранилища
func Mysql(ctx context.Context, dbKey string) *mysql.ConnectionPoolItem {

	db := conf.GetShardingConfig().Mysql[dbKey].Db
	mysqlConf := conf.GetShardingConfig().Mysql[dbKey].Mysql
	mysqlConnectionPoolItem, err := mysql.GetMysqlConnection(ctx, db, mysqlConf.Host, mysqlConf.User, mysqlConf.Pass, mysqlConf.MaxConnections, mysqlConf.Ssl)

	err = mysqlConnectionPoolItem.Ping()
	if err != nil {

		log.Infof("Ping error, err: %s", err)
		return nil
	}

	return mysqlConnectionPoolItem
}

// получаем mysql подключение из хранилища
func MysqlWithCredentials(ctx context.Context, dbKey string, credentials *DbCredentials) *mysql.ConnectionPoolItem {

	if ctx == nil {
		return nil
	}

	if credentials == nil {
		return nil
	}

	mysqlConnectionPoolItem, err := mysql.GetMysqlConnection(ctx, dbKey, credentials.Host+":"+functions.IntToString(int(credentials.Port)),
		credentials.User, credentials.Pass, 10, false)

	if err != nil {
		return nil
	}

	if mysqlConnectionPoolItem == nil {
		log.Errorf("mysqlConnectionPoolItem is nil after GetMysqlConnection")
		return nil
	}

	err = mysqlConnectionPoolItem.Ping()
	if err != nil {

		// пытаемся переподключиться - вдруг изменилось подключение
		mysqlConnectionPoolItem, err = mysql.GetMysqlConnection(ctx, dbKey, credentials.Host+":"+functions.IntToString(int(credentials.Port)),
			credentials.User, credentials.Pass, 10, false)

		if err != nil {
			return nil
		}

		if mysqlConnectionPoolItem == nil {
			log.Errorf("mysqlConnectionPoolItem is nil after GetMysqlConnection")
			return nil
		}

		// снова пингуем - должны были подключиться
		err = mysqlConnectionPoolItem.Ping()
		if err != nil {

			log.Errorf("cant connect to db %s, %s", dbKey, err.Error())
			return nil
		}
	}

	return mysqlConnectionPoolItem
}
