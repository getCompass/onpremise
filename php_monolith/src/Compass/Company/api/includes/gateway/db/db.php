<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс работы с базой данных
 */
abstract class Gateway_Db_Db {

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод открывает транзакцию
	 */
	public static function beginTransaction():bool {

		return ShardingGateway::database(static::_DB_KEY)->beginTransaction();
	}

	/**
	 * Метод для коммита транзакции
	 *
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function commitTransaction():void {

		if (!ShardingGateway::database(static::_DB_KEY)->commit()) {

			ShardingGateway::database(static::_DB_KEY)->rollback();
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	/**
	 * Метод откатывает транзакцию
	 */
	public static function rollback():bool {

		return ShardingGateway::database(static::_DB_KEY)->rollback();
	}
}
