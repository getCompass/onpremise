<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о покидании участником пространства
 */
class Domain_Crm_Entity_Event_SpaceLeaveMember extends Domain_Crm_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "crm.space_leave_member";

	/**
	 * Создаем событие
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $space_id, int $user_id, int $leaving_role):void {

		$params = [
			"space_id"     => $space_id,
			"user_id"      => $user_id,
			"leaving_role" => $leaving_role,
		];

		// отправляем в crm
		static::_sendToCrm($params);
	}

}