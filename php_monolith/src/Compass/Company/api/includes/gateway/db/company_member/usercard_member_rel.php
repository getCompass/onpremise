<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для таблицы company_member.usercard_member_rel
 */
class Gateway_Db_CompanyMember_UsercardMemberRel extends Gateway_Db_CompanyMember_Main {

	protected const _TABLE_KEY = "usercard_member_rel";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(int $user_id, int $role, int $recipient_user_id):Struct_Domain_Usercard_MemberRel {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		$insert_row = [
			"user_id"           => $user_id,
			"role"              => $role,
			"recipient_user_id" => $recipient_user_id,
			"is_deleted"        => 0,
			"created_at"        => time(),
			"updated_at"        => 0,
		];

		// осуществляем запрос
		$row_id = ShardingGateway::database($shard_key)->insert($table_name, $insert_row);

		if ($row_id > 0) {
			$insert_row["row_id"] = $row_id;
		}

		return self::_makeStructFromRow($insert_row);
	}

	/**
	 * метод для добавления/обновления записи
	 *
	 * @throws \queryException
	 */
	public static function insertOrUpdate(int $user_id, int $role, int $recipient_user_id):void {

		// пробуем вставить запись
		$relationship_user_obj = self::insert($user_id, $role, $recipient_user_id);

		// если идентификатор записи больше нуля, то значит добавление выполнилось успешно
		if ($relationship_user_obj->row_id > 0) {
			return;
		}

		// обновляем существующую запись
		self::set($user_id, $role, $recipient_user_id);
	}

	/**
	 * метод для обновляем записи
	 */
	public static function set(int $user_id, int $role, int $recipient_user_id):void {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// обновляем
		$set = [
			"role"       => $role,
			"is_deleted" => 0,
			"updated_at" => time(),
		];

		// запрос проверен на EXPLAIN (UNIQUE=`user_id_and_recipient_user_id`)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `recipient_user_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $recipient_user_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $row_id):Struct_Domain_Usercard_MemberRel {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE row_id = ?i AND `is_deleted` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $row_id, 0, 1);
		if (!isset($row["row_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_makeStructFromRow($row);
	}

	/**
	 * метод для получения несколько записей по user_id и роли
	 *
	 * @return Struct_Domain_Usercard_MemberRel[]
	 */
	public static function getByUserIdAndRole(int $user_id, int $role):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_user_id_role_is_deleted`)
		$query = "SELECT * FROM `?p` FORCE INDEX(`get_by_user_id_role_is_deleted`)
			WHERE `user_id` = ?i AND `role` = ?i AND `is_deleted` = ?i ORDER BY `updated_at` ASC LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $role, 0, 1000);

		foreach ($list as $index => $row) {
			$list[$index] = self::_makeStructFromRow($row);
		}

		return $list;
	}

	/**
	 * помечаем запись удаленной
	 */
	public static function delete(int $user_id, int $recipient_user_id):void {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// обновляем
		$set = [
			"is_deleted" => 1,
			"updated_at" => time(),
		];

		// запрос проверен на EXPLAIN (UNIQUE=`user_id_and_recipient_user_id`)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `recipient_user_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $recipient_user_id, 1);
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * создаем сткруктуру из строки бд
	 */
	protected static function _makeStructFromRow(array $row):Struct_Domain_Usercard_MemberRel {

		return new Struct_Domain_Usercard_MemberRel(
			$row["row_id"] ?? 0,
			$row["user_id"],
			$row["role"],
			$row["recipient_user_id"],
			$row["is_deleted"],
			$row["created_at"],
			$row["updated_at"]
		);
	}

	/**
	 * функция возвращает название таблицы
	 */
	protected static function _getTableName():string {

		return self::_TABLE_KEY;
	}
}