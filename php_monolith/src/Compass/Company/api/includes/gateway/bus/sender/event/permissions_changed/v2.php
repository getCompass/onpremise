<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.permissions_changed версии 2
 */
class Gateway_Bus_Sender_Event_PermissionsChanged_V2 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.member.permissions_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 2;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"user_id"     => \Entity_Validator_Structure::TYPE_INT,
		"role"        => \Entity_Validator_Structure::TYPE_STRING,
		"permissions" => [
			"group_administrator"              => \Entity_Validator_Structure::TYPE_INT,
			"bot_management"                   => \Entity_Validator_Structure::TYPE_INT,
			"message_delete"                   => \Entity_Validator_Structure::TYPE_INT,
			"member_profile_edit"              => \Entity_Validator_Structure::TYPE_INT,
			"member_invite"                    => \Entity_Validator_Structure::TYPE_INT,
			"member_kick"                      => \Entity_Validator_Structure::TYPE_INT,
			"space_settings"                   => \Entity_Validator_Structure::TYPE_INT,
			"administrator_management"         => \Entity_Validator_Structure::TYPE_INT,
			"administrator_statistic_infinite" => \Entity_Validator_Structure::TYPE_INT,
		],
	];

	/**
	 * собираем объект ws события
	 *
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 * @param int $permission_output_version
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $user_id, int $role, int $permissions, int $permission_output_version):Struct_Sender_Event {

		return self::_buildEvent(Apiv2_Format::permissions($user_id, $role, $permissions, $permission_output_version));
	}
}