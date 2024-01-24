<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_business.bitrix_user_info_failed_task_list
 */
class Gateway_Db_PivotBusiness_BitrixUserInfoFailedTaskList extends Gateway_Db_PivotBusiness_Main {

	protected const _TABLE_KEY = "bitrix_user_info_failed_task_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(int $task_id, int $user_id, int $failed_at):string {

		$insert = [
			"task_id"   => $task_id,
			"user_id"   => $user_id,
			"failed_at" => $failed_at,
		];

		// осуществляем запрос
		return ShardingGateway::database(self::_getDbKey())->insert(self::_getTableKey(), $insert);
	}

	/**
	 * метод для удаления записей
	 *
	 * @throws \parseException
	 */
	public static function deleteList(array $task_id_list):int {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `task_id` IN (?a) LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->delete($query, self::_getTableKey(), $task_id_list, count($task_id_list));
	}

	/**
	 * метод для получения количества записей
	 *
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getCount():int {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableKey(), 1, 1);

		return $row["count"];
	}

	/**
	 * Получаем список всех записей
	 *
	 * @return array
	 */
	public static function getAll():array {

		if (!isCLi()) {
			throw new ParseFatalException("works only in cli");
		}

		// запрос проверен на EXPLAIN (INDEX=NULL)
		$query = "SELECT * FROM `?p` WHERE ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getAll($query, self::_getTableKey(), 1, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}