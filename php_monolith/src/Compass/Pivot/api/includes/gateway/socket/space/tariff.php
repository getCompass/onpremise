<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Гейтвей для общения с тарифами пространства
 */
class Gateway_Socket_Space_Tariff {

	/**
	 * Опубликовать анонс в пространстве
	 *
	 * @param int                            $announcement_type
	 * @param array                          $data
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws cs_CompanyIsHibernate
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function publishAnnouncement(int $announcement_type, array $data, Struct_Db_PivotCompany_Company $company):void {

		$params = [
			"announcement_type" => $announcement_type,
			"data"              => $data,
		];
		[$status,] = self::_call(
			"space.tariff.publishAnnouncement",
			$params,
			0,
			$company->company_id,
			$company->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($company->extra));

		if ($status !== "ok") {
			throw new ReturnFatalException("passed unknown error_code");
		}
	}

	/**
	 * Опубликовать анонс в пространстве
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws cs_CompanyIsHibernate
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function disableAnnouncements(Struct_Db_PivotCompany_Company $company):void {

		$params = [];
		[$status,] = self::_call(
			"space.tariff.disableAnnouncements",
			$params,
			0,
			$company->company_id,
			$company->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($company->extra));

		if ($status !== "ok") {
			throw new ReturnFatalException("passed unknown error_code");
		}
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
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_CompanyIsHibernate
	 */
	protected static function _call(string $method, array $params, int $user_id, int $company_id, string $domino_id, string $private_key):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url = self::_getEntryPoint($domino_id);

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