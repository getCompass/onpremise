<?php

namespace Compass\Company;

/**
 * Абстрактный класс для работы с генераторами событий.
 */
class Type_Generator_Abstract {

	public const GENERATOR_TYPE = "";

	public const GENERATOR_OPTIONS = [
		"period"            => 30 + (COMPANY_ID % 100),                            // частота генерации событий в секундах
		"subscription_item" => [
			"trigger_type" => 5,
			"event"        => "",                                                // событие которое выбрасывает генератор
			"extra"        => [
				"type"        => 2,
				"module"      => "php_" . CURRENT_MODULE,                      // модуль получатель события
				"group"       => Type_Attribute_EventListener::DEFAULT_GROUP,  // метод обработчик события
				"error_limit" => 0                                             // до победного
			],
		],
		"event_data"        => []                                                  // дополнительные данные события
	];

	/**
	 * Требуется ли пропустить добавление генератора.
	 */
	public static function isSkipAdding():bool {

		return false;
	}

	/**
	 * Получить параметры генератора событий.
	 */
	public static function getOptions():array {

		return self::GENERATOR_OPTIONS;
	}
}