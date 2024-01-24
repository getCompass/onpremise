<?php

namespace Compass\FileNode;

/**
 * абстрактный класс-интерфейс для работы с сокетами
 */
abstract class Gateway_Socket_Default {

	// получаем подпись из массива параметров
	protected static function _doCall(string $url, string $method, array $params, int $user_id = 0, int $company_id = 0):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(
			Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_ME, $json_params
		);

		try {
			return Type_Socket_Main::doCall($url, $method, $json_params, $signature, $user_id, $company_id);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($company_id > 0 && $e->getHttpStatusCode() == 404) {
				throw new Gateway_Socket_Exception_CompanyIsNotServed("company is not served");
			}
			if ($company_id > 0 && $e->getHttpStatusCode() == 503) {
				throw new Gateway_Socket_Exception_CompanyIsHibernated("company is hibernated");
			}
			throw $e;
		}
	}

	/**
	 * получаем url
	 */
	protected static function _getSocketCompanyUrl(string $module, string $company_url):string {

		$socket_module_config = getConfig("SOCKET_MODULE");
		return $company_url . "/" . $socket_module_config[$module]["socket_path"];
	}

	/**
	 * получаем url
	 */
	protected static function _getSocketPivotUrl(string $module):string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["pivot"] . $socket_module_config[$module]["socket_path"];
	}
}
