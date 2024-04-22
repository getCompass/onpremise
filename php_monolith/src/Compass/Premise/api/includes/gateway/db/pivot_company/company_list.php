<?php

namespace Compass\Premise;

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
	 * Получить запись команды
	 *
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotCompany_Company
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
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
	 * Получить список команд
	 *
	 * @param array $company_id_list
	 * @param bool  $is_assoc
	 *
	 * @return array
	 * @throws ParseFatalException
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
	 * Получить список команд по статусу
	 *
	 * @param int $status
	 *
	 * @return Struct_Db_PivotCompany_Company[]
	 * @throws ParseFatalException
	 */
	public static function getByStatus(int $status):array {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// запрос проверен на EXPLAIN (INDEX=`status`).
		$query = "SELECT * FROM `?p` WHERE `status` = ?i ORDER BY `company_id` DESC LIMIT ?i";
		$list   = ShardingGateway::database($shard_key)->getAll($query, $table_name, $status, 10000);

		$object_list = [];
		foreach ($list as $row) {
			$object_list[] = self::_rowToObject($row);
		}

		return $object_list;
	}

	/**
	 * Получить число активных команд
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function getActiveCount():int {

		$shard_key  = self::_getDbKey(1);
		$table_name = self::_getTableKey(1);

		// запрос проверен на EXPLAIN (INDEX=`status`)
		$query = "SELECT COUNT(*) AS `count` FROM `?p` WHERE `status` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE, 1);

		return $row["count"] ?? 0;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки БД
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotCompany_Company
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
	 * Получаем таблицу
	 *
	 * @param int $company_id
	 *
	 * @return string
	 */
	protected static function _getTableKey(int $company_id):string {

		$shard = ceil($company_id / 1000000);

		$table_shard = self::_TABLE_KEY . "_$shard";

		return $table_shard;
	}
}
