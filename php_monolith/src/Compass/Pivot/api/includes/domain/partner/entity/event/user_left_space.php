<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о покидании пространства пользователем, которое будет отправлено в партнерку
 */
class Domain_Partner_Entity_Event_UserLeftSpace extends Domain_Partner_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "partner.user_left_space";

	/**
	 * Создаем событие
	 *
	 * @param int $user_id
	 * @param int $space_id
	 * @param int $leaving_role
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id, int $space_id, int $leaving_role):void {

		$params = [
			"user_id"      => $user_id,
			"space_id"     => $space_id,
			"leaving_role" => $leaving_role,
		];

		// отправляем в партнерское ядро
		self::_sendToPartner($params);
	}

}