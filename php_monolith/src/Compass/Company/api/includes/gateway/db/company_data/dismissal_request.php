<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.dismissal_request
 */
class Gateway_Db_CompanyData_DismissalRequest extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "dismissal_request";
	public const    MAX_COUNT  = 10000;

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function insert(int $status, int $creator_user_id, int $dismissal_user_id, array $extra):Struct_Db_CompanyData_DismissalRequest {

		$insert_row = [
			"status"            => $status,
			"created_at"        => time(),
			"updated_at"        => 0,
			"creator_user_id"   => $creator_user_id,
			"dismissal_user_id" => $dismissal_user_id,
			"extra"             => toJson($extra),
		];

		$dismissal_request_id = ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert_row);

		// проверяем что заявка создалась
		$dismissal_request_id = formatInt($dismissal_request_id);
		if ($dismissal_request_id == 0) {
			throw new ParseFatalException("increment in db not working");
		}

		// отдаем структуру заявки
		$insert_row["dismissal_request_id"] = $dismissal_request_id;
		return self::_rowToStruct($insert_row, false);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $dismissal_request_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_DismissalRequest::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE dismissal_request_id = ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $dismissal_request_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $dismissal_request_id):Struct_Db_CompanyData_DismissalRequest {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `dismissal_request_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $dismissal_request_id, 1);

		if (!isset($row["dismissal_request_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * метод для получения записи под обновление
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $dismissal_request_id):Struct_Db_CompanyData_DismissalRequest {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `dismissal_request_id` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $dismissal_request_id, 1);

		if (!isset($row["dismissal_request_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row, false);
	}

	/**
	 * !!СЕРВИСНЫЙ ЗАПРОС, НЕ ИСПОЛЬЗОВАТЬ В ОСНОВНОМ ФУНКЦИОНАЛЕ!!
	 * Получить все записи с пагинацией
	 */
	public static function getAllWithPagination(int $offset):array {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE TRUE LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, self::MAX_COUNT, $offset);

		$list = static::_rowsToStructList($rows);
		return [$list, count($list) >= self::MAX_COUNT];
	}

	/**
	 * Метод для получения массива записей
	 *
	 * @return Struct_Db_CompanyData_DismissalRequest[]
	 */
	public static function getList(array $dismissal_request_id_list, int $limit = 50):array {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `dismissal_request_id` IN (?a) LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $dismissal_request_id_list, $limit);

		return static::_rowsToStructList($rows);
	}

	/**
	 * метод для получения записи по dismissal_user_id
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getByDismissalUserId(int $dismissal_user_id, array $status_list):Struct_Db_CompanyData_DismissalRequest {

		// запрос проверен на EXPLAIN (INDEX=`dismissal_user_id_by_status`)
		$query = "SELECT * FROM `?p` WHERE `dismissal_user_id` = ?i AND status IN (?a) LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $dismissal_user_id, $status_list, 1);

		if (!isset($row["dismissal_request_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * Метод для получения всех записей увольняемого пользователя.
	 *
	 * @return Struct_Db_CompanyData_DismissalRequest[]
	 */
	public static function getFullByDismissalUserId(int $dismissal_user_id):array {

		// запрос проверен на EXPLAIN (INDEX=`user_id_status_duplicate_protector`)
		$query = "SELECT * FROM `?p` WHERE `dismissal_user_id` = ?i LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $dismissal_user_id, static::MAX_COUNT);

		return static::_rowsToStructList($rows);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToStruct(array $row, bool $need_update = true):Struct_Db_CompanyData_DismissalRequest {

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
	 * @return Struct_Db_CompanyData_DismissalRequest[]
	 */
	protected static function _rowsToStructList(array $row_list, bool $need_update = true):array {

		return $need_update
			? Struct_Db_CompanyData_DismissalRequest::constructAndLazyUpdate($row_list)
			: Struct_Db_CompanyData_DismissalRequest::construct($row_list);
	}
}