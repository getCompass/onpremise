<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_member.usercard_worked_hour_list
 */
class Gateway_Db_CompanyMember_UsercardWorkedHourList extends Gateway_Db_CompanyMember_Main {

	protected const _TABLE_KEY = "usercard_worked_hour_list";

	/**
	 * Получаем список итемов фиксации рабочего времени
	 *
	 * @return Struct_Domain_Usercard_WorkedHours[]
	 */
	public static function getList(array $worked_hour_id_list):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `worked_hour_id` IN (?a) AND `is_deleted` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $worked_hour_id_list, 0, count($worked_hour_id_list));

		$obj_list = [];
		foreach ($list as $row) {

			$row["data"] = fromJson($row["data"]);
			$obj_list[]  = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * Добавляем новую запись в таблицу
	 *
	 * @throws \queryException|cs_RowAlreadyExist
	 */
	public static function add(int $user_id, int $type, int $day_start_at, float $value, array $data):Struct_Domain_Usercard_WorkedHours {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		$insert_row     = [
			"user_id"      => $user_id,
			"day_start_at" => $day_start_at,
			"type"         => $type,
			"is_deleted"   => 0,
			"value_1000"   => floatToInt($value),
			"created_at"   => time(),
			"updated_at"   => 0,
			"data"         => $data,
		];
		$worked_hour_id = ShardingGateway::database($shard_key)->insert($table_name, $insert_row);

		// если запись НЕ создалась
		if ($worked_hour_id < 1) {
			throw new cs_RowAlreadyExist();
		}

		$insert_row["worked_hour_id"] = $worked_hour_id;
		return self::_rowToObject($insert_row);
	}

	/**
	 * Получаем запись за определенный день
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOneByDayStartAt(int $user_id, int $day_start_at):Struct_Domain_Usercard_WorkedHours {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=get_by_user_id_day_start_at)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `day_start_at` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $day_start_at, 1);

		// переводим в объект
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$row["data"] = fromJson($row["data"]);
		return self::_rowToObject($row);
	}

	/**
	 * Обновляем запись в таблице
	 */
	public static function set(int $worked_hour_id, array $set):void {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE worked_hour_id = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $worked_hour_id, 1);
	}

	/**
	 * Получаем записи по временной метке
	 *
	 * @return Struct_Domain_Usercard_WorkedHours[]
	 */
	public static function getListByDayStartAt(int $user_id, int $day_start_at, int $limit = 100):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// запрос проверен на EXPLAIN (INDEX=get_by_user_id_is_deleted)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `is_deleted` = ?i AND `day_start_at` >= ?i ORDER BY `day_start_at` ASC LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, 0, $day_start_at, $limit);

		$obj_list = [];
		foreach ($list as $row) {

			$row["data"] = fromJson($row["data"]);

			// создаем объект рабочего времени
			$obj_list[] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * Делает выборку всех отработанных часов для сотрудников на указанный период.
	 */
	public static function getWorkedByPeriod(int $period_start_date, int $period_end_date, array $user_list):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// запрос проверен на EXPLAIN (INDEX=get_by_user_id_is_deleted)
		$query = "SELECT `user_id`, `value_1000` FROM `?p` WHERE `user_id` IN (?a) AND `is_deleted` = ?i AND `day_start_at` >= ?i AND `day_start_at` < ?i LIMIT ?i";

		// внимание, считаем, что у каждого сотрудника не больше одной смены в день
		// то есть лимит выборки кол-во пользователей * число дней в периоде
		$limit = ceil(($period_end_date - $period_start_date) / DAY1) * count($user_list);

		// получаем все записи и сливаем их в общий набор
		$list = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_list, 0, $period_start_date, $period_end_date, $limit);

		// !!! преобразовываем отдельно из int во float
		foreach ($list as $index => $row) {
			$list[$index]["float_value"] = self::_getValueFromTable($row["value_1000"]);
		}

		return $list;
	}

	/**
	 * Получаем итемы рабочего времени пользователя пользователя
	 *
	 * @return Struct_Domain_Usercard_WorkedHours[]
	 */
	public static function getByLastWorkedId(int $user_id, int $worked_hour_id, int $limit):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=get_by_user_id_is_deleted)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `worked_hour_id` > ?i AND `is_deleted` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $worked_hour_id, 0, $limit);

		$obj_list = [];
		foreach ($list as $row) {

			$row["data"] = fromJson($row["data"]);
			$obj_list[]  = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * Получаем запись по worked_hour_id
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $worked_hour_id):Struct_Domain_Usercard_WorkedHours {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `worked_hour_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $worked_hour_id, 1);

		// переводим в объект
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$row["data"] = fromJson($row["data"]);

		return self::_rowToObject($row);
	}

	/**
	 * Получить запись на обновление
	 *
	 * @throws \parseException
	 */
	public static function getOneForUpdate(int $worked_hour_id):Struct_Domain_Usercard_WorkedHours {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableName();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `worked_hour_id` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $worked_hour_id, 1);

		if (!isset($row["worked_hour_id"])) {
			throw new ParseFatalException(__METHOD__ . ": row is not exist");
		}

		$row["data"] = fromJson($row["data"]);
		return self::_rowToObject($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Domain_Usercard_WorkedHours {

		return new Struct_Domain_Usercard_WorkedHours(
			$row["worked_hour_id"],
			$row["user_id"],
			$row["day_start_at"],
			$row["type"],
			$row["is_deleted"],
			self::_getValueFromTable($row["value_1000"]),
			$row["created_at"],
			$row["updated_at"],
			Type_User_Card_WorkedHours::actualData($row["data"])
		);
	}

	/**
	 * преобразовываем значение, полученное из таблицы
	 */
	protected static function _getValueFromTable(int $int_value):float {

		return intToFloat($int_value);
	}

	/**
	 * функция возвращает название таблицы
	 */
	protected static function _getTableName():string {

		return self::_TABLE_KEY;
	}
}