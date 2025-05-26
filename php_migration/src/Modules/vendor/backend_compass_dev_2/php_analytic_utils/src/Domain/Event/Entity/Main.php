<?php

namespace AnalyticUtils\Domain\Event\Entity;

/**
 * Класс сущности событий
 */
class Main {

	// тип сущности - в родительском классе это общее событие
	protected const _ENTITY_TYPE = "general";

	// известные события с типом
	public const EVENT_SETTINGS_LIST = [];

	public const STATUS_SUCCESS = "success"; // успешный статус события
	public const STATUS_FAIL    = "fail"; // событие закончилось неудачей

	// доступные типы статусов
	public const ALLOWED_STATUS_LIST = [
		self::STATUS_FAIL    => 0,
		self::STATUS_SUCCESS => 1,
	];

	// типы статусом для конвертации в строковое название
	public const STATUS_TO_STRING_CAST_LIST = [
		0 => self::STATUS_FAIL,
		1 => self::STATUS_SUCCESS,
	];

	/**
	 * Выключено ли событие
	 *
	 * @param int   $event_type
	 * @param array $disabled_analytics_event_group_list
	 *
	 * @return bool
	 */
	public static function isDisabled(int $event_type, array $disabled_analytics_event_group_list):bool {

		return in_array(static::EVENT_SETTINGS_LIST[$event_type]["group"], $disabled_analytics_event_group_list);
	}
}