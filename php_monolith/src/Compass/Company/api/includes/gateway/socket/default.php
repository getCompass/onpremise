<?php

namespace Compass\Company;

/**
 * абстрактный класс-интерфейс для работы с сокетами
 */
abstract class Gateway_Socket_Default {

	// получаем подпись из массива параметров
	protected static function _doCall(string $url, string $method, array $params, int $user_id = 0, int $company_id = COMPANY_ID):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = \BaseFrame\Socket\Authorization\Handler::getSignature(
			\BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY, SOCKET_KEY_COMPANY, $json_params
		);

		return \BaseFrame\Socket\Main::doCall($url, $method, $json_params, $signature, CURRENT_MODULE, $company_id, $user_id);
	}

	/**
	 * получаем url
	 */
	protected static function _getSocketCompanyUrl(string $module):string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["company"] . $socket_module_config[$module]["socket_path"];
	}

	/**
	 * получаем url
	 */
	protected static function _getSocketPivotUrl(string $module):string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["pivot"] . $socket_module_config[$module]["socket_path"];
	}

	/**
	 * Получаем url
	 */
	protected static function _getSocketIntercomUrl(string $module):string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["intercom"] . $socket_module_config[$module]["socket_path"];
	}
}
