<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для работы с модулем integration
 */
class Gateway_Socket_Integration extends Gateway_Socket_Default {

	/**
	 * уведомление при регистрации нового пользователя
	 *
	 * @return Struct_Integration_Notifier_Response_OnUserRegistered
	 * @throws ReturnFatalException
	 */
	public static function onUserRegistered(Struct_Integration_Notifier_Request_OnUserRegistered $data):Struct_Integration_Notifier_Response_OnUserRegistered {

		$ar_post = $data->formatNotifyParameters();
		[$status, $response] = self::_doCallSocket("notifier.onUserRegistered", $ar_post);

		if ($status === "error" || !isset($response["action_list"])) {
			throw new ReturnFatalException("wrong response");
		}

		return Struct_Integration_Notifier_Response_OnUserRegistered::build($response["action_list"]);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// выполняем socket запрос
	protected static function _doCallSocket(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketIntegrationUrl();
		return self::_doCall($url, $method, $params, $user_id);
	}
}
