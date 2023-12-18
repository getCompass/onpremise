<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_member.usercard_respect_list
 */
class Gateway_Db_CompanyMember_UsercardRespectList extends Gateway_Db_CompanyMember_Main {

	protected const _TABLE_KEY = "usercard_respect_list";

	/**
	 * добавляем новый респект пользователя в базу
	 *
	 * @throws \parseException|\queryException
	 */
	public static function add(int $user_id, int $creator_user_id, int $type, string $respect_text, int $created_at):Struct_Domain_Usercard_Respect {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		$insert_row = [
			"user_id"         => $user_id,
			"creator_user_id" => $creator_user_id,
			"type"            => $type,
			"is_deleted"      => 0,
			"created_at"      => $created_at,
			"updated_at"      => 0,
			"respect_text"    => $respect_text,
			"data"            => Type_User_Card_Respect::initData(),
		];

		$respect_id = ShardingGateway::database($shard_key)->insert($table_name, $insert_row);

		$respect_id = formatInt($respect_id);
		if ($respect_id == 0) {
			throw new ParseFatalException("increment in db not working");
		}

		$insert_row["respect_id"] = $respect_id;

		return self::_rowToObject($insert_row);
	}

	/**
	 * получаем респект пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $user_id, int $respect_id):Struct_Domain_Usercard_Respect {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `respect_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $respect_id, 1);

		if (!isset($row["respect_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$row["data"] = fromJson($row["data"]);
		return self::_rowToObject($row);
	}

	/**
	 * метод для получения последних записей
	 *
	 * @return Struct_Domain_Usercard_Respect[]
	 */
	public static function getLastRespectList(int $user_id, int $limit):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_user_id_is_deleted`)
		$query = "SELECT * FROM `?p` FORCE INDEX(`get_by_user_id_is_deleted`) 
				WHERE `user_id` = ?i AND `is_deleted` = ?i ORDER BY `created_at` DESC, `respect_id` DESC LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, 0, $limit);

		$obj_list = [];
		foreach ($list as $row) {

			$row["data"] = fromJson($row["data"]);
			$obj_list[]  = self::_rowToObject($row);
		}
		return $obj_list;
	}

	/**
	 * метод для получения записей после respect_id
	 *
	 * @return Struct_Domain_Usercard_Respect[]
	 */
	public static function getRespectListAfterId(int $user_id, int $last_respect_id, int $limit):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (FORCE INDEX=`get_by_user_id_is_deleted`)
		$query = "SELECT * FROM `?p` FORCE INDEX(`get_by_user_id_is_deleted`) 
				WHERE `user_id` = ?i AND `respect_id` < ?i AND `is_deleted` = ?i ORDER BY `created_at` DESC, `respect_id` DESC LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $last_respect_id, 0, $limit);

		$obj_list = [];
		foreach ($list as $row) {

			$row["data"] = fromJson($row["data"]);
			$obj_list[]  = self::_rowToObject($row);
		}
		return $obj_list;
	}

	/**
	 * обновляем запись респекта в базе
	 */
	public static function set(int $user_id, int $respect_id, array $set):void {

		$set["updated_at"] = $set["updated_at"] ?? time();

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `respect_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $respect_id, 1);
	}

	/**
	 * обновляем запись респекта в базе по его respect_id
	 */
	public static function setByRespectId(int $respect_id, array $set):void {

		$set["updated_at"] = $set["updated_at"] ?? time();

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE respect_id = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $respect_id, 1);
	}

	/**
	 * Получаем список респектов по их id
	 *
	 * @return Struct_Domain_Usercard_Respect[]
	 */
	public static function getListByIdList(array $respect_id_list):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE respect_id IN (?a) ORDER BY created_at DESC, respect_id DESC LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $respect_id_list, count($respect_id_list));

		$obj_list = [];
		foreach ($list as $k => $row) {

			$row["data"]  = fromJson($row["data"]);
			$obj_list[$k] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * получаем записи респектов за период
	 */
	public static function getAllByPeriod(int $creator_user_id, int $from_date_at, int $to_date_at, int $limit, int $offset):array {

		// корректируем время
		[$from_date_at, $to_date_at] = self::_getCorrectedPeriodDate($from_date_at, $to_date_at);

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_creator_user_and_is_deleted_and_created_at`)
		$query = "SELECT * FROM `?p` FORCE INDEX(`get_by_creator_user_and_is_deleted_and_created_at`) 
		WHERE `creator_user_id` = ?i AND `created_at` >= ?i AND `created_at` <= ?i AND `is_deleted` = ?i
		ORDER BY `created_at` DESC, `respect_id` DESC LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $creator_user_id, $from_date_at, $to_date_at, 0, $limit, $offset);

		$obj_list = [];
		foreach ($list as $row) {

			$row["data"] = fromJson($row["data"]);
			$obj_list[]  = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * помечаем респект удаленным для создателя респекта
	 *
	 * @throws \parseException
	 */
	public static function deleteForCreator(int $creator_user_id, int $respect_id):void {

		assertTestServer();

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		$set = [
			"is_deleted" => 1,
			"updated_at" => time(),
		];

		// формируем и осуществляем запрос (INDEX не используется, так как метод только для тестовых серверов)
		$query = "UPDATE `?p` SET ?u WHERE creator_user_id = ?i AND `respect_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $creator_user_id, $respect_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * создаем сткруктуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Domain_Usercard_Respect {

		return new Struct_Domain_Usercard_Respect(
			$row["respect_id"],
			$row["type"],
			$row["user_id"],
			$row["creator_user_id"],
			$row["is_deleted"],
			$row["created_at"],
			$row["updated_at"],
			$row["respect_text"],
			Type_User_Card_Respect::actualData($row["data"])
		);
	}

	/**
	 * функция возвращает название таблицы
	 */
	protected static function _getTableName():string {

		return self::_TABLE_KEY;
	}

	/**
	 * !!! Корректируем время для корректного получения планов.
	 * !!! Так как до апреля (включительно) создание плана происходило по гринвичу
	 *
	 * за апрель, получаем данные за период с начала месяца по гринвичу, НО(!) по конец месяца по времени сервера
	 * так как с мая у нас будут писаться корректно данные.
	 *
	 * @param int $from_date
	 * @param int $to_date
	 *
	 * @return array
	 */
	protected static function _getCorrectedPeriodDate(int $from_date, int $to_date):array {

		// если это любой месяц до апреля
		// 1648760400 - Mar 31 2022 21:00:00 GMT+00 || Apr 01 2022 00:00:00 GMT+03 (MSK)
		$timestamp_of_transition = 1648760400;
		if ($to_date <= $timestamp_of_transition) {

			// отходим от стыка месяцев, чтоб не получить предыдущий месяц
			$from_date = monthStartOnGreenwich($from_date + DAY1);
			$to_date   = monthEndOnGreenwich($to_date - DAY1);
		}

		// если это апрель
		// 1651352400 - Apr 30 2022 21:00:00 GMT+00 || May 01 2022 00:00:00 GMT+03 (MSK)
		if ($from_date >= $timestamp_of_transition && $to_date <= 1651352400) {

			// отходим от стыка месяцев, чтоб не получить предыдущий месяц
			$from_date = monthStartOnGreenwich($from_date + DAY1);
			$to_date   = monthEnd($to_date - DAY1);
		}

		return [$from_date, $to_date];
	}
}