<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с конфиг-файлом аутентификации api/conf/auth.php
 * @package Compass\Pivot
 */
class Domain_User_Entity_Auth_Config {

	/** ключ для получения конфига с основными параметрами аутентификации */
	protected const _KEY_AUTH_MAIN = "AUTH_MAIN";

	/** ключ для получения конфига с параметрами аутентификации через почту */
	protected const _KEY_AUTH_MAIL = "AUTH_MAIL";

	/** ключ для получения конфига с параметрами аутентификации через SSO */
	protected const _KEY_AUTH_SSO = "AUTH_SSO";

	/**
	 * получаем список доступных способов аутентификации
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getAvailableMethodList():array {

		return self::_getConfig(self::_KEY_AUTH_MAIN)["available_method_list"];
	}

	/**
	 * получаем кол-во попыток аутентификации, после которых запрашивается разгадывание капчи
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function getCaptchaRequireAfter():int {

		return self::_getConfig(self::_KEY_AUTH_MAIN)["captcha_require_after"];
	}

	/**
	 * получаем список доступных доменов почтовых адресов, для которых разрешена аутентификация в приложении
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getMailAllowedDomainList():array {

		// получаем список
		$allowed_domain_list = self::_getConfig(self::_KEY_AUTH_MAIL)["allowed_domain_list"];

		// удаляем символ @, если есть
		return array_map(static fn(string $allowed_domain) => trim($allowed_domain, "@"), $allowed_domain_list);
	}

	/**
	 * включена ли опция подтверждения почты через проверочный код при регистрации
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public static function isMailRegistration2FAEnabled():bool {

		return self::_getConfig(self::_KEY_AUTH_MAIL)["registration_2fa_enabled"];
	}

	/**
	 * включена ли опция подтверждения почты через проверочный код при авторизации
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public static function isMailAuthorization2FAEnabled():bool {

		return self::_getConfig(self::_KEY_AUTH_MAIL)["authorization_2fa_enabled"];
	}

	/**
	 * получаем протокол, через который работает аутентификаци через SSO
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public static function getSsoProtocol():string {

		return self::_getConfig(self::_KEY_AUTH_SSO)["protocol"];
	}

	/**
	 * получаем текст для кнопки начала аутентификации через SSO
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public static function getSsoStartButtonText():string {

		return self::_getConfig(self::_KEY_AUTH_SSO)["start_button_text"];
	}

	/**
	 * включена ли опция альтернативной аутентификации при SSO
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public static function isAuthorizationAlternativeEnabled():bool {

		return self::_getConfig(self::_KEY_AUTH_SSO)["authorization_alternative_enabled"];
	}

	/**
	 * получаем контент конфига
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	protected static function _getConfig(string $config_key):array {

		$config = getConfig($config_key);

		// если пришел пустой конфиг
		if (count($config) < 1) {
			throw new ParseFatalException("unexpected content");
		}

		return $config;
	}
}