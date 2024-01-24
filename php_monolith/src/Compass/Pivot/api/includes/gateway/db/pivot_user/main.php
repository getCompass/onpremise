<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * класс-интерфейс для базы данных pivot_user
 */
class Gateway_Db_PivotUser_Main {

	protected const _DB_KEY = "pivot_user";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод открывает транзакцию
	public static function beginTransaction(int $user_id):bool {

		return ShardingGateway::database(self::_getDbKey($user_id))->beginTransaction();
	}

	// метод для коммита транзакции
	public static function commitTransaction(int $user_id):void {

		if (!ShardingGateway::database(self::_getDbKey($user_id))->commit()) {

			ShardingGateway::database(self::_getDbKey($user_id))->rollback();
			throw new ReturnFatalException("Transaction commit failed");
		}
	}

	// метод откатывает транзакцию
	public static function rollback(int $user_id):bool {

		return ShardingGateway::database(self::_getDbKey($user_id))->rollback();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем шард ключ для базы данных
	protected static function _getDbKey(int $user_id):string {

		return self::_DB_KEY . "_" . ceil($user_id / 10000000) . "0m";
	}

	/**
	 * Группируем список компаний по шарду таблиц
	 */
	protected static function _groupedUserIdListByDbKey(array $user_id_list):array {

		$grouped_user_id_list = [];
		foreach ($user_id_list as $user_id) {
			$grouped_user_id_list[self::_DB_KEY . "_" . ceil($user_id / 10000000) . "0m"][] = $user_id;
		}

		return $grouped_user_id_list;
	}
}