<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Абстрактный класс описывающий любое событие, которое будет отправлено в партнерку
 */
class Domain_Partner_Entity_Event_Abstract {

	/**
	 * Должен переопределяться каждым событием и содержать строковый тип события
	 */
	protected const _PARTNER_EVENT_TYPE = null;

	private const _GO_EVENT_TYPE       = "partner_web.send_task";
	private const _PARTNER_MODULE_NAME = "php_partner_web";

	##########################################################
	# region структура поля event_data
	##########################################################

	/**
	 * Схема структуры поля event_data
	 */
	private const _EVENT_DATA_CURRENT_VERSION        = 1;
	private const _EVENT_DATA_SCHEMA_LIST_BY_VERSION = [
		1 => [
			"type"   => "",
			"params" => [],
		],
	];

	/**
	 * Возвращаем структуру event_data
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	protected static function _initEventData(array $params):array {

		$event_data = self::_EVENT_DATA_SCHEMA_LIST_BY_VERSION[self::_EVENT_DATA_CURRENT_VERSION];

		$event_data["version"] = self::_EVENT_DATA_CURRENT_VERSION;
		$event_data["type"]    = static::_PARTNER_EVENT_TYPE;
		$event_data["params"]  = $params;

		return $event_data;
	}

	# endregion
	##########################################################

	##########################################################
	# region все для работы с полем event_data->params
	##########################################################

	/**
	 * Каждый класс события должен переопределять эти константы
	 */
	protected const _PARAMS_CURRENT_VERSION        = 0;
	protected const _PARAMS_SCHEMA_LIST_BY_VERSION = null;

	/**
	 * Возвращаем пустую структуру params
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _initParams():array {

		// если у унаследованного класса событий не инициализирована схема параметров
		if (static::_PARAMS_CURRENT_VERSION == 0 || is_null(static::_PARAMS_SCHEMA_LIST_BY_VERSION)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("inherited schema not initialized");
		}

		$output            = static::_PARAMS_SCHEMA_LIST_BY_VERSION[static::_PARAMS_CURRENT_VERSION];
		$output["version"] = static::_PARAMS_CURRENT_VERSION;

		return $output;
	}

	# endregion
	##########################################################

	/**
	 * Отправляем событие в партнерское ядро
	 *
	 * @param array $event_data
	 *
	 * @throws \busException
	 */
	protected static function _sendToPartner(array $event_data):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		// на тестовых пока не шлем, там нет партнерки
		if (isTestServer()) {
			return;
		}

		// ничего не шлем пока не релизнем новую партнерку
		if (!IS_PARTNER_WEB_ENABLED) {
			return;
		}

		Gateway_Bus_Event::pushTask(self::_GO_EVENT_TYPE, $event_data, self::_PARTNER_MODULE_NAME);
	}
}