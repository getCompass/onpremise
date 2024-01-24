<?php

namespace Compass\Pivot;

/**
 * абстрактный класс-интерфейс для работы с сокетами
 */
abstract class Gateway_Socket_Default {

	// получаем подпись из массива параметров
	protected static function _doCall(string $url, string $method, array $params, int $company_id = 0, int $user_id = 0):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(
			Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_PIVOT, $json_params
		);

		return Type_Socket_Main::doCall($url, $method, $json_params, $signature, $company_id, $user_id);
	}

	/**
	 * Получаем url
	 */
	protected static function _getSocketIntercomUrl():string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["intercom"] . $socket_module_config["intercom"]["socket_path"];
	}

	/**
	 * Получаем url
	 */
	protected static function _getSocketFilesUrl():string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["pivot"] . $socket_module_config["files"]["socket_path"];
	}

	/**
	 * Получаем url
	 */
	protected static function _getSocketCollectorUrl():string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["collector_server"] . $socket_module_config["collector"]["socket_path"];
	}
}
