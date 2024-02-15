<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о создании пространства пользователем, которое будет отправлено в партнерку
 */
class Domain_Partner_Entity_Event_UserCreateSpace extends Domain_Partner_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "partner.user_create_space";

	/**
	 * Создаем событие
	 *
	 * @param int $user_id
	 * @param int $space_id
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id, int $space_id):void {

		$params = [
			"user_id"  => $user_id,
			"space_id" => $space_id,
		];

		// отправляем в партнерское ядро
		static::_sendToPartner($params);
	}

}