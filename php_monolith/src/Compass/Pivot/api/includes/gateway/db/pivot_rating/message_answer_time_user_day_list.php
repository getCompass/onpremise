<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_rating_{10m}.message_answer_time_user_day_list_{1}
 */
class Gateway_Db_PivotRating_MessageAnswerTimeUserDayList extends Gateway_Db_PivotRating_Main {

	protected const _TABLE_KEY = "message_answer_time_user_day_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для создания записи
	 */
	public static function insert(int $user_id, int $day_start_at, array $answer_time_list):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$insert = [
			"user_id"          => $user_id,
			"day_start_at"     => $day_start_at,
			"created_at"       => time(),
			"updated_at"       => 0,
			"answer_time_list" => $answer_time_list,
		];
		ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * Метод для получения записи
	 *
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $user_id, int $day_start_at):Struct_Db_PivotRating_MessageAnswerTimeUserDay {

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
	 * @param int $user_id
	 * @param int $limit
	 *
	 * @return Struct_Db_PivotRating_MessageAnswerTimeUserDay[]
	 */
	public static function getListByUserId(int $user_id, int $limit):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i ORDER BY `day_start_at` DESC LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $limit);

		return self::_listToStruct($list);
	}

	/**
	 * Получаем запись на обновление
	 *
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getForUpdate(int $user_id, int $day_start_at):Struct_Db_PivotRating_MessageAnswerTimeUserDay {

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
	public static function update(int $user_id, int $day_start_at, array $set):void {

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
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}

	/**
	 * Форматируем список записей из базы
	 * @return Struct_Db_PivotRating_MessageAnswerTimeUserDay[]
	 */
	protected static function _listToStruct(array $list):array {

		return array_map(fn(array $row) => self::_rowToStruct($row), $list);
	}

	/**
	 * Форматируем запись в структуру
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotRating_MessageAnswerTimeUserDay {

		if (!isset($row["user_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("not found row");
		}

		return Struct_Db_PivotRating_MessageAnswerTimeUserDay::fromRow($row);
	}
}