<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс-интерфейс для таблицы pivot_company.company_list_{ceil}
 */
class Gateway_Db_PivotCompany_CompanyList extends Gateway_Db_PivotCompany_Main {

	protected const _TABLE_KEY = "company_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для получения записи компании
	 *
	 * @param int $company_id
	 *
	 * @return Struct_Db_PivotCompany_Company
	 * @throws RowNotFoundException
	 * @throws ParseFatalException
	 */
	public static function getOne(int $company_id):Struct_Db_PivotCompany_Company {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE company_id=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $company_id, 1);
		if (!isset($row["company_id"])) {
			throw new RowNotFoundException();
		}

		return self::_rowToObject($row);
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
	 */
	protected static function _getTableKey(int $company_id):string {

		$shard = ceil($company_id / 1000000);

		$table_shard = self::_TABLE_KEY . "_$shard";
		self::_checkExistShard($table_shard, $company_id);

		return $table_shard;
	}
}
