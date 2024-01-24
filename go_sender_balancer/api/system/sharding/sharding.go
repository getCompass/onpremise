package sharding

import (
	"github.com/getCompassUtils/go_base_frame/api/system/rabbit"
	"go_sender_balancer/api/conf"
)

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
