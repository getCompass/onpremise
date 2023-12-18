<?php

namespace Compass\Pivot;

/**
 * Класс для работы с аналитикой присоединения нового пользователя к пространству
 */
class Type_Space_NewUserJoinedSpace {

	protected const _EVENT_KEY = "new_user_joined_space";

	/**
	 * Пишем аналитику по присоединению нового пользователя к пространству
	 */
	public static function send(int $user_id, int $registered_at, int $company_id, int $creator_user_id):void {

		// если зарегался больше 3-х дней назад - не шлем
		if (time() - $registered_at > DAY3) {
			return;
		}

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"user_id"         => $user_id,
			"registered_at"   => $registered_at,
			"created_at"      => time(),
			"company_id"      => $company_id,
			"creator_user_id" => $creator_user_id,
		]);
	}
}