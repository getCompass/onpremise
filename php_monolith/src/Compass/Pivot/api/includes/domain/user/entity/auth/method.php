<?php

namespace Compass\Pivot;

/**
 * класс для работы с возможными способами аутентификации
 * @package Compass\Pivot
 */
class Domain_User_Entity_Auth_Method {

	/** аутентификация по номеру телефона */
	public const METHOD_PHONE_NUMBER = "phone_number";

	/** аутентификация по почте */
	public const METHOD_MAIL = "mail";

	/** список существующих способов */
	protected const _EXISTING_METHOD_LIST = [
		self::METHOD_PHONE_NUMBER,
		self::METHOD_MAIL,
	];

	/**
	 * получаем список доступных способов аутентификации
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getAvailableMethodList():array {

		$list = Domain_User_Entity_Auth_Config::getAvailableMethodList();

		// не доверяем значениям, поэтому фильтруем
		return array_filter($list, static fn (string $item) => in_array($item, self::_EXISTING_METHOD_LIST));
	}

	/**
	 * проверяем, что способ аутентификации доступен на сервере
	 *
	 * @return bool
	 */
	public static function isMethodAvailable(string $method):bool {

		return in_array($method, self::getAvailableMethodList());
	}
}