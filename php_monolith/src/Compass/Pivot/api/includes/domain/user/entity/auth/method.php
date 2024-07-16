<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с возможными способами аутентификации
 * @package Compass\Pivot
 */
class Domain_User_Entity_Auth_Method {

	/** аутентификация по номеру телефона */
	public const METHOD_PHONE_NUMBER = "phone_number";

	/** аутентификация по почте */
	public const METHOD_MAIL = "mail";

	/** аутентификация через SSO */
	public const METHOD_SSO = "sso";

	/** список существующих способов */
	protected const _EXISTING_METHOD_LIST = [
		self::METHOD_PHONE_NUMBER,
		self::METHOD_MAIL,
		self::METHOD_SSO,
	];

	/**
	 * получаем список доступных способов аутентификации
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getAvailableMethodList():array {

		$list = Domain_User_Entity_Auth_Config::getAvailableMethodList();

		// не доверяем значениям, поэтому фильтруем
		return array_filter($list, static fn(string $item) => in_array($item, self::_EXISTING_METHOD_LIST));
	}

	/**
	 * проверяем, что способ аутентификации доступен на сервере
	 *
	 * @return bool
	 */
	public static function isMethodAvailable(string $method):bool {

		return in_array($method, self::getAvailableMethodList());
	}

	/**
	 * удостоверяемся, что метод аутентификации включен
	 *
	 * @throws Domain_User_Exception_AuthMethodDisabled
	 * @throws ParseFatalException
	 */
	public static function assertMethodEnabled(string $method):void {

		if (!in_array($method, self::_EXISTING_METHOD_LIST)) {
			throw new ParseFatalException("unexpected auth method: $method");
		}

		if (!self::isMethodAvailable($method)) {
			throw new Domain_User_Exception_AuthMethodDisabled("auth method not enabled");
		}
	}

	/**
	 * Проверяем, что способ включен данный метод аутентификации
	 */
	public static function isSingleAuthMethodEnabled(string $method):bool {

		return in_array($method, self::getAvailableMethodList()) && count(self::getAvailableMethodList()) == 1;
	}
}