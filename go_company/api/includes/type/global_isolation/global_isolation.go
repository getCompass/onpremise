package GlobalIsolation

import (
	"go_company/api/conf"
)

/** Пакет описывает сущность изоляции исполнения внутри модуля
  Изоляция может быть связана с компанией или быть глобальной для сервиса **/

// GlobalIsolation структура контекста компании
type GlobalIsolation struct {
	config         *conf.ConfigStruct
	shardingConfig *conf.ShardingStruct
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

	context := GlobalIsolation{
		config:         config,
		shardingConfig: shardingConfig,
	}

	return &context
}
