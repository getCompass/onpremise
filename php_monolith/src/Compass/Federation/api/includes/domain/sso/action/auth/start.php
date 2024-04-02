<?php

namespace Compass\Federation;

/**
 * класс описывает действие запускающее попытку аутентификации через SSO
 * @package Compass\Federation
 */
class Domain_Sso_Action_Auth_Start {

	/**
	 * запускаем попытку аутентификации
	 *
	 * @return Struct_Db_SsoData_SsoAuth
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function do(string $user_agent, string $ip_address, string|bool $redirect_url):Struct_Db_SsoData_SsoAuth {

		// если не передали никакой redirect_url, то отправим на главную веб-страницу on-premise
		if (false === $redirect_url) {
			$redirect_url = sprintf("%s://%s", WEB_PROTOCOL_PUBLIC, PUBLIC_ADDRESS_GLOBAL);
		}

		return Domain_Sso_Entity_Auth::create($user_agent, $ip_address, $redirect_url);
	}
}