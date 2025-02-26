package sharding

import (
	"context"
	"github.com/getCompassUtils/go_base_frame/api/system/mysql"
	"go_activity/api/conf"
)

// Mysql получаем mysql подключение из хранилища
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
