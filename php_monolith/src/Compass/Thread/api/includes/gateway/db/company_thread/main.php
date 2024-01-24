<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для базы company_thread
 */
class Gateway_Db_CompanyThread_Main {

	protected const _DB_KEY = "company_thread";

	// метод для старта транзакции
	public static function beginTransaction():bool {

		return ShardingGateway::database(self::_getDbKey())->beginTransaction();
	}

	// метод для коммита транзакции
	public static function commitTransaction():void {

		if (!ShardingGateway::database(self::_getDbKey())->commit()) {

			ShardingGateway::database(self::_getDbKey())->rollback();
			throw new ReturnFatalException("Transaction commit failed in " . __METHOD__);
		}
	}

	// метод для отката транзакции
	public static function rollback():bool {

		$result = ShardingGateway::database(self::_getDbKey())->rollback();

		if (!$result) {
			throw new ReturnFatalException("Transaction rollback failed in " . __METHOD__);
		}

		return true;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем ключ в sharding
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}