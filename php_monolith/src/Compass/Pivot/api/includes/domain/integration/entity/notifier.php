<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/** класс для отправки уведомлений в модуль интеграции */
class Domain_Integration_Entity_Notifier {

	public const ACTION_SEND_REQUEST_TO_JOIN_SPACE = "send_request_to_join_space";
	public const ACTION_JOIN_SPACE                 = "join_space";
	public const ACTION_UPDATE_USER_PROFILE        = "update_user_profile";

	/**
	 * отправляем уведомление при регистрации нового пользователя
	 */
	public static function onUserRegistered(Struct_Integration_Notifier_Request_OnUserRegistered $data):?Struct_Integration_Notifier_Response_OnUserRegistered {

		// если нет интеграции, то ничего не отправляем
		if (!ServerProvider::isIntegration()) {
			return null;
		}

		return Gateway_Socket_Integration::onUserRegistered($data);
	}
}