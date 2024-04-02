<?php

namespace Compass\Federation;

use BaseFrame\Server\ServerProvider;

/**
 * класс для инициализации объекта клиента для работы с oidc
 * @package Compass\Federation
 */
class Domain_Sso_Entity_Oidc_Client {

	/** @var string куда редиректим пользователя после успешной аутентификации */
	protected const _REDIRECT_URL = PUBLIC_ENTRYPOINT_FEDERATION . "/sso/auth_result/oidc/";

	/** @var Gateway_Sso_Oidc_Client|null объект клиента */
	protected static ?Gateway_Sso_Oidc_Client $_client = null;

	/**
	 * инициализируем объект клиента для работы с oidc, если он ранее не был инициализирован
	 *
	 * @return Gateway_Sso_Oidc_Client
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function init():Gateway_Sso_Oidc_Client {

		if (!is_null(static::$_client)) {
			return static::$_client;
		}

		$client_id     = Domain_Sso_Entity_Oidc_Config::getClientID();
		$client_secret = Domain_Sso_Entity_Oidc_Config::getClientSecret();
		$client        = new Gateway_Sso_Oidc_Client($client_id, $client_secret);
		$client->setProviderConfig(Domain_Sso_Entity_Oidc_Config::getProviderConfig());
		$client->setRedirectURL(self::_REDIRECT_URL);
		$client->addScope([
			"openid",
			"profile",
			"email",
			"offline_access",
		]);

		// если окружение разработки
		if (ServerProvider::isDev()) {

			$client->setVerifyHost(false);
			$client->setVerifyPeer(false);
			$client->setHttpUpgradeInsecureRequests(false);
		}

		static::$_client = $client;

		return static::$_client;
	}

	/**
	 * Формируем параметр nonce на основе объекта попытки аутентификации
	 *
	 * @return string
	 */
	public static function getNonceByAuthStruct(Struct_Db_SsoData_SsoAuth $sso_auth):string {

		return sha1($sso_auth->sso_auth_token . $sso_auth->signature);
	}

	/**
	 * Получаем ссылку для прохождения аутентификации в SSO
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getAuthorizationURL(Struct_Db_SsoData_SsoAuth $sso_auth, string $state):string {

		$client = self::init();
		return $client->getAuthorizationURL($state, self::getNonceByAuthStruct($sso_auth));
	}
}