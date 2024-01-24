<?php

namespace Compass\Announcement;

/**
 * Сценарии пользователя для API
 *
 * Class Domain_User_Scenario_Api
 */
class Domain_User_Scenario_Api {

	/**
	 * Получить данные для соединения по WS
	 *
	 * @param int $user_id
	 *
	 * @return array
	 *
	 * @throws cs_PlatformNotFound
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function getConnection(int $user_id):array {

		return Domain_User_Action_GetConnection::getConnection($user_id, getDeviceId(), Type_Api_Platform::getPlatform());
	}
}
