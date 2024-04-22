<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;
use cs_SocketRequestIsFailed;
use parseException;
use returnException;

/**
 * задача класса общаться между php_premise -> php_company
 */
class Gateway_Socket_Company {

	/**
	 * Получить список участников пространства
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_SocketRequestIsFailed
	 */
	public static function getMemberList(int $company_id, string $domino_id, string $private_key):array {

		$params = [
			"is_only_user" => 0,
			"is_need_left" => 0,
		];
		[$status, $response] = self::_call("company.member.getAll", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["member_list"];
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
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	protected static function _call(string $method, array $params, int $user_id, int $company_id, string $domino_id, string $private_key):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url = self::_getEntrypoint($domino_id);

		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_SSL, $private_key, $json_params);
		try {
			return Type_Socket_Main::doCall($url, $method, $json_params, $signature, $company_id, $user_id);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 404) {
				throw new Gateway_Socket_Exception_CompanyIsNotServed("company is not served");
			}
			if ($e->getHttpStatusCode() == 503) {
				throw new cs_CompanyIsHibernate("company is hibernated");
			}
			throw $e;
		}
	}

	/**
	 * получаем url
	 *
	 * @param string $domino
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	protected static function _getEntryPoint(string $domino):string {

		$domino_entrypoint_config = getConfig("DOMINO_ENTRYPOINT");
		$socket_module_config     = getConfig("SOCKET_MODULE");

		if (!isset($domino_entrypoint_config[$domino])) {
			throw new \BaseFrame\Exception\Request\CompanyNotServedException("company not served");
		}
		return $domino_entrypoint_config[$domino]["private_entrypoint"] . $socket_module_config["company"]["socket_path"];
	}
}
