package sharding

import (
	"context"
	"fmt"
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"github.com/getCompassUtils/go_base_frame/api/system/rabbit"
	"go_event/api/conf"
)

// получаем mysql подключение из хранилища
func Mysql(dbKey string) *mysql.ConnectionPoolItem {

	db := conf.GetShardingConfig().Mysql[dbKey].Db
	mysqlConf := conf.GetShardingConfig().Mysql[dbKey].Mysql
	mysqlConnectionHost := fmt.Sprintf("%s:%d", mysqlConf.Host, mysqlConf.Port)
	mysqlConnectionPoolItem, err := mysql.GetMysqlConnection(context.Background(), db, mysqlConnectionHost, mysqlConf.User, mysqlConf.Pass,
		mysqlConf.MaxConnections, mysqlConf.Ssl)

	if err != nil {

		log.Infof("open database connection error, err: %s", err)
		return nil
	}

	return mysqlConnectionPoolItem
}

// получаем rabbit подключение из хранилища
func Rabbit(key string) *rabbit.ConnectionStruct {

	// получаем значение из кэша
	connectionItem, isExist := rabbit.GetRabbitConnection(key)
	if !isExist {

		// открываем соединение
		rabbitConf := conf.GetShardingConfig().Rabbit[key]
		connectionItem, err := rabbit.OpenRabbitConnection(key, rabbitConf.User, rabbitConf.Pass, rabbitConf.Host, rabbitConf.Port)
		if err != nil {

			return nil
		}

		rabbit.UpdateRabbitConnection(key, connectionItem)
		return connectionItem
	}

	return connectionItem
}
