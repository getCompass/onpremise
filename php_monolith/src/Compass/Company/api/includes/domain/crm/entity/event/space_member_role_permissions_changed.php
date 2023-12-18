<?php

namespace Compass\Company;

/**
 * класс для формирования события о смене роли/прав участника пространства
 */
class Domain_Crm_Entity_Event_SpaceMemberRolePermissionsChanged extends Domain_Crm_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "crm.space_member_role_permissions_changed";

	/**
	 * Создаем событие
	 *
	 * @param int $space_id
	 * @param int $user_id
	 * @param int $role
	 * @param int $permissions
	 *
	 * @throws \busException
	 */
	public static function create(int $space_id, int $user_id, int $role, int $permissions):void {

		$params = [
			"space_id"    => $space_id,
			"user_id"     => $user_id,
			"role"        => $role,
			"permissions" => $permissions,
		];

		// отправляем в crm
		static::_sendToCrm($params);
	}

}