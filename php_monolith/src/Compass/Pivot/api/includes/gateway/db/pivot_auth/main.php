<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для базы данных pivot_auth_{Y}
 */
class Gateway_Db_PivotAuth_Main {

	protected const _DB_KEY = "pivot_auth";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод открывает транзакцию
	public static function beginTransaction(int $shard_id):bool {

		return ShardingGateway::database(self::_getDbKey($shard_id))->beginTransaction();
	}

	// метод для коммита транзакции
	public static function commitTransaction(int $shard_id):void {

		if (!ShardingGateway::database(self::_getDbKey($shard_id))->commit()) {

			ShardingGateway::database(self::_getDbKey($shard_id))->rollback();
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	// метод откатывает транзакцию
	public static function rollback(int $shard_id):bool {

		return ShardingGateway::database(self::_getDbKey($shard_id))->rollback();
	}

	// открыта ли транзакция
	public static function inTransaction(int $shard_id):bool {

		return ShardingGateway::database(self::_getDbKey($shard_id))->inTransaction();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем шард ключ для базы данных
	protected static function _getDbKey(int $shard_id):string {

		return self::_DB_KEY . "_" . $shard_id;
	}
}