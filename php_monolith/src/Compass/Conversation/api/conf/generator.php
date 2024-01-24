<?php

namespace Compass\Conversation;

/**
 * Настройки генераторв.
 *
 * Генераторы сущесвтуют глобально на домино, используют внутреннее распределение по времени,
 * обеспечивая равномерную нагрузки генерируемыми событиями — условно говоря на значение period
 * будет равномерно «размазаны» все дейсвтующие компании.
 */
$CONFIG["GENERATOR"] = [

	/**
	 * Воркер для подготовки сущностей перед индексаций
	 * Работает с очередью space_search . entity_preparation_task_queue
	 *
	 * Основная задача – собрать список сущностей для индексации и передать в очередь следующему воркеру
	 */
	"search_entity_preparation_worker" => [
		"period"            => 60,
		"subscription_item" => [
			"trigger_type" => 5,
			"event"        => Type_Event_Search_EntityPreparationQueue::EVENT_TYPE,
			"extra"        => [
				"type"        => 2,
				"module"      => "php_conversation",
				"group"       => Type_Attribute_EventListener::INDEX_PREPARATION_GROUP,
				"error_limit" => 0
			],
		],
		"event_data"        => [],
	],

	/**
	 * Воркер для сохранения сущностей в поисковый индекс
	 * Работает с очередью space_search . index_task_queue
	 *
	 * Основная задача – сохранить сущность в поисковый индекс Manticore
	 */
	"search_index_filling_worker" => [
		"period"            => 60,
		"subscription_item" => [
			"trigger_type" => 5,
			"event"        => Type_Event_Search_IndexFillingQueue::EVENT_TYPE,
			"extra"        => [
				"type"        => 2,
				"module"      => "php_conversation",
				"group"       => Type_Attribute_EventListener::INDEX_GROUP,
				"error_limit" => 0
			],
		],
		"event_data"        => [],
	],
];

return $CONFIG;