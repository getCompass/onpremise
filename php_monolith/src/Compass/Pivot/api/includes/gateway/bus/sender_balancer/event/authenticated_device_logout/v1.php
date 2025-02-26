<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывающий событие action.authenticated_device_logout версии 1
 */
class Gateway_Bus_SenderBalancer_Event_AuthenticatedDeviceLogout_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.authenticated_device_logout";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"device_id_list" => \Entity_Validator_Structure::TYPE_ARRAY,
	];

	/**
	 * собираем объект ws события
	 *
	 * @throws ParseFatalException
	 */
	public static function makeEvent(array $device_id_list):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"device_id_list" => $device_id_list,
		]);
	}
}