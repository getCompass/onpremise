<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для работы с модулём userbot
 */
class Gateway_Socket_Userbot {

	/**
	 * метод для отправки команды боту на внешний сервис
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function sendCommand(string $command, string $token, string $webhook, int $user_id, string $message_key, string $conversation_key):void {

		$params = [
			"command"    => $command,
			"token"      => $token,
			"webhook"    => $webhook,
			"user_id"    => $user_id,
			"message_id" => $message_key,
			"group_id"   => $conversation_key,
		];
		[$status] = self::_call("userbot.sendCommand", $params, 0);
		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем подпись из массива параметров.
	 *
	 * @param string $method
	 * @param array  $params
	 * @param int    $user_id
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _call(string $method, array $params, int $user_id):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url       = self::_getUrl();
		$signature = \BaseFrame\Socket\Authorization\Handler::getSignature(
			\BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY, COMPANY_USERBOT_PRIVATE_KEY, $json_params
		);

		return \BaseFrame\Socket\Main::doCall($url, $method, $json_params, $signature, CURRENT_MODULE, COMPANY_ID, $user_id);
	}

	/**
	 * получаем url
	 *
	 * @return string
	 */
	protected static function _getUrl():string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["userbot"] . $socket_module_config["userbot"]["socket_path"];
	}
}
