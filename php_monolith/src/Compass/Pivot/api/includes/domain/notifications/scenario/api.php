<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

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

	/**
	 * Подтверждаем получение пушей
	 */
	public static function confirmPushReceiving(array $uuid_list):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		$push_list = [];
		foreach ($uuid_list as $uuid) {

			if (!checkUuid($uuid)) {
				continue;
			}

			$push_list[$uuid] = [
				"uuid"               => $uuid,
				"user_id"            => 0,
				"event_time"         => time(),
				"event_type"         => Domain_Analytic_Entity_PushHistory::CONFIRM_PUSH_RECEIVING,
				"device_id"          => getDeviceId(),
				"token_hash"         => "",
				"token_type_service" => 0,
				"push_id"            => "",
				"push_response"      => 0,
			];
		}

		if (count($push_list) < 1) {
			return;
		}

		Gateway_Bus_CollectorAgent::init()->saveAnalyticsPush($push_list);
	}
}