<?php

namespace Compass\Pivot;

/**
 * Абстрактный класс описывающий любое событие, которое будет отправлено в пивот
 */
class Domain_Pivot_Entity_Event_Abstract {

	private const _EVENT_TYPE  = null;
	private const _MODULE_NAME = "php_pivot";

	##########################################################
	# region все для работы с полем event_data->params
	##########################################################

	/**
	 * Возвращаем пустую структуру params
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _initEventData():array {

		// если у унаследованного класса событий не инициализирована схема параметров
		if (static::_PARAMS_CURRENT_VERSION == 0 || is_null(static::_PARAMS_SCHEMA_LIST_BY_VERSION)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("inherited schema not initialized");
		}

		return static::_PARAMS_SCHEMA_LIST_BY_VERSION[static::_PARAMS_CURRENT_VERSION];
	}

	# endregion
	##########################################################

	/**
	 * Отправляем событие
	 *
	 * @param array $event_data
	 *
	 * @throws \busException
	 */
	public static function _send(array $event_data):void {

		Gateway_Bus_Event::pushTask(static::_EVENT_TYPE, $event_data, self::_MODULE_NAME);
	}
}