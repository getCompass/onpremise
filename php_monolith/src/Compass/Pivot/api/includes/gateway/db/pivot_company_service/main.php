<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для базы данных pivot_company_service
 */
class Gateway_Db_PivotCompanyService_Main {

	protected const _DB_KEY = "pivot_company_service";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод открывает транзакцию
	 *
	 */
	public static function beginTransaction():bool {

		return ShardingGateway::database(self::_getDbKey())->beginTransaction();
	}

	/**
	 * метод для коммита транзакции
	 *
	 * @throws ReturnFatalException
	 */
	public static function commitTransaction():void {

		if (!ShardingGateway::database(self::_getDbKey())->commit()) {

			ShardingGateway::database(self::_getDbKey())->rollback();
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	/**
	 * Метод устанавливает уровень изоляции READ COMMITTED для следующей начинаемой транзакций
	 *
	 * @return bool
	 */
	public static function setReadCommittedIsolationLevelInTransaction():bool {

		return ShardingGateway::database(self::_getDbKey())->setTransactionIsolationLevel(\myPDObasic::ISOLATION_READ_COMMITTED);
	}

	/**
	 * метод откатывает транзакцию
	 *
	 */
	public static function rollback():bool {

		return ShardingGateway::database(self::_getDbKey())->rollback();
	}

	/**
	 * метод выполняет sql запрос
	 *
	 */
	public static function query(string $query):\PDOStatement|bool {

		return ShardingGateway::database(self::_getDbKey())->execQuery($query);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем шард ключ для базы данных
	 *
	 */
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}