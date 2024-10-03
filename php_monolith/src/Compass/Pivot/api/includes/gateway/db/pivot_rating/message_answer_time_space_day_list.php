<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_rating_{10m}.message_answer_time_space_day_list_{1}
 */
class Gateway_Db_PivotRating_MessageAnswerTimeSpaceDayList extends Gateway_Db_PivotRating_Main {

	protected const _TABLE_KEY = "message_answer_time_space_day_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для создания записи
	 */
	public static function insert(int $space_id, int $day_start_at, array $answer_time_list):void {

		$shard_key  = self::_getDbKey($space_id);
		$table_name = self::_getTableKey($space_id);

		$insert = [
			"space_id"         => $space_id,
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
	public static function getOne(int $space_id, int $day_start_at):Struct_Db_PivotRating_MessageAnswerTimeSpaceDay {

		$shard_key  = self::_getDbKey($space_id);
		$table_name = self::_getTableKey($space_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `space_id` = ?i AND `day_start_at` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $space_id, $day_start_at, 1);

		return self::_rowToStruct($row);
	}

	/**
	 * Метод для получения записей
	 *
	 * @param array $space_id_list
	 * @param int   $day_start_at
	 *
	 * @return Struct_Db_PivotRating_MessageAnswerTimeSpaceDay[]
	 */
	public static function getSpaceList(array $space_id_list, int $day_start_at):array {

		$grouped_by_shard = [];

		// группируем по шардам
		foreach ($space_id_list as $space_id) {
			$grouped_by_shard[self::_getDbKey($space_id)][self::_getTableKey($space_id)][] = $space_id;
		}

		// для каждого шарда базы данных
		$list = [];
		foreach ($grouped_by_shard as $db_name => $grouped_by_table_space_id_list) {

			// для каждой таблицы базы данных
			foreach ($grouped_by_table_space_id_list as $table_name => $space_id_list) {

				// запрос проверен на EXPLAIN(INDEX=PRIMARY)
				$query = "SELECT * FROM `?p` WHERE `space_id` IN (?a) AND `day_start_at` = ?i LIMIT ?i";
				$temp  = ShardingGateway::database($db_name)->getAll($query, $table_name, $space_id_list, $day_start_at, count($space_id_list));
				$list  = array_merge($list, $temp);
			}
		}

		return self::_listToStruct($list);
	}

	/**
	 * Обновляем запись
	 */
	public static function update(int $space_id, int $day_start_at, array $set):void {

		if (!isset($set["updated_at"])) {
			$set["updated_at"] = time();
		}

		$shard_key  = self::_getDbKey($space_id);
		$table_name = self::_getTableKey($space_id);

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `space_id` = ?i AND `day_start_at` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $space_id, $day_start_at, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем таблицу
	 */
	protected static function _getTableKey(int $space_id):string {

		return self::_TABLE_KEY . "_" . ceil($space_id / 1000000);
	}

	/**
	 * Форматируем список записей из базы
	 * @return Struct_Db_PivotRating_MessageAnswerTimeSpaceDay[]
	 */
	protected static function _listToStruct(array $list):array {

		return array_map(fn(array $row) => self::_rowToStruct($row), $list);
	}

	/**
	 * Форматируем запись в структуру
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotRating_MessageAnswerTimeSpaceDay {

		if (!isset($row["space_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("not found row");
		}

		return Struct_Db_PivotRating_MessageAnswerTimeSpaceDay::fromRow($row);
	}
}