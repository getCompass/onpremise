<?php

namespace Compass\Company;

/**
 * класс для формирования события о вступлении участника в команду, которое будет отправлено в premise-модуль
 */
class Domain_Premise_Entity_Event_SpaceNewMember extends Domain_Premise_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "premise.space_new_member";

	/**
	 * Создаём событие
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function create(int $user_id, int $npc_type, int $role, int $permissions):void {

		$params = [
			"user_id"     => $user_id,
			"npc_type"    => $npc_type,
			"role"        => $role,
			"permissions" => $permissions,
			"space_id"    => \CompassApp\System\Company::getCompanyId(),
		];

		self::_sendToPremise($params);
	}
}