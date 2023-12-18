<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.profile_card_permissions_changed версии 1
 */
class Gateway_Bus_Sender_Event_ProfileCardPermissionsChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.member.profile_card_permissions_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"permissions" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @param array $permissions
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $permissions):Struct_Sender_Event {

		return self::_buildEvent([
			"permissions" => (object) $permissions,
		]);
	}
}