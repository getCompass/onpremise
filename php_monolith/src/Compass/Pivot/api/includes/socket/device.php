<?php

namespace Compass\Pivot;

/**
 * Контроллер для работы с устройствами пользователя
 */
class Socket_Device extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"getDeviceLoginHistory",
	];

	/**
	 * Получить список истории логина/разлогина устройств пользователя
	 */
	public function getDeviceLoginHistory():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		$list = Domain_User_Scenario_Socket::getDeviceLoginHistory($user_id);

		return $this->ok([
			"list" => (array) $list,
		]);
	}
}