<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_data.company_join_link_rel
 */
class Gateway_Db_PivotData_CompanyJoinLinkRel extends Gateway_Db_PivotData_Main {

	protected const _TABLE_KEY = "company_join_link_rel";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws cs_RowDuplication
	 * @throws \queryException
	 */
	public static function insert(string $join_link_uniq, int $company_id, int $status_alias):void {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($join_link_uniq);

		$insert = [
			"join_link_uniq" => $join_link_uniq,
			"company_id"     => $company_id,
			"status_alias"   => $status_alias,
			"created_at"     => time(),
			"updated_at"     => 0,
		];

		// осуществляем запрос
		try {
			ShardingGateway::database($shard_key)->insert($table_name, $insert, false);
		} catch (\PDOException $e) {

			// если это дупликат
			if ($e->getCode() == 23000) {
				throw new cs_RowDuplication();
			}

			throw $e;
		}
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(string $join_link_uniq, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($join_link_uniq);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotData_CompanyJoinLinkRel::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `join_link_uniq` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $join_link_uniq, 1);
	}

	/**
	 * достаем запись из таблицы
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(string $join_link_uniq):Struct_Db_PivotData_CompanyJoinLinkRel {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($join_link_uniq);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE join_link_uniq = ?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $join_link_uniq, 1);

		if (!isset($row["join_link_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получает таблицу
	 *
	 */
	protected static function _getTableKey(string $join_link_uniq):string {

		return self::_TABLE_KEY . "_" . substr(sha1($join_link_uniq), -1);
	}

	/**
	 * преобразуем строку записи базы в объект
	 *
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotData_CompanyJoinLinkRel {

		return new Struct_Db_PivotData_CompanyJoinLinkRel(
			$row["join_link_uniq"],
			$row["company_id"],
			$row["status_alias"],
			$row["created_at"],
			$row["updated_at"],
		);
	}
}