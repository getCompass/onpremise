<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о регистрации пользователя, которое будет отправлено в premise-модуль
 */
class Domain_Premise_Entity_Event_UserRegistered extends Domain_Premise_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "premise.user_registered";

	/**
	 * Создаём событие
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id, int $npc_type, int $is_root):void {

		$params = [
			"user_id"  => $user_id,
			"npc_type" => $npc_type,
			"is_root"  => $is_root,
		];

		self::_sendToPremise($params);
	}
}