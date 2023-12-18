<?php

namespace Compass\FileNode;

/**
 * класс-интерфейс для бд file_node
 */
class Gateway_Db_FileNode_Main {

	protected const _DB_KEY = "file_node";

	// начать транзакцию
	public static function beginTransaction():bool {

		return \sharding::pdo(self::_getDbKey())->beginTransaction();
	}

	// commit транзакции
	public static function commit():void {

		if (!\sharding::pdo(self::_getDbKey())->commit()) {

			\sharding::pdo(self::_getDbKey())->rollback();
			throw new \ReturnException("Transaction commit failed in " . __METHOD__);
		}
	}

	// откат транзакции
	public static function rollback():bool {

		return \sharding::pdo(self::_getDbKey())->rollback();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем ключ для бд
	 */
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}