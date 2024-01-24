<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * задача класса общаться между php_userbot -> php_pivot
 */
class Gateway_Socket_Pivot {

	/**
	 * установить версию webhook бота
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setWebhookVersion(string $userbot_id, string $token, int $version):void {

		// формируем параметры для запроса
		$params = [
			"userbot_id" => $userbot_id,
			"token"      => $token,
			"version"    => $version,
		];

		[$status, $response] = self::_call("userbot.setWebhookVersion", $params);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("not found error_code");
			}

			if ($response["error_code"] == 1434001) {
				throw new \cs_Userbot_RequestIncorrect("passed incorrect webhook version");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем вызов в модуль
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _call(string $method, array $params):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, GLOBAL_USERBOT_PRIVATE_KEY, $json_params);
		return Type_Socket_Main::doCall(self::_getUrl(), $method, $json_params, $signature);
	}

	/**
	 * получаем url
	 */
	protected static function _getUrl():string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["pivot"] . $socket_module_config["pivot"]["socket_path"];
	}
}
