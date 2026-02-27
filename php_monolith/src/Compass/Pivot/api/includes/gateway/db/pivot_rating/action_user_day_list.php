<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс-интерфейс для таблицы pivot_rating_{10m}.action_user_day_list_{1}
 */
class Gateway_Db_PivotRating_ActionUserDayList extends Gateway_Db_PivotRating_Main
{
	protected const _TABLE_KEY = "action_user_day_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для создания записи
	 */
	public static function insert(int $user_id, int $day_start_at, array $action_list): void
	{

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$insert = [
			"user_id"      => $user_id,
			"day_start_at" => $day_start_at,
			"created_at"   => time(),
			"updated_at"   => 0,
			"action_list"  => $action_list,
		];
		ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * Метод для получения записи
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(int $user_id, int $day_start_at): Struct_Db_PivotRating_ActionUserDay
	{

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `day_start_at` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $day_start_at, 1);

		return self::_rowToStruct($row);
	}

	/**
	 * Метод для получения записей
	 *
	 * @return Struct_Db_PivotRating_ActionUserDay[]
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getListByUserId(int $user_id, array $day_start_at_list): array
	{

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `day_start_at` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $day_start_at_list, count($day_start_at_list));

		return self::_formatList($list);
	}

	/**
	 * Получаем запись на обновление
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @throws RowNotFoundException
	 */
	public static function getForUpdate(int $user_id, int $day_start_at): Struct_Db_PivotRating_ActionUserDay
	{

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `day_start_at` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $day_start_at, 1);

		return self::_rowToStruct($row);
	}

	/**
	 * Обновляем запись
	 */
	public static function update(int $user_id, int $day_start_at, array $set): void
	{

		if (!isset($set["updated_at"])) {
			$set["updated_at"] = time();
		}

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `day_start_at` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $day_start_at, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем таблицу
	 */
	protected static function _getTableKey(int $user_id): string
	{

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}

	/**
	 * форматируем список записей из базы
	 *
	 * @return Struct_Db_PivotRating_ActionUserDay[]
	 */
	protected static function _formatList(array $list): array
	{

		return array_map([self::class, "_rowToStruct"], $list);
	}

	/**
	 * Форматируем запись в структуру
	 */
	protected static function _rowToStruct(array $row): Struct_Db_PivotRating_ActionUserDay
	{

		if (!isset($row["user_id"])) {
			throw new RowNotFoundException("not found row");
		}

		return Struct_Db_PivotRating_ActionUserDay::fromRow($row);
	}
}
