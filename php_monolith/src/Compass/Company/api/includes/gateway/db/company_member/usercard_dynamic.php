<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_member.usercard_dynamic
 */
class Gateway_Db_CompanyMember_UsercardDynamic extends Gateway_Db_CompanyMember_Main {

	protected const _TABLE_KEY = "usercard_dynamic";

	/**
	 * Добавляем запись dynamic-данных карточки пользователя в базу
	 *
	 * @param int   $user_id
	 * @param array $data
	 *
	 * @return Struct_Domain_Usercard_Dynamic
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function add(int $user_id, array $data):Struct_Domain_Usercard_Dynamic {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		$insert_row = [
			"user_id"    => $user_id,
			"created_at" => time(),
			"updated_at" => 0,
			"data"       => $data,
		];

		ShardingGateway::database($shard_key)->insert($table_name, $insert_row);

		return self::_rowToObject($insert_row);
	}

	/**
	 * метод для получения записи на обновление
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function getForUpdate(int $user_id):Struct_Domain_Usercard_Dynamic {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$row["data"] = fromJson($row["data"]);
		return self::_rowToObject($row);
	}

	/**
	 * Обновляем запись dynamic карточки пользователя
	 */
	public static function set(int $user_id, array $set):void {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, 1);
	}

	/**
	 * Получаем запись dynamic карточки пользователя
	 *
	 * @throws \parseException|\cs_RowIsEmpty
	 */
	public static function get(int $user_id):Struct_Domain_Usercard_Dynamic {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		// переводим в объект
		$row["data"] = fromJson($row["data"]);
		return self::_rowToObject($row);
	}

	/**
	 * Получение списка ID пользователей по массиву ID
	 */
	public static function getUserIdList(array $user_id_list):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT `user_id` FROM `?p` WHERE `user_id` IN (?a) LIMIT ?i";
		$rows  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id_list, count($user_id_list));

		return $rows;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * создаем сткруктуру из строки бд
	 *
	 * @param array $row
	 *
	 * @return Struct_Domain_Usercard_Dynamic
	 * @throws ParseFatalException
	 */
	protected static function _rowToObject(array $row):Struct_Domain_Usercard_Dynamic {

		foreach ($row as $field => $_) {
			if (!property_exists(Struct_Domain_Usercard_Dynamic::class, $field)) {

				throw new ParseFatalException("send unknown field = '{$field}'");
			}
		}

		return new Struct_Domain_Usercard_Dynamic(
			$row["user_id"],
			$row["created_at"],
			$row["updated_at"],
			Type_User_Card_DynamicData::actualData($row["data"]),
		);
	}

	/**
	 * функция возвращает название таблицы
	 */
	protected static function _getTableName():string {

		return self::_TABLE_KEY;
	}
}