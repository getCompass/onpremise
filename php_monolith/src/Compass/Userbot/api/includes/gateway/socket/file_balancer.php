<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * задача класса общаться между php_userbot -> php_file_balancer
 */
class Gateway_Socket_FileBalancer {

	/**
	 * получить данные по файловой ноде
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getNodeUrl(int $userbot_user_id, string $company_url):array {

		// формируем параметры для запроса
		$params = [
			"userbot_user_id" => $userbot_user_id,
		];

		[$status, $response] = self::_call("files.getNodeForUserbot", $params, $company_url);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("not found error code");
			}
			throw new ParseFatalException("passed unknown error_code");
		}

		return [$response["node_url"], $response["file_token"]];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем вызов в php_company
	 *
	 * @param string $method
	 * @param array  $params
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $company_url
	 * @param string $private_key
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _call(string $method, array $params, string $company_url):array {

		[$entrypoint_url, $company_id] = self::_getCompanyEntrypoint($company_url);

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, COMPANY_USERBOT_PRIVATE_KEY, $json_params);
		return Type_Socket_Main::doCall($entrypoint_url, $method, $json_params, $signature, $company_id);
	}

	/**
	 * Получаем entrypoint компании
	 *
	 * @param string $company_url
	 *
	 * @return array
	 */
	public static function _getCompanyEntrypoint(string $company_url):array {

		// если компании в заголовке нет - возьмем из домена
		$company_domino           = explode(".", $company_url)[0];
		$company_domino_explode   = explode("-", $company_domino);
		$domino                   = $company_domino_explode[1];
		$domino_entrypoint_config = getConfig("DOMINO_ENTRYPOINT");
		$socket_module_config     = getConfig("SOCKET_MODULE");
		$company_id               = intval(substr($company_domino_explode[0], 1));
		return [$domino_entrypoint_config[$domino] . $socket_module_config["file_balancer"]["socket_path"], $company_id];
	}
}
