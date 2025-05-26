<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.user_banned
 */
class Gateway_Db_PivotUser_UserBanned extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "user_banned";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @param Struct_Db_PivotUser_UserBanned $user
	 *
	 * @return string
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotUser_UserBanned $user_banned):string {

		$shard_key  = self::_getDbKey($user_banned->user_id);
		$table_name = self::_getTableKey();

		$insert = [
			"user_id"    => $user_banned->user_id,
			"comment"    => $user_banned->comment,
			"created_at" => $user_banned->created_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_PivotUser_UserBanned
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(int $user_id):Struct_Db_PivotUser_UserBanned {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey();

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new RowNotFoundException("user_banned row not found");
		}

		return self::_rowToStruct($row);
	}

	/**
	 * Удаляем запись
	 */
	public static function delete(int $user_id):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey();

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `user_id`=?i LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, $table_name, $user_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получает таблицу
	 *
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}

	/**
	 * Форматирует запись в структуру
	 *
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotUser_UserBanned {

		return new Struct_Db_PivotUser_UserBanned(
			$row["user_id"],
			$row["comment"],
			$row["created_at"]
		);
	}
}