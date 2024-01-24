<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;

/**
 * Класс описывающий событие event.user_company.role_changed версии 1
 */
class Gateway_Bus_Sender_Event_UserRoleChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.user_company.role_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"user_id"            => \Entity_Validator_Structure::TYPE_INT,
		"role"               => \Entity_Validator_Structure::TYPE_INT,
		"role_name"          => \Entity_Validator_Structure::TYPE_STRING,
		"is_owner_pretender" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $user_id, int $role):Struct_Sender_Event {

		return self::_buildEvent([
			"user_id"            => (int) $user_id,
			"role"               => (int) $role,
			"role_name"          => (string) Member::getRoleOutputType($role),
			"is_owner_pretender" => (int) 0,
		]);
	}
}