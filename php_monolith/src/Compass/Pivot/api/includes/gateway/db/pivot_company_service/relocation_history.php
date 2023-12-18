<?php

namespace Compass\Pivot;

/**
 * Шлюз для работы с таблицей БД pivot_company_service.relocation_history
 * Таблица хранит историю переездов компаний между домино.
 */
class Gateway_Db_PivotCompanyService_RelocationHistory extends Gateway_Db_PivotCompanyService_Main {

	/** @var string имя таблицы базы данных */
	protected const _TABLE_KEY = "relocation_history";

	/**
	 * Создает новую запись в истории релокации компаний.
	 */
	public static function insert(int $company_id, string $source_domino_id, string $target_domino_id, array $extra = []):int {

		return ShardingGateway::database(static::_DB_KEY)->insert(static::_TABLE_KEY, [
			"is_success"       => 0,
			"created_at"       => time(),
			"updated_at"       => 0,
			"finished_at"      => 0,
			"company_id"       => $company_id,
			"source_domino_id" => $source_domino_id,
			"target_domino_id" => $target_domino_id,
			"extra"            => $extra,
		]);
	}

	/**
	 * Выполняет обновление записи.
	 */
	public static function update(int $relocation_id, array $set):void {

		// добавляем время обновления записи
		$set["updated_at"] = time();

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `relocation_id` = ?s LIMIT ?i";
		ShardingGateway::database(static::_DB_KEY)->update($query, static::_TABLE_KEY, $set, $relocation_id, 1);
	}

	/**
	 * Выполняет обновление записи.
	 * @return Struct_Db_PivotCompanyService_RelocationHistory[]
	 */
	public static function getAllByCompany(int $company_id, int $limit = 10, int $offset = 0):array {

		// EXPLAIN get_by_company_id
		$query  = "SELECT * FROM `?t` WHERE `company_id` = ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database(static::_DB_KEY)->getAll($query, static::_TABLE_KEY, $company_id, $limit, $offset);

		return static::_convertArray($result);
	}

	/**
	 * Выполняет обновление записи.
	 */
	public static function getLastByCompany(int $company_id):Struct_Db_PivotCompanyService_RelocationHistory {

		// EXPLAIN get_by_company_id KEY
		$query  = "SELECT * FROM `?t` WHERE `company_id` = ?i ORDER BY `created_at` DESC LIMIT ?i";
		$result = ShardingGateway::database(static::_DB_KEY)->getOne($query, static::_TABLE_KEY, $company_id, 1);

		return static::_convert($result);
	}

	# region protected-shared

	/**
	 * Форматирует в массив структур все переданные элементы.
	 * @return Struct_Db_PivotCompanyService_RelocationHistory[]
	 */
	protected static function _convertArray(array $input):array {

		return array_map(fn(array $el):Struct_Db_PivotCompanyService_RelocationHistory => static::_convert($el), $input);
	}

	/**
	 * Форматирует в структуру один элемент.
	 */
	protected static function _convert(array $input):Struct_Db_PivotCompanyService_RelocationHistory {

		return new Struct_Db_PivotCompanyService_RelocationHistory(
			(int) $input["relocation_id"],
			(int) $input["is_success"],
			(int) $input["created_at"],
			(int) $input["updated_at"],
			(int) $input["finished_at"],
			(int) $input["company_id"],
			$input["source_domino_id"],
			$input["target_domino_id"],
			fromJson($input["extra"]),
		);
	}

	# endregion protected
}