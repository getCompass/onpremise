<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о регистрации пользователя, которое будет отправлено в партнерку
 */
class Domain_Partner_Entity_Event_UserRegistered extends Domain_Partner_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "partner.user_registered";

	/**
	 * Создаем событие
	 *
	 * @param int    $user_id
	 * @param string $source_type
	 * @param array  $source_extra
	 * @param int    $registered_at
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id):void {

		$params = [
			"user_id" => $user_id,
		];

		// отправляем в партнерское ядро
		self::_sendToPartner($params);
	}

}