<?php declare(strict_types=1);

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс для базы данных premise_user
 */
class Gateway_Db_PremiseUser_Main {

	protected const _DB_KEY = "premise_user";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод открывает транзакцию
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public static function beginTransaction():bool {

		return ShardingGateway::database(self::_getDbKey())->beginTransaction();
	}

	/**
	 * Метод для коммита транзакции
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
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
	 * @throws ParseFatalException
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