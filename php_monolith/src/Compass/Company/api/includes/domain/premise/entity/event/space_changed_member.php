<?php

namespace Compass\Company;

/**
 * класс для формирования события изменения роли/прав участника команды, которое будет отправлено в premise-модуль
 */
class Domain_Premise_Entity_Event_SpaceChangedMember extends Domain_Premise_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "premise.space_changed_member";

	/**
	 * Создаём событие
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function create(int $user_id, int $role, int $permissions):void {

		$params = [
			"user_id"     => $user_id,
			"role"        => $role,
			"permissions" => $permissions,
			"space_id"    => \CompassApp\System\Company::getCompanyId(),
		];

		self::_sendToPremise($params);
	}
}