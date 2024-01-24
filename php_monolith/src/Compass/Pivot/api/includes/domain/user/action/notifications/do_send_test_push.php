<?php

namespace Compass\Pivot;

/**
 * Отправляем тестовый пуш
 */
class Domain_User_Action_Notifications_DoSendTestPush {

	/**
	 * Отправляем тестовый пуш
	 *
	 * @param int    $user_id
	 * @param string $push_type
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \busException
	 * @throws cs_UserNotHaveToken
	 * @throws \parseException
	 */
	public static function do(int $user_id, string $push_type = "TEST_MESSAGE"):void {

		$device_id = getDeviceId();

		if (!checkUuid($device_id)) {
			throw new \BaseFrame\Exception\Request\ParamException("invalid device id");
		}

		$token_item = Type_User_Notifications::getTokenByDeviceId(getDeviceId(), getUa());

		// шлем пуш
		Gateway_Bus_Pusher::sendTestPush($user_id, $token_item, $push_type);
	}
}