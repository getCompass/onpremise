<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * абстрактный класс, который наследует класс каждой версии websocket события
 */
abstract class Gateway_Bus_Sender_Event_Abstract {

	/** @var int название события */
	protected const _WS_EVENT = "";

	/** @var int версия события */
	protected const _WS_EVENT_VERSION = 0;

	/** @var array структура ws события */
	protected const _WS_DATA = [];

	/**
	 * подготавливаем структуру события
	 *
	 * @param array $ws_data
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws ParseFatalException
	 */
	protected static function _buildEvent(array $ws_data):Struct_SenderBalancer_Event {

		// если разработчик забыл поменять дефолтные значения
		if (static::_WS_EVENT === "" || static::_WS_EVENT_VERSION === 0) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected ws method version");
		}

		// проверяем, что структура отправляемых WS данных соответствует ожидаемой
		if (!\Entity_Validator_Structure::isExpectedStructure($ws_data, static::_WS_DATA)) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("ws data is not matching expected structure");
		}

		return new Struct_SenderBalancer_Event(static::_WS_EVENT, static::_WS_EVENT_VERSION, $ws_data);
	}

}