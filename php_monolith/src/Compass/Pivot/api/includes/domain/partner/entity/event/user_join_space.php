<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о присоединении к пространству пользователя, которое будет отправлено в партнерку
 */
class Domain_Partner_Entity_Event_UserJoinSpace extends Domain_Partner_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "partner.user_join_space";

	/**
	 * Создаем событие
	 *
	 * @param int $user_id
	 * @param int $space_id
	 * @param int $user_registered_at
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id, int $space_id, int $role, int $joined_at):void {

		$params = [
			"user_id"   => $user_id,
			"space_id"  => $space_id,
			"role"      => $role,
			"joined_at" => $joined_at,
		];

		// отправляем в партнерское ядро
		static::_sendToPartner($params);
	}

}