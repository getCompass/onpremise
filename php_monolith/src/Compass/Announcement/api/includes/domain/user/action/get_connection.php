<?php

namespace Compass\Announcement;

/**
 * Сценарии пользователя для API
 *
 * Class Domain_User_Scenario_Api
 */
class Domain_User_Action_GetConnection {

	/**
	 * Получить данные для соединения по WS
	 *
	 * @param int    $user_id
	 * @param string $device_id
	 * @param string $platform
	 *
	 * @return array
	 *
	 * @throws \busException
	 * @throws \parseException
	 */
	public static function getConnection(int $user_id, string $device_id, string $platform):array {

		return Gateway_Bus_SenderBalancer::getConnection($user_id, $device_id, $platform);
	}
}
