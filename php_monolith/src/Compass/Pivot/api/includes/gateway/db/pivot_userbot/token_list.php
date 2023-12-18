<?php

namespace Compass\Pivot;

/**
 * Интерфейс для работы в БД со связью токена бота и самим ботом
 */
class Gateway_Db_PivotUserbot_TokenList extends Gateway_Db_PivotUserbot_Main {

	protected const _TABLE_KEY = "token_list";

	/**
	 * вставляем новую запись
	 *
	 * @throws cs_RowDuplication
	 * @throws \queryException
	 */
	public static function insert(string $token, string $userbot_id, int $created_at, array $extra):void {

		$insert = [
			"token"      => $token,
			"userbot_id" => $userbot_id,
			"created_at" => $created_at,
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
	 * получаем запись токена
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(string $token):Struct_Db_PivotUserbot_Token {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `token` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $token, 1);

		if (!isset($row["token"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * обновляем запись
	 */
	public static function set(string $token, array $set):void {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `token` = ?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $token, 1);
	}

	/**
	 * получаем последнюю запись (Только для тестов!!!)
	 */
	public static function getLastRow():Struct_Db_PivotUserbot_Token {

		assertTestServer();

		// только для тестирования (без EXPLAIN)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE ?i LIMIT ?i";
		$count = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, 1, 1)["count"];

		// только для тестирования (без EXPLAIN)
		$query = "SELECT * FROM `?p` WHERE ?i=?i ORDER BY `created_at` ASC LIMIT ?i OFFSET ?i ";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, 1, 1, 1, $count - 1);

		if (!isset($row["token"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * удаляем запись
	 */
	public static function delete(string $token):void {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `token` = ?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, $token, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * форматируем строку базы в структуру
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotUserbot_Token {

		return new Struct_Db_PivotUserbot_Token(
			$row["token"],
			$row["userbot_id"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["extra"])
		);
	}
}