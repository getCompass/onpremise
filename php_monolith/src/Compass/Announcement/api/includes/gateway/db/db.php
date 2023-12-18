<?php

namespace Compass\Announcement;

/**
 * класс-интерфейс работы с базой данных
 */
abstract class Gateway_Db_Db {

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Получить соединение с бд
	 *
	 * @param string $shard_suffix
	 *
	 * @return \myPDObasic
	 */
	public static function getConnection(string $shard_suffix = ""):\myPDObasic {

		if ($shard_suffix != "") {
			$shard_suffix = "_{$shard_suffix}";
		}

		return ShardingGateway::database(static::_getDataBaseKey() . $shard_suffix);
	}

	/**
	 * Метод открывает транзакцию
	 *
	 * @return bool
	 */
	public static function beginTransaction():bool {

		return ShardingGateway::database(static::_getDataBaseKey())->beginTransaction();
	}

	/**
	 * Метод для коммита транзакции
	 *
	 * @throws \returnException
	 */
	public static function commitTransaction():void {

		if (!ShardingGateway::database(static::_getDataBaseKey())->commit()) {

			ShardingGateway::database(static::_getDataBaseKey())->rollback();
			throw new \returnException("Transaction commit failed");
		}
	}

	/**
	 * Метод откатывает транзакцию
	 *
	 * @return bool
	 */
	public static function rollback():bool {

		return ShardingGateway::database(static::_getDataBaseKey())->rollback();
	}

	/**
	 * получаем ключ для базы данных
	 *
	 * @return string
	 */
	public static function _getDataBaseKey():string {

		return static::_DB_KEY;
	}
}
