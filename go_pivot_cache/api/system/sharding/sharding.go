package sharding

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"github.com/getCompassUtils/go_base_frame/api/system/rabbit"
	"go_pivot_cache/api/conf"
)

// получаем mysql подключение из хранилища
func Mysql(ctx context.Context, dbKey string) *mysql.ConnectionPoolItem {

	db := conf.GetShardingConfig().Mysql[dbKey].Db
	mysqlConf := conf.GetShardingConfig().Mysql[dbKey].Mysql
	mysqlConnectionPoolItem, err := mysql.GetMysqlConnection(ctx, db, mysqlConf.Host, mysqlConf.User, mysqlConf.Pass,
		mysqlConf.MaxConnections, mysqlConf.Ssl)
	if err != nil {
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
