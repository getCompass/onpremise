<?php

namespace Compass\Pivot;

/**
 * класс описывающий событие event.company.status_rejected версии 1
 */
class Gateway_Bus_SenderBalancer_Event_CompanyStatusRejected_V1 extends Gateway_Bus_SenderBalancer_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.company.status_rejected";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"company_id"              => \Entity_Validator_Structure::TYPE_INT,
		"inviter_user_id"         => \Entity_Validator_Structure::TYPE_INT,
		"inviter_full_name"       => \Entity_Validator_Structure::TYPE_STRING,
		"inviter_avatar_file_key" => \Entity_Validator_Structure::TYPE_STRING,
		"inviter_avatar_color"    => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @param int    $company_id
	 * @param string $company_name
	 * @param int    $inviter_user_id
	 * @param string $inviter_full_name
	 * @param string $inviter_avatar_file_key
	 * @param string $inviter_avatar_color
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $company_id, string $company_name, int $inviter_user_id, string $inviter_full_name, string $inviter_avatar_file_key, string $inviter_avatar_color):Struct_SenderBalancer_Event {

		return self::_buildEvent([
			"company_id"              => (int) $company_id,
			"company_name"            => (string) $company_name,
			"inviter_user_id"         => (int) $inviter_user_id,
			"inviter_full_name"       => (string) $inviter_full_name,
			"inviter_avatar_file_key" => (string) $inviter_avatar_file_key,
			"inviter_avatar_color"    => (string) $inviter_avatar_color,
		]);
	}
}