<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для базы данных pivot_attribution
 */
class Gateway_Db_PivotAttribution_Main {

	protected const _DB_KEY = "pivot_attribution";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод открывает транзакцию
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function beginTransaction():bool {

		return ShardingGateway::database(self::_getDbKey())->beginTransaction();
	}

	/**
	 * метод для коммита транзакции
	 *
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function commitTransaction():void {

		if (!ShardingGateway::database(self::_getDbKey())->commit()) {

			ShardingGateway::database(self::_getDbKey())->rollback();
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	/**
	 * метод откатывает транзакцию
	 *
	 * @return bool
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function rollback():bool {

		return ShardingGateway::database(self::_getDbKey())->rollback();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем шард ключ для базы данных
	 *
	 * @return string
	 */
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}