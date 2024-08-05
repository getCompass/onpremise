<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс для работы с базой данных ldap_data
 * @package Compass\Federation
 */
class Gateway_Db_LdapData_Main {

	protected const _DB_KEY = "ldap_data";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод открывает транзакцию
	 *
	 * @return bool
	 */
	public static function beginTransaction():bool {

		return ShardingGateway::database(self::_getDbKey())->beginTransaction();
	}

	/**
	 * Метод для коммита транзакции
	 *
	 * @throws \returnException
	 */
	public static function commitTransaction():void {

		if (!ShardingGateway::database(self::_getDbKey())->commit()) {

			ShardingGateway::database(self::_getDbKey())->rollback();
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	/**
	 * Метод откатывает транзакцию
	 *
	 * @return bool
	 */
	public static function rollback():bool {

		return ShardingGateway::database(self::_getDbKey())->rollback();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем шард ключ для базы данных
	 *
	 * @return string
	 */
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}