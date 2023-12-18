<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс для общения с go_userbot_cache через сокет
 */
class Gateway_Socket_UserbotCache extends Gateway_Socket_Default {

	/**
	 * чистим инфу бота из кэша
	 *
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function clearUserbotCache(string $token):void {

		$ar_post = [
			"token" => $token,
		];
		[$status, $_] = self::_call("userbot.clear", $ar_post);

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем вызов
	 *
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _call(string $method, array $params):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url = self::_getUrl();

		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_USERBOT_CACHE, $json_params);
		try {
			return Type_Socket_Main::doCall($url, $method, $json_params, $signature);
		} catch (\cs_SocketRequestIsFailed $e) {
			throw $e;
		}
	}

	/**
	 * получаем ссылку для запроса
	 */
	protected static function _getUrl():string {

		$socket_module_config = getConfig("SOCKET_URL");
		$socket_path_config   = getConfig("SOCKET_MODULE");

		return $socket_module_config["userbot"] . $socket_path_config["userbot_cache"]["socket_path"];
	}
}