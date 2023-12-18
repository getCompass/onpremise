<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о присоединении участника к пространству
 */
class Domain_Crm_Entity_Event_SpaceJoinMember extends Domain_Crm_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "crm.space_join_member";

	/**
	 * Создаем событие
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $space_id, int $user_id, int $role, int $permissions, int $joined_at):void {

		$params = [
			"space_id"    => $space_id,
			"user_id"     => $user_id,
			"role"        => $role,
			"permissions" => $permissions,
			"joined_at"   => $joined_at,
		];

		// отправляем в crm
		static::_sendToCrm($params);
	}

}