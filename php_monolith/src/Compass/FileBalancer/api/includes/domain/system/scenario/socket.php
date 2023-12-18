<?php

namespace Compass\FileBalancer;

/**
 * Сценарии для домена
 */
class Domain_System_Scenario_Socket {

	/**
	 * Очистка блокировок, если передан user_id чистим только по пользователю
	 */
	public static function clearAntispam(int $user_id = 0):void {

		Type_Antispam_User::clearAll($user_id);
	}
}