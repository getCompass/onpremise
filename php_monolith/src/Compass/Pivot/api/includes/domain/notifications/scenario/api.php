<?php

namespace Compass\Pivot;

/**
 * Сценарии для домена уведомлений
 */
class Domain_Notifications_Scenario_Api {

	/**
	 * Открепить voip токен
	 *
	 * @param string $token
	 *
	 * @return void
	 * @throws \returnException
	 */
	public static function detachVoipToken(string $token):void {

		Domain_Notifications_Action_DetachVoipToken::do($token);
	}

}