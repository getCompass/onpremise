<?php

namespace Compass\Company;

use JetBrains\PhpStorm\Pure;

/**
 * Класс-интерфейс для таблицы company_data.entry_join_link_list
 */
class Gateway_Db_CompanyData_EntryJoinLinkList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "entry_join_link_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для вставки записи
	 *
	 * @param int    $entry_id
	 * @param string $join_link_uniq
	 * @param int    $inviter_user_id
	 *
	 * @return string
	 * @throws \parseException
	 * @throws \queryException
	 * @mixed
	 */
	public static function insert(int $entry_id, string $join_link_uniq, int $inviter_user_id):string {

		$insert = [
			"entry_id"        => $entry_id,
			"join_link_uniq"  => $join_link_uniq,
			"inviter_user_id" => $inviter_user_id,
			"created_at"      => time(),
		];

		// осуществляем запрос
		return ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * получение заявок на найм по join_link_uniq
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getAllByJoinLinkUniq(string $join_link_uniq):array {

		// запрос проверен на EXPLAIN (INDEX=join_link_uniq)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `join_link_uniq` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $join_link_uniq, 1);

		// запрос проверен на EXPLAIN (INDEX=join_link_uniq)
		$query = "SELECT * FROM `?p` WHERE `join_link_uniq` = ?s LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $join_link_uniq, $row["count"]);

		$list = [];
		foreach ($rows as $row) {
			$list[] = self::_rowToObject($row);
		}

		return $list;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 */
	#[Pure]
	protected static function _rowToObject(array $row):Struct_Db_CompanyData_EntryJoinLinkList {

		return new Struct_Db_CompanyData_EntryJoinLinkList(
			$row["entry_id"],
			$row["join_link_uniq"],
			$row["inviter_user_id"],
			$row["created_at"],
		);
	}
}