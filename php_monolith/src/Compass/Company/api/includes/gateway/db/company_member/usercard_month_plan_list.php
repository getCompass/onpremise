<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для таблицы company_member.usercard_month_plan_list
 */
class Gateway_Db_CompanyMember_UsercardMonthPlanList extends Gateway_Db_CompanyMember_Main {

	public const _TABLE_KEY = "usercard_month_plan_list";

	/**
	 * Добавляем новую запись плана на месяц в базу
	 */
	public static function insertOrUpdate(int $user_id, int $type, int $month_start_at, int $plan_value, int $user_value = null):void {

		// корректируем время
		$month_start_at = Type_User_Card_MonthPlan::getCorrectedMonthStartAt($month_start_at);

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		$insert_row = [
			"user_id"    => $user_id,
			"type"       => $type,
			"plan_value" => $plan_value,
			"created_at" => $month_start_at,
			"updated_at" => time(),
		];

		if (!is_null($user_value)) {
			$insert_row["user_value"] = $user_value;
		}

		ShardingGateway::database($shard_key)->insertOrUpdate($table_name, $insert_row);
	}

	/**
	 * Обновляем запись плана на месяц
	 */
	public static function set(int $user_id, int $type, int $month_start_at, array $set):void {

		// корректируем время
		$month_start_at = Type_User_Card_MonthPlan::getCorrectedMonthStartAt($month_start_at);

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_user_and_type_and_created_at`)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `type` = ?i AND `created_at` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $type, $month_start_at, 1);
	}

	/**
	 * Достаем запись плана на месяц
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $row_id):Struct_Domain_Usercard_MonthPlan {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `row_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $row_id, 1);
		if (!isset($row["row_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Достаем несколько записей плана на текущий месяц
	 *
	 * @return Struct_Domain_Usercard_MonthPlan[]
	 */
	public static function getAll(int $user_id, int $type, int $month_start_at, int $limit):array {

		// корректируем время
		$month_start_at = Type_User_Card_MonthPlan::getCorrectedMonthStartAt($month_start_at);

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_user_and_type_and_created_at`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `type` = ?i AND `created_at` <= ?i ORDER BY `created_at` DESC LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $type, $month_start_at, $limit);

		$obj_list = [];
		foreach ($list as $row) {
			$obj_list[] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * получаем записи всех типов
	 *
	 * @return Struct_Domain_Usercard_MonthPlan[]
	 */
	public static function getAllType(int $user_id, int $month_start_at):array {

		// корректируем время
		$month_start_at = Type_User_Card_MonthPlan::getCorrectedMonthStartAt($month_start_at);

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// все доступные типы для плана на месяц
		$all_type = Type_User_Card_MonthPlan::ALLOW_MONTH_PLAN_TYPE_LIST;

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_user_and_type_and_created_at`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `type` IN (?a) AND `created_at` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $all_type, $month_start_at, count($all_type));

		$obj_list = [];
		foreach ($list as $row) {
			$obj_list[] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * Инкрементим количество сущностей плана на месяц
	 *
	 * @throws \returnException
	 */
	public static function incUserValue(int $user_id, int $type, int $month_start_at, int $inc_value):void {

		// корректируем время
		$month_start_at = Type_User_Card_MonthPlan::getCorrectedMonthStartAt($month_start_at);

		// получаем запись плана на месяц для обновления
		self::beginTransaction();
		$month_plan_data_row = self::_getForUpdate($user_id, $type, $month_start_at);

		// если не нашли запись, то создаём
		if (!isset($month_plan_data_row["user_id"])) {

			self::rollback();

			self::insertOrUpdate($user_id, $type, $month_start_at, 0, $inc_value);
			return;
		}

		// инкрементим значение
		$set = [
			"user_value" => "user_value + {$inc_value}",
			"updated_at" => time(),
		];
		self::set($user_id, $type, $month_start_at, $set);

		self::commitTransaction();
	}

	/**
	 * Декрементим количество сущностей плана на месяц
	 *
	 * @throws \returnException
	 */
	public static function decUserValue(int $user_id, int $type, int $month_start_at, int $dec_value):void {

		// корректируем время
		$month_start_at = Type_User_Card_MonthPlan::getCorrectedMonthStartAt($month_start_at);

		// получаем запись плана на месяц для обновления
		self::beginTransaction();
		$month_plan_data_row = self::_getForUpdate($user_id, $type, $month_start_at);

		// если не нашли запись, то заканчиваем выполнение
		if (!isset($month_plan_data_row["user_id"])) {

			self::rollback();
			return;
		}

		// если декрементить дальше некуда
		if ($month_plan_data_row["user_value"] == 0) {

			self::rollback();
			return;
		}

		// декрементим значение
		$set = [
			"user_value" => "user_value - {$dec_value}",
			"updated_at" => time(),
		];
		self::set($user_id, $type, $month_start_at, $set);

		self::commitTransaction();
	}

	// получаем запись на обновление
	protected static function _getForUpdate(int $user_id, int $type, int $month_start_at):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// запрос проверен на EXPLAIN (INDEX=`get_by_user_and_type_and_created_at`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `type` = ?i AND `created_at` = ?i LIMIT ?i FOR UPDATE";
		return ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $type, $month_start_at, 1);
	}

	/**
	 * удаляем запись из таблицы
	 */
	public static function delete(int $user_id, int $type, int $month_start_at):void {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// запрос проверен на EXPLAIN (INDEX=`get_by_user_and_type_and_created_at`)
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `type` = ?i AND `created_at` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, $table_name, $user_id, $type, $month_start_at, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * создаем сткруктуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Domain_Usercard_MonthPlan {

		return new Struct_Domain_Usercard_MonthPlan(
			$row["row_id"],
			$row["user_id"],
			$row["type"],
			$row["plan_value"],
			$row["user_value"],
			$row["created_at"],
			$row["updated_at"]
		);
	}

	/**
	 *
	 * функция возвращает название таблицы
	 */
	protected static function _getTableName():string {

		return self::_TABLE_KEY;
	}
}