<?php

namespace Compass\Pivot;

/**
 * класс для формирования события ою удалении профиля пользователя, которое будет отправлено в premise-модуль
 */
class Domain_Premise_Entity_Event_UserProfileDeleted extends Domain_Premise_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "premise.user_profile_deleted";

	/**
	 * Создаём событие
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id):void {

		$params = [
			"user_id"  => $user_id,
		];

		self::_sendToPremise($params);
	}
}