<?php

namespace Compass\Thread;

/**
 * класс-интерфейс для таблицы cloud_user_thread.dynamic
 */
class Gateway_Db_CompanyThread_UserInbox extends Gateway_Db_CompanyThread_Main {

	protected const _TABLE_KEY = "user_inbox";

	// метод для создания записи
	public static function insert(int $user_id):array {

		$table_key = self::_getTableKey();

		$insert = [
			"user_id"              => $user_id,
			"thread_unread_count"  => 0,
			"message_unread_count" => 0,
			"created_at"           => time(),
			"updated_at"           => 0,
		];
		ShardingGateway::database(self::_getDbKey())->insert($table_key, $insert);

		return $insert;
	}

	// метод для создания записи
	public static function insertList(array $user_id_list):void {

		$table_key = self::_getTableKey();

		$insert_list = [];
		foreach ($user_id_list as $user_id) {

			$insert_list[] = [
				"user_id"              => $user_id,
				"thread_unread_count"  => 0,
				"message_unread_count" => 0,
				"created_at"           => time(),
				"updated_at"           => 0,
			];
		}
		ShardingGateway::database(self::_getDbKey())->insertArray($table_key, $insert_list);
	}

	// получение записи
	public static function getOne(int $user_id):array {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, $table_key, $user_id, 1);
	}

	/**
	 * метод для получения и блокировки записи пользователя
	 */
	public static function getForUpdate(int $user_id):array {

		$table_key = self::_getTableKey();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i FOR UPDATE";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, $table_key, $user_id, 1);
	}

	// обновление записи
	public static function set(int $user_id, array $set):void {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, $table_key, $set, $user_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
