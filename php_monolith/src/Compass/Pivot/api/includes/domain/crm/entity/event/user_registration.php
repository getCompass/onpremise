<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о регистрации нового пользователя
 */
class Domain_Crm_Entity_Event_UserRegistration extends Domain_Crm_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "crm.user_registration";

	/**
	 * Создаем событие
	 *
	 * @param int $user_id
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id):void {

		$params = [
			"user_id" => $user_id,
		];

		// отправляем в crm
		static::_sendToCrm($params);
	}

}