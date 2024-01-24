<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.hiring_request
 */
class Gateway_Db_CompanyData_HiringRequest extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "hiring_request";
	public const    MAX_COUNT  = 10000;

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для создания записи
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function insert(int $status, string $join_link_uniq, int $entry_id, int $hired_by_user_id, array $extra, int $candidate_user_id):Struct_Db_CompanyData_HiringRequest {

		$insert_row = [
			"status"            => $status,
			"join_link_uniq"    => $join_link_uniq,
			"entry_id"          => $entry_id,
			"hired_by_user_id"  => $hired_by_user_id,
			"created_at"        => time(),
			"updated_at"        => 0,
			"candidate_user_id" => $candidate_user_id,
			"extra"             => toJson($extra),
		];

		$hiring_request_id = ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert_row);

		$hiring_request_id = formatInt($hiring_request_id);
		if ($hiring_request_id == 0) {
			throw new ParseFatalException("increment in db not working");
		}
		$insert_row["hiring_request_id"] = $hiring_request_id;
		return self::_rowToStruct($insert_row, false);
	}

	/**
	 * Метод для обновления записи
	 *
	 * @throws cs_RowNotUpdated
	 * @throws \parseException
	 */
	public static function set(int $hiring_request_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_HiringRequest::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query        = "UPDATE `?p` SET ?u WHERE hiring_request_id = ?i LIMIT ?i";
		$update_count = ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $hiring_request_id, 1);
		if ($update_count === 0) {
			throw new cs_RowNotUpdated();
		}

		return $update_count;
	}

	/**
	 * Метод для обновления записи
	 *
	 * @throws cs_RowNotUpdated
	 * @throws \parseException
	 */
	public static function setWhereStatus(int $hiring_request_id, array $set, int $status):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_HiringRequest::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query        = "UPDATE `?p` SET ?u WHERE hiring_request_id = ?i AND `status` = ?i LIMIT ?i";
		$update_count = ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $hiring_request_id, $status, 1);
		if ($update_count === 0) {
			throw new cs_RowNotUpdated();
		}

		return $update_count;
	}

	/**
	 * Метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function setByCandidateUserId(int $candidate_user_id, array $set):void {

		if ($candidate_user_id === 0) {
			return;
		}

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_HiringRequest::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// запрос проверен на EXPLAIN (INDEX=`candidate_user_id`)
		$query = "UPDATE `?p` SET ?u WHERE candidate_user_id = ?i LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $candidate_user_id, 1);
	}

	/**
	 * Метод для получения заявок по пользователю
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 */
	public static function getByCandidateUserId(int $candidate_user_id):array {

		// запрос проверен на EXPLAIN (INDEX=`candidate_user_id`)
		$query = "SELECT * FROM `?p` WHERE `candidate_user_id` = ?i LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $candidate_user_id, static::MAX_COUNT);

		return static::_rowsToStructList($rows);
	}

	/**
	 * Метод для получения заявок по пользователю - последней
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function getByCandidateUserIdLast(int $candidate_user_id):Struct_Db_CompanyData_HiringRequest {

		// запрос проверен на EXPLAIN (INDEX=`candidate_user_id`)
		$query = "SELECT * FROM `?p` WHERE `candidate_user_id` = ?i ORDER BY hiring_request_id DESC LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $candidate_user_id, 1);

		if (!isset($row["hiring_request_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * Метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $hiring_request_id):Struct_Db_CompanyData_HiringRequest {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `hiring_request_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $hiring_request_id, 1);

		if (!isset($row["hiring_request_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * !!СЕРВИСНЫЙ ЗАПРОС, НЕ ИСПОЛЬЗОВАТЬ В ОСНОВНОМ ФУНКЦИОНАЛЕ!!
	 * Получить все записи с пагинацией
	 */
	public static function getAllWithPagination(int $offset):array {

		// формируем и осуществляем запрос
		// индекс не требуется, сервисный запрос
		$query = "SELECT * FROM `?p` WHERE TRUE LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, self::MAX_COUNT, $offset);

		$list = static::_rowsToStructList($rows);
		return [$list, count($list) >= self::MAX_COUNT];
	}

	/**
	 *  Метод для получения записи на обновление
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $hiring_request_id):Struct_Db_CompanyData_HiringRequest {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `hiring_request_id` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $hiring_request_id, 1);

		if (!isset($row["hiring_request_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row, false);
	}

	/**
	 * Метод для получения массива заявок по candidate_user_id
	 */
	public static function getByCandidateUserIdList(array $candidate_user_id_list, array $status_list, int $limit):array {

		// получаем массив заявок по массиву id пользователей
		// запрос проверен на EXPLAIN (INDEX=`candidate_user_id`)
		$query = "SELECT * FROM `?p` WHERE `candidate_user_id` IN (?a) AND `status` IN (?a) LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $candidate_user_id_list, $status_list, $limit);

		return static::_rowsToStructList($rows);
	}

	/**
	 * Метод для получения массива записей
	 */
	public static function getList(array $hiring_request_id_list, int $limit):array {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `hiring_request_id` IN (?a) LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $hiring_request_id_list, $limit);

		return static::_rowsToStructList($rows);
	}

	/**
	 * Метод для получения записи по entry_id
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getByEntryId(int $entry_id):Struct_Db_CompanyData_HiringRequest {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`entry_id`)
		$query = "SELECT * FROM `?p` WHERE `entry_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $entry_id, 1);

		if (!isset($row["hiring_request_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * получаем заявки по статусу
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 * @throws ParseFatalException
	 */
	public static function getListByStatus(int $status, int $limit, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX=`status.created_at`)
		$query = "SELECT * FROM `?p` WHERE `status` = ?i ORDER BY `created_at` DESC LIMIT ?i OFFSET ?i;";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $status, $limit, $offset);

		$obj_list = [];
		foreach ($list as $row) {
			$obj_list[] = self::_rowToStruct($row);
		}

		return $obj_list;
	}

	/**
	 * получаем заявки по статусам без сортировки
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 * @throws ParseFatalException
	 */
	public static function getListByStatusList(array $status_list, int $limit, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX=`status.created_at`)
		$query = "SELECT * FROM `?p` WHERE `status` IN (?a) LIMIT ?i OFFSET ?i;";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $status_list, $limit, $offset);

		$obj_list = [];
		foreach ($list as $row) {
			$obj_list[] = self::_rowToStruct($row);
		}

		return $obj_list;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToStruct(array $row, bool $need_update = true):Struct_Db_CompanyData_HiringRequest {

		// делаем по-тупому, чтобы в ленивые оброаботчики пробросить
		// наше заявку на потенциальный ремонт
		$list = static::_rowsToStructList([$row], $need_update);
		return reset($list);
	}

	/**
	 * Формирует массив из записей.
	 *
	 * @param array $row_list
	 * @param bool  $need_update
	 *
	 * @return Struct_Db_CompanyData_HiringRequest[]
	 */
	protected static function _rowsToStructList(array $row_list, bool $need_update = true):array {

		return $need_update
			? Struct_Db_CompanyData_HiringRequest::constructAndLazyUpdate($row_list)
			: Struct_Db_CompanyData_HiringRequest::construct($row_list);
	}
}