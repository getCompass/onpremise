<?php

namespace Compass\Pivot;

/**
 * Интерфейс для работы в БД со списком ботов
 */
class Gateway_Db_PivotUserbot_UserbotList extends Gateway_Db_PivotUserbot_Main {

	protected const _TABLE_KEY = "userbot_list";

	/**
	 * вставляем новую запись
	 *
	 * @throws cs_RowDuplication
	 * @throws \queryException
	 */
	public static function insert(string $userbot_id, int $user_id, int $company_id, int $status, int $time, array $extra):void {

		$insert = [
			"userbot_id" => $userbot_id,
			"company_id" => $company_id,
			"status"     => $status,
			"user_id"    => $user_id,
			"created_at" => $time,
			"updated_at" => 0,
			"extra"      => $extra,
		];

		try {
			ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert, false);
		} catch (\PDOException $e) {

			// если это дупликат
			if ($e->getCode() == 23000) {
				throw new cs_RowDuplication();
			}

			throw $e;
		}
	}

	/**
	 * получаем бота
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(string $userbot_id):Struct_Db_PivotUserbot_Userbot {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `userbot_id` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $userbot_id, 1);

		if (!isset($row["userbot_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * обновляем запись
	 */
	public static function set(string $userbot_id, array $set):void {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `userbot_id` = ?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $userbot_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * форматируем строку базы в структуру
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotUserbot_Userbot {

		return new Struct_Db_PivotUserbot_Userbot(
			$row["userbot_id"],
			$row["status"],
			$row["company_id"],
			$row["user_id"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["extra"])
		);
	}
}