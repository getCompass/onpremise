<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс работы с базой данных
 */
abstract class Gateway_Db_Db {

	protected const _DB_KEY = "";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод открывает транзакцию
	 *
	 */
	public static function beginTransaction():bool {

		return static::_connect()->beginTransaction();
	}

	/**
	 * Метод для коммита транзакции
	 *
	 * @throws \returnException
	 */
	public static function commitTransaction():void {

		if (!static::_connect()->commit()) {

			static::_connect()->rollback();
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	/**
	 * Метод откатывает транзакцию
	 *
	 */
	public static function rollback():bool {

		return static::_connect()->rollback();
	}

	/**
	 * Создает подключение к базе данных
	 */
	protected static function _connect():\myPDObasic {

		return ShardingGateway::database(static::_DB_KEY);
	}
}
