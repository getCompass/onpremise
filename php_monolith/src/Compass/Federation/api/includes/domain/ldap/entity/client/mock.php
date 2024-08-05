<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;
use LDAP\Connection;

/**
 * mock-класс для тестирования работы с LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Client_Mock implements Domain_Ldap_Entity_Client_Interface {

	protected const _MOCK_KEY_BIND           = "bind";
	protected const _MOCK_KEY_SEARCH_ENTRIES = "search_entries";

	/**
	 * мокаем результат функции @see self::bind
	 */
	public static function mockBind(bool $result):void {

		self::_setMock(self::_MOCK_KEY_BIND, $result);
	}

	public function bind(string $dn, string $password):bool {

		return self::_getMock(self::_MOCK_KEY_BIND, false);
	}

	public function unbind():void {

		// ничего не делаем
	}

	/**
	 * мокаем результат функции @see self::searchEntries
	 */
	public static function mockSearchResult(array $result):void {

		self::_setMock(self::_MOCK_KEY_SEARCH_ENTRIES, $result);
	}

	public function searchEntries(string $base, string $filter, array $attribute_list = []):array {

		return self::_getMock(self::_MOCK_KEY_SEARCH_ENTRIES, []);
	}

	/**
	 * устанавливаем mock значение
	 */
	protected static function _setMock(string $key, mixed $value):void {

		\mCache::init()->set(self::_prepareMockKey($key), $value);
	}

	/**
	 * получаем mock значение
	 *
	 * @return mixed
	 */
	protected static function _getMock(string $key, mixed $default_value):mixed {

		return \mCache::init()->get(self::_prepareMockKey($key), $default_value);
	}

	/**
	 * подготавливаем ключ для мока данных
	 *
	 * @return string
	 */
	protected static function _prepareMockKey(string $suffix):string {

		return __CLASS__ . $suffix;
	}
}