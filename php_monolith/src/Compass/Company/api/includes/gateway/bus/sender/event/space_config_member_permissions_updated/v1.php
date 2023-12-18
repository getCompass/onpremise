<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.space.config.member_permissions_updated версии 1
 */
class Gateway_Bus_Sender_Event_SpaceConfigMemberPermissionsUpdated_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.space.config.member_permissions_updated";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"member_permission_list" => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * Собираем объект ws события
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $member_permission_list):Struct_Sender_Event {

		return self::_buildEvent([
			"member_permission_list" => (object) $member_permission_list,
		]);
	}
}