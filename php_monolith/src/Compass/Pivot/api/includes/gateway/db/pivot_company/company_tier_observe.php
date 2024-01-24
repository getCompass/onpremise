<?php

namespace Compass\Pivot;

/**
 * Класс для работы с таблицей pivot_company_{10m}.company_tier_observe
 */
class Gateway_Db_PivotCompany_CompanyTierObserve extends Gateway_Db_PivotCompany_Main {

	/** @var string имя таблицы */
	protected const _TABLE_KEY = "company_tier_observe";

	/**
	 * Добавляет одну запись.
	 */
	public static function insert(int $company_id, int $current_domino_tier, int $expected_domino_tier, array $extra):void {

		$insert = [
			"company_id"           => $company_id,
			"current_domino_tier"  => $current_domino_tier,
			"expected_domino_tier" => $expected_domino_tier,
			"need_work"            => time() + 60,
			"created_at"           => time(),
			"updated_at"           => 0,
			"extra"                => $extra,
		];

		$shard_key = self::_getDbKey($company_id);
		ShardingGateway::database($shard_key)->insert(static::_TABLE_KEY, $insert);
	}

	/**
	 * Возвращает запись компании
	 */
	public static function get(int $company_id):Struct_Db_PivotCompany_CompanyTierObserve {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `company_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey($company_id))->getOne($query, static::_TABLE_KEY, $company_id, 1);
		if (!isset($row["company_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("row not found");
		}

		return self::_fromRow($row);
	}

	/**
	 * Удаляем запись компании
	 */
	public static function delete(int $company_id):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `company_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey($company_id))->delete($query, static::_TABLE_KEY, $company_id, 1);
	}

	/**
	 * Возвращает компании, готовые к релокации
	 * @return Struct_Db_PivotCompany_CompanyTierObserve[]
	 */
	public static function getReadyToRelocationList(int $limit, int $offset):array {

		$shard_key = self::_getDbKey(1);

		// запрос проверен на EXPLAIN (INDEX=expected_domino_tier)
		$query  = "SELECT * FROM `?p` FORCE INDEX (`expected_domino_tier`) WHERE `expected_domino_tier` != ?s LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($shard_key)->getAll($query, static::_TABLE_KEY, 0, $limit, $offset);

		return array_map(static fn(array $el) => static::_fromRow($el), $result);
	}

	// обновляем запись
	public static function set(int $company_id, array $set):void {

		//
		if (!isset($set["updated_at"])) {
			$set["updated_at"] = time();
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `company_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey($company_id))->update($query, static::_TABLE_KEY, $set, $company_id, 1);
	}

	// обновляем need_work компаниям
	public static function updateNeedWorkList(int $shard_company_id, array $company_id_list, int $need_work):void {

		$set = [
			"need_work"  => $need_work,
			"updated_at" => time(),
		];

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `company_id` IN (?a) LIMIT ?i";
		ShardingGateway::database(self::_getDbKey($shard_company_id))->update($query, static::_TABLE_KEY, $set, $company_id_list, count($company_id_list));
	}

	/**
	 * Возвращает записи, подходящие для observe действия.
	 * @return Struct_Db_PivotCompany_CompanyTierObserve[]
	 */
	public static function getForObserve(int $shard_company_id, int $observe_at, int $limit, int $offset):array {

		$shard_key = self::_getDbKey($shard_company_id);

		// запрос проверен на EXPLAIN (INDEX=need_work)
		$query  = "SELECT * FROM `?p` FORCE INDEX(`need_work`) WHERE `need_work` <= ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($shard_key)->getAll($query, static::_TABLE_KEY, $observe_at, $limit, $offset);

		return array_map(static fn(array $el) => static::_fromRow($el), $result);
	}

	/**
	 * Получение количества записей
	 *
	 * @param int $shard_company_id
	 *
	 * @return int
	 */
	public static function getTotalCount(int $shard_company_id):int {

		// запрос проверен на EXPLAIN (INDEX=need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey($shard_company_id))->getOne($query, self::_TABLE_KEY, 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param int $shard_company_id
	 * @param int $need_work
	 *
	 * @return int
	 */
	public static function getExpiredCount(int $shard_company_id, int $need_work):int {

		// запрос проверен на EXPLAIN (INDEX=need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `need_work` < ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey($shard_company_id))->getOne($query, self::_TABLE_KEY, $need_work, 1);
		return $row["count"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Конвертирует запись в структуру.
	 */
	protected static function _fromRow(array $row):Struct_Db_PivotCompany_CompanyTierObserve {

		$row["extra"] = fromJson($row["extra"]);
		return new Struct_Db_PivotCompany_CompanyTierObserve(...$row);
	}
}