<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс-интерфейс для таблицы pivot_phone.phone_banned
 */
class Gateway_Db_PivotPhone_PhoneBanned extends Gateway_Db_PivotPhone_Main {

	protected const _TABLE_KEY = "phone_banned";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @param Struct_Db_PivotPhone_PhoneBanned $phone_banned
	 *
	 * @return string
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotPhone_PhoneBanned $phone_banned):string {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"phone_number_hash" => $phone_banned->phone_number_hash,
			"comment"           => $phone_banned->comment,
			"created_at"        => $phone_banned->created_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @param string $phone_number_hash
	 *
	 * @return Struct_Db_PivotPhone_PhoneBanned
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(string $phone_number_hash):Struct_Db_PivotPhone_PhoneBanned {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `phone_number_hash`=?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $phone_number_hash, 1);

		if (!isset($row["phone_number_hash"])) {
			throw new RowNotFoundException("phone_banned row not found");
		}

		return self::_rowToStruct($row);
	}

	/**
	 * Удаляем запись
	 */
	public static function delete(string $phone_number_hash):void {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `phone_number_hash`=?s LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, $table_name, $phone_number_hash, 1);
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
	protected static function _rowToStruct(array $row):Struct_Db_PivotPhone_PhoneBanned {

		return new Struct_Db_PivotPhone_PhoneBanned(
			$row["phone_number_hash"],
			$row["comment"],
			$row["created_at"]
		);
	}
}