<?php

namespace Compass\Company;

/**
 * класс для формирования события о смене роли участника пространства
 */
class Domain_Partner_Entity_Event_SpaceMemberRoleChanged extends Domain_Partner_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "partner.space_member_role_changed";

	/**
	 * Создаем событие
	 *
	 * @param int $space_id
	 * @param int $user_id
	 * @param int $role
	 *
	 * @throws \busException
	 */
	public static function create(int $space_id, int $user_id, int $role):void {

		$params = [
			"space_id"    => $space_id,
			"user_id"     => $user_id,
			"role"        => $role,
		];

		// отправляем в ПП
		static::_sendToPartner($params);
	}

}