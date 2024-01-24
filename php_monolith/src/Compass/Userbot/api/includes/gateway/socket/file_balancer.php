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
	public static function getNodeUrl(int $userbot_user_id, string $domino_entrypoint, int $company_id):array {

		// формируем параметры для запроса
		$params = [
			"userbot_user_id" => $userbot_user_id,
		];

		[$status, $response] = self::_call("files.getNodeForUserbot", $params, $domino_entrypoint, $company_id);

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
	 * делаем вызов
	 *
	 */
	protected static function _call(string $method, array $params, string $domino_entrypoint, int $company_id):array {

		$entrypoint_url = self::_getCompanyEntrypoint($domino_entrypoint);

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, COMPANY_USERBOT_PRIVATE_KEY, $json_params);

		return Type_Socket_Main::doCall($entrypoint_url, $method, $json_params, $signature, $company_id);
	}

	/**
	 * Получаем entrypoint компании
	 *
	 */
	public static function _getCompanyEntrypoint(string $domino_entrypoint):string {

		// если компании в заголовке нет - возьмем из домена
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $domino_entrypoint . $socket_module_config["file_balancer"]["socket_path"];
	}
}
