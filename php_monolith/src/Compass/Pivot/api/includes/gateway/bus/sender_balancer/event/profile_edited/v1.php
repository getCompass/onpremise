<?php

namespace Compass\Pivot;

/**
 * класс описывающий событие event.profile_edited версии 1
 */
class Gateway_Bus_SenderBalancer_Event_ProfileEdited_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.profile_edited";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"user" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $user):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"user" => (object) $user,
		]);
	}
}