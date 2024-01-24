<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_member.usercard_loyalty_list
 */
class Gateway_Db_CompanyMember_UsercardLoyaltyList extends Gateway_Db_CompanyMember_Main {

	public const _TABLE_KEY = "usercard_loyalty_list";

	/**
	 * метод для создания записи
	 *
	 * @throws \parseException|\queryException
	 */
	public static function insert(int $user_id, int $creator_user_id, int $is_deleted, int $created_at, int $updated_at, string $comment_text, array $data):Struct_Domain_Usercard_Loyalty {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		$insert_row = [
			"user_id"         => $user_id,
			"creator_user_id" => $creator_user_id,
			"is_deleted"      => $is_deleted,
			"created_at"      => $created_at,
			"updated_at"      => $updated_at,
			"comment_text"    => $comment_text,
			"data"            => $data,
		];

		// осуществляем запрос
		$loyalty_id = ShardingGateway::database($shard_key)->insert($table_name, $insert_row);

		$loyalty_id = formatInt($loyalty_id);
		if ($loyalty_id == 0) {
			throw new ParseFatalException("increment in db not working");
		}

		$insert_row["loyalty_id"] = $loyalty_id;
		return self::_makeStructFromRow($insert_row);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $user_id, int $loyalty_id, array $set):void {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Domain_Usercard_Loyalty::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i  AND `loyalty_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $loyalty_id, 1);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id, int $loyalty_id):Struct_Domain_Usercard_Loyalty {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `loyalty_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $loyalty_id, 1);
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}
		$row["data"] = fromJson($row["data"]);

		return self::_makeStructFromRow($row);
	}

	/**
	 * метод для получения последних записей
	 *
	 * @return Struct_Domain_Usercard_Loyalty[]
	 */
	public static function getLastLoyaltyList(int $user_id, int $limit):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_user_id_is_deleted`)
		$query        = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `is_deleted` = ?i ORDER BY `loyalty_id` DESC LIMIT ?i";
		$loyalty_list = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, 0, $limit);

		$output = [];
		foreach ($loyalty_list as $row) {

			$row["data"] = fromJson($row["data"]);
			$output[]    = self::_makeStructFromRow($row);
		}
		return $output;
	}

	/**
	 * метод для получения записей после loyalty_id
	 *
	 * @return Struct_Domain_Usercard_Loyalty[]
	 */
	public static function getLoyaltyListAfterId(int $user_id, int $last_loyalty_id, int $limit):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (FORCE INDEX=`get_by_user_id_is_deleted`)
		$query        = "SELECT * FROM `?p` FORCE INDEX(`get_by_user_id_is_deleted`) WHERE `user_id` = ?i AND `loyalty_id` < ?i AND `is_deleted` = ?i ORDER BY `loyalty_id` DESC LIMIT ?i";
		$loyalty_list = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $last_loyalty_id, 0, $limit);

		$output = [];
		foreach ($loyalty_list as $row) {

			$row["data"] = fromJson($row["data"]);
			$output[]    = self::_makeStructFromRow($row);
		}
		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * создаем сткруктуру из строки бд
	 */
	protected static function _makeStructFromRow(array $row):Struct_Domain_Usercard_Loyalty {

		return new Struct_Domain_Usercard_Loyalty(
			$row["loyalty_id"],
			$row["user_id"],
			$row["creator_user_id"],
			$row["is_deleted"],
			$row["created_at"],
			$row["updated_at"],
			$row["comment_text"],
			Type_User_Card_Loyalty::actualData($row["data"])
		);
	}

	/**
	 * функция возвращает название таблицы
	 */
	protected static function _getTableName():string {

		return self::_TABLE_KEY;
	}
}