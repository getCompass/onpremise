<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для базы данных company_call
 */
class Gateway_Db_CompanyCall_Main {

	protected const _DB_KEY = "company_call";

	// метод для открытия транзакции
	public static function beginTransaction():bool {

		return ShardingGateway::database(self::_getDbKey())->beginTransaction();
	}

	// метод для коммита транзакции
	public static function commitTransaction():void {

		// коммитим транзакцию
		if (!ShardingGateway::database(self::_getDbKey())->commit()) {

			ShardingGateway::database(self::_getDbKey())->rollback();
			throw new \returnException("Transaction commit failed in " . __METHOD__);
		}
	}

	// метод закрывает транзакцию
	public static function commit():bool {

		return ShardingGateway::database(self::_DB_KEY)->commit();
	}

	// метод для отката транзакции
	public static function rollback():bool {

		return ShardingGateway::database(self::_getDbKey())->rollback();
	}

	// получаем шард ключ для базы данных
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}