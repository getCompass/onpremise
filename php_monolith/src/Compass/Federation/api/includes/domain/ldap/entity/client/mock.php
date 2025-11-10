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

	public function searchEntries(string $base, string $filter, int $page_size, array $attribute_list = []):array {

		$entry_list = self::_getMock(self::_MOCK_KEY_SEARCH_ENTRIES, []);
		$count = 0;
		if (isset($entry_list["count"])) {

			$count = $entry_list["count"];
			unset($entry_list["count"]);
		}

		return [$count, $entry_list];
	}

	/**
	 * устанавливаем mock значение
	 */
	protected static function _setMock(string $key, mixed $value):void {

        ShardingGateway::cache()->set(self::_prepareMockKey($key), $value);
	}

	/**
	 * получаем mock значение
	 *
	 * @return mixed
	 */
	protected static function _getMock(string $key, mixed $default_value):mixed {

		return ShardingGateway::cache()->get(self::_prepareMockKey($key), $default_value);
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