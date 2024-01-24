<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * класс-интерфейс для таблицы pivot_company.company_list_{ceil}
 */
class Gateway_Db_PivotCompany_CompanyList extends Gateway_Db_PivotCompany_Main {

	protected const _TABLE_KEY = "company_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для создания записи
	 *
	 * @throws \queryException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @long
	 */
	public static function insert(
		int    $company_id,
		int    $status,
		int    $created_at,
		int    $updated_at,
		int    $avatar_color_id,
		int    $created_by_user_id,
		string $domino_id,
		string $name,
		string $url,
		array  $extra
	):string {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		$insert = [
			"company_id"         => $company_id,
			"status"             => $status,
			"created_at"         => $created_at,
			"updated_at"         => $updated_at,
			"avatar_color_id"    => $avatar_color_id,
			"created_by_user_id" => $created_by_user_id,
			"domino_id"          => $domino_id,
			"name"               => $name,
			"url"                => $url,
			"extra"              => $extra,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * Метод для создания/обновления записи
	 * !!! используем только на тестовых
	 *
	 * @throws \parseException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @long
	 */
	public static function insertOrUpdate(
		int    $company_id,
		int    $status,
		int    $created_at,
		int    $updated_at,
		int    $avatar_color_id,
		int    $created_by_user_id,
		string $name,
		string $url,
		array  $extra
	):string {

		// !!! используем только на тестовых
		assertTestServer();

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		$insert = [
			"company_id"         => $company_id,
			"status"             => $status,
			"created_at"         => $created_at,
			"updated_at"         => $updated_at,
			"avatar_color_id"    => $avatar_color_id,
			"created_by_user_id" => $created_by_user_id,
			"name"               => $name,
			"url"                => $url,
			"extra"              => $extra,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insertOrUpdate($table_name, $insert);
	}

	/**
	 * Метод для обновления записи
	 *
	 * @param int   $company_id
	 * @param array $set
	 *
	 * @return int
	 * @throws ParseFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function set(int $company_id, array $set):int {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotCompany_Company::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE company_id = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $company_id, 1);
	}

	/**
	 * Метод для обновления записи
	 *
	 * @param int   $company_id
	 * @param int   $status
	 * @param array $set
	 *
	 * @return int
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_RowNotUpdated
	 * @throws \parseException
	 */
	public static function setByStatus(int $company_id, int $status, array $set):int {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotCompany_Company::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query        = "UPDATE `?p` SET ?u WHERE company_id = ?i AND status = ?i LIMIT ?i";
		$update_count = ShardingGateway::database($shard_key)->update($query, $table_name, $set, $company_id, $status, 1);
		if ($update_count === 0) {
			throw new cs_RowNotUpdated();
		}

		return $update_count;
	}

	/**
	 * Метод для получения записи компании
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getOne(int $company_id):Struct_Db_PivotCompany_Company {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE company_id=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $company_id, 1);
		if (!isset($row["company_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Метод для получения записи компании на обновление
	 *
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotCompany_Company
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $company_id):Struct_Db_PivotCompany_Company {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE company_id=?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $company_id, 1);
		if (!isset($row["company_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Получаем список компаний
	 *
	 * @return Struct_Db_PivotCompany_Company[]
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getList(array $company_id_list, bool $is_assoc = false):array {

		$grouped_by_shard_list = [];
		foreach ($company_id_list as $company_id) {

			$key                           = sprintf("%s.%s", self::_getDbKey($company_id), self::_getTableKey($company_id));
			$grouped_by_shard_list[$key][] = $company_id;
		}

		$query = "SELECT * FROM `?p` WHERE `company_id` IN (?a) LIMIT ?i";

		// делаем запросы
		$grouped_query_list = [];
		foreach ($grouped_by_shard_list as $key => $company_id_list) {

			// формируем и осуществляем запрос
			[$shard_key, $table_name] = explode(".", $key);
			$grouped_query_list[] = ShardingGateway::database($shard_key)->getAll($query, $table_name, $company_id_list, count($company_id_list));
		}

		// собираем массив объектов
		$output_list = [];
		foreach ($grouped_query_list as $query_list) {

			foreach ($query_list as $row) {

				$object = self::_rowToObject($row);
				if ($is_assoc) {
					$output_list[$object->company_id] = $object;
				} else {
					$output_list[] = $object;
				}
			}
		}

		return $output_list;
	}

	/**
	 * Получаем количество компаний в нужных статусах
	 *
	 * @return Struct_Db_PivotCompany_Company[]
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getStatusCountList(array $company_id_list, array $company_count_status_list):array {

		// формируем и осуществляем запрос
		$grouped_by_shard_list = [];
		foreach ($company_id_list as $company_id) {

			$sharding_key                           = sprintf("%s.%s", self::_getDbKey($company_id), self::_getTableKey($company_id));
			$grouped_by_shard_list[$sharding_key][] = $company_id;
		}
		foreach ($grouped_by_shard_list as $sharding_key => $full_company_id_list) {

			// формируем и осуществляем запрос
			[$shard_key, $table_name] = explode(".", $sharding_key);
			$query      = "SELECT `status` FROM `?p` WHERE `company_id` IN (?a) LIMIT ?i";
			$query_list = ShardingGateway::database($shard_key)->getAll($query, $table_name, $company_id_list, count($company_id_list));
			foreach ($query_list as $row) {

				// если нужно считать этот статус
				if (isset($company_count_status_list[$row["status"]])) {
					$company_count_status_list[$row["status"]]++;
				}
			}
		}

		return $company_count_status_list;
	}

	/**
	 * Возвращает список всех компаний.
	 * Нужно только для crm, пока ищет только в одном шарде.
	 *
	 */
	public static function getFullList(int $count = 1000, int $offset = 0):array {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		$query    = "SELECT * FROM `?p` WHERE TRUE LIMIT ?i OFFSET ?i";
		$row_list = ShardingGateway::database($shard_key)->getAll($query, $table_name, $count, $offset);

		// собираем массив объектов
		$output_list = [];
		foreach ($row_list as $row) {
			$output_list[] = self::_rowToObject($row);
		}

		return $output_list;
	}

	/**
	 * Возвращает список всех активных и свободных компаний.
	 * Нужно только для crm, пока ищет только в одном шарде.
	 *
	 */
	public static function getActiveList(int $count = 1000, int $offset = 0):array {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		$query    = "SELECT * FROM `?p` WHERE `status` != ?i LIMIT ?i OFFSET ?i";
		$row_list = ShardingGateway::database($shard_key)->getAll($query, $table_name, Domain_Company_Entity_Company::COMPANY_STATUS_INVALID, $count, $offset);

		// собираем массив объектов
		$output_list = [];
		foreach ($row_list as $row) {
			$output_list[] = self::_rowToObject($row);
		}

		return $output_list;
	}

	/**
	 * Возвращает список компаний, соответствующих статусам.
	 *
	 * @return Struct_Db_PivotCompany_Company[]
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	public static function getByStatusList(array $status_list, int $count = 1000, int $offset = 0):array {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		$query    = "SELECT * FROM `?p` WHERE `status` IN (?a) LIMIT ?i OFFSET ?i";
		$row_list = ShardingGateway::database($shard_key)->getAll($query, $table_name, $status_list, $count, $offset);

		// собираем массив объектов
		$output_list = [];
		foreach ($row_list as $row) {
			$output_list[] = self::_rowToObject($row);
		}

		return $output_list;
	}

	/**
	 * Возвращает число активных компаний
	 * Нужно только для crm, пока ищет только в одном шарде.
	 *
	 */
	public static function getActiveCount():int {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// запрос проверен на EXPLAIN (INDEX=`status`)
		$query = "SELECT COUNT(*) AS `count` FROM `?p` WHERE `status` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE, 1);

		return $row["count"] ?? 0;
	}

	/**
	 * Получаем количество созданных компаний за промежуток времени
	 */
	public static function getCountByInterval(int $from_date, int $to_date):int {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// запрос проверен на EXPLAIN (INDEX=`created_at`)
		$query  = "SELECT COUNT(*) AS `count` FROM `?p` WHERE `created_at` BETWEEN ?i AND ?i AND `created_by_user_id` != ?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, $from_date, $to_date, 0, 1);

		return (int) $result["count"];
	}

	/**
	 * Вжух и обновляем сразу несколько компаний
	 *
	 * @param array $company_id_list
	 * @param array $set
	 *
	 * @return int
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	public static function setList(array $company_id_list, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotCompany_Company::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		$grouped_by_shard_list = [];
		foreach ($company_id_list as $company_id) {

			$key                           = sprintf("%s.%s", self::_getDbKey($company_id), self::_getTableKey($company_id));
			$grouped_by_shard_list[$key][] = $company_id;
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query             = "UPDATE `?p` SET ?u WHERE company_id IN (?a) LIMIT ?i";
		$updated_row_count = 0;
		foreach ($grouped_by_shard_list as $key => $one_shard_company_id_list) {

			[$shard_key, $table_name] = explode(".", $key);
			$updated_row_count += ShardingGateway::database($shard_key)->update($query, $table_name, $set, $one_shard_company_id_list, count($one_shard_company_id_list));
		}

		return $updated_row_count;
	}

	/**
	 * Метод для удаления записи
	 *
	 * @param int $company_id
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	public static function delete(int $company_id):void {

		assertTestServer(); // только для тестовых

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `company_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, $table_name, $company_id, 1);
	}

	/**
	 * Возвращает количество занятых компаний.
	 *
	 * @return int
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getOwnedCompanyCount():int {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// запрос проверен на EXPLAIN (индекс не используется)
		// запрос вызывается раз в день для сбора аналитики по количеству компаний, индекс для такого нецелесообразен
		$query  = "SELECT COUNT(*) AS count FROM `?p` WHERE created_by_user_id != ?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, 0, 1);

		// возвращаем массив идентификаторов
		return $result["count"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Создаем сткруктуру из строки бд
	 *
	 */
	protected static function _rowToObject(array $row):Struct_Db_PivotCompany_Company {

		$extra = fromJson($row["extra"]);

		return new Struct_Db_PivotCompany_Company(
			$row["company_id"],
			$row["is_deleted"],
			$row["status"],
			$row["created_at"],
			$row["updated_at"],
			$row["deleted_at"],
			$row["avatar_color_id"],
			$row["created_by_user_id"],
			$row["partner_id"],
			$row["domino_id"],
			$row["name"],
			$row["url"],
			$row["avatar_file_map"],
			$extra,
		);
	}

	/**
	 * Получает таблицу
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	protected static function _getTableKey(int $company_id):string {

		$shard = ceil($company_id / 1000000);

		$table_shard = self::_TABLE_KEY . "_$shard";
		self::_checkExistShard($table_shard, $company_id);

		return $table_shard;
	}
}
