package GlobalIsolation

import (
	"github.com/getCompassUtils/go_base_frame/api/system/log"
	"github.com/getCompassUtils/go_base_frame/api/system/rabbit"
	"go_sender/api/conf"
	"go_sender/api/includes/type/balancer"
)

/** Пакет описывает сущность изоляции исполнения внутри модуля
  Изоляция может быть связана с компанией или быть глобальной для сервиса **/

// GlobalIsolation структура контекста компании
type GlobalIsolation struct {
	config            *conf.ConfigStruct
	shardingConfig    *conf.ShardingStruct
	BalancerConn      *balancer.Conn
	EmptyBalancerConn *balancer.Conn
}

// GetConfig возвращает ид компании для изоляции
func (i *GlobalIsolation) GetConfig() *conf.ConfigStruct {

	return i.config
}

// GetShardingConfig возвращает ид компании для изоляции
func (i *GlobalIsolation) GetShardingConfig() *conf.ShardingStruct {

	return i.shardingConfig
}

// MakeGlobalIsolationIsolation возвращает новую локальную изоляцию сервиса
// создать глобальную изоляцию самостоятельно нельзя
func MakeGlobalIsolationIsolation(config *conf.ConfigStruct, shardingConfig *conf.ShardingStruct) *GlobalIsolation {

	rabbitConf := shardingConfig.Rabbit["local"]
	rabbitConn, err := rabbit.OpenRabbitConnection("local", rabbitConf.User, rabbitConf.Pass, rabbitConf.Host, rabbitConf.Port)

	if err != nil {
		log.Errorf(err.Error())
	}

	context := GlobalIsolation{
		config:            config,
		shardingConfig:    shardingConfig,
		BalancerConn:      balancer.MakeBalancerConn(config.RabbitSenderBalancerQueue, config.NodeId, config.IsHasBalancer, rabbitConn),
		EmptyBalancerConn: balancer.MakeBalancerConn("", 0, false, nil),
	}

	return &context
}
