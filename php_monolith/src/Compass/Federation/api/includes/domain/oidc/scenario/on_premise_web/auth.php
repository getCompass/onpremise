<?php

namespace Compass\Federation;

use BaseFrame\System\UserAgent;

/**
 * класс описывает сценарий on-premise web апи-методов аутентификации через SSO
 * @package Compass\Federation
 */
class Domain_Oidc_Scenario_OnPremiseWeb_Auth {

	/** существующие статусы sso аутентификации */
	public const STATUS_WAIT      = "wait";
	public const STATUS_EXPIRED   = "expired";
	public const STATUS_READY     = "ready";
	public const STATUS_COMPLETED = "completed";

	/**
	 * запускаем попытку
	 *
	 * @return Struct_Db_SsoData_SsoAuth
	 */
	public static function begin(string|bool $redirect_url):Struct_Db_SsoData_SsoAuth {

		// запускаем попытку
		return Domain_Oidc_Action_Auth_Start::do(UserAgent::getUserAgent(), getIp(), $redirect_url);
	}

	/**
	 * определяем статус попытки
	 *
	 * @return string
	 * @throws Domain_Oidc_Exception_Auth_SignatureMismatch
	 * @throws Domain_Oidc_Exception_Auth_TokenNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getStatus(string $sso_auth_token, string $signature):string {

		// получаем запись с попыткой
		$auth = Domain_Oidc_Entity_Auth::get($sso_auth_token);

		// проверяем, что подпись верна
		Domain_Oidc_Entity_Auth::assertSignature($auth, $signature);

		// определяем статус
		return self::_resolveStatus($auth);
	}

	/**
	 * определяем статус попытки
	 *
	 * @return string
	 */
	protected static function _resolveStatus(Struct_Db_SsoData_SsoAuth $auth):string {

		// если попытка завершена
		if (Domain_Oidc_Entity_Auth::isCompassAuthComplete($auth)) {
			return self::STATUS_COMPLETED;
		}

		// если попытка протухла
		if (Domain_Oidc_Entity_Auth::isExpired($auth)) {
			return self::STATUS_EXPIRED;
		}

		// если попытка готова к продолжению аутентификации
		if (Domain_Oidc_Entity_Auth::isSsoAuthComplete($auth)) {
			return self::STATUS_READY;
		}

		// статус по умолчанию
		return self::STATUS_WAIT;
	}
}