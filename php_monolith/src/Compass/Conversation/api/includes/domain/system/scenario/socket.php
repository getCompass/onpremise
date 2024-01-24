<?php

namespace Compass\Conversation;

/**
 * Сценарии для домена
 */
class Domain_System_Scenario_Socket {

	/**
	 * Очистка блокировок, если передан пользователь, то только блокировки пользователя
	 */
	public static function clearAntispam(int $user_id = 0):void {

		Type_Antispam_User::clearAll($user_id);
	}
}