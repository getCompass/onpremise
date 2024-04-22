<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс описывающий событие event.permissions_changed версии 1
 */
class Gateway_Bus_Sender_Event_PermissionsChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.premise_user.premise_permissions_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"premise_user_id"         => \Entity_Validator_Structure::TYPE_INT,
		"premise_permission_list" => \Entity_Validator_Structure::TYPE_ARRAY,
	];

	/**
	 * собираем объект ws события
	 *
	 * @param int $user_id
	 * @param int $permissions
	 *
	 * @return Struct_SenderBalancer_Event
	 * @throws ParseFatalException
	 */
	public static function makeEvent(int $user_id, int $permissions):Struct_SenderBalancer_Event {

		return self::_buildEvent(Apiv2_Format::permissions($user_id, $permissions));
	}
}