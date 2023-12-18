<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для таблицы company_data.member_notification_list
 */
class Gateway_Db_CompanyData_MemberNotificationList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "member_notification_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_CompanyData_MemberNotification $user_notification):string {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		$insert = [
			"user_id"       => $user_notification->user_id,
			"snoozed_until" => $user_notification->snoozed_until,
			"created_at"    => $user_notification->created_at,
			"updated_at"    => $user_notification->updated_at,
			"token"         => $user_notification->token,
			"device_list"   => $user_notification->device_list,
			"extra"         => $user_notification->extra,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_key, $insert);
	}

	/**
	 * метод для вставки/обновления записи
	 */
	public static function insertOrUpdate(Struct_Db_CompanyData_MemberNotification $user_notification):string {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		$insert = [
			"user_id"       => $user_notification->user_id,
			"snoozed_until" => $user_notification->snoozed_until,
			"created_at"    => $user_notification->created_at,
			"updated_at"    => $user_notification->updated_at,
			"token"         => $user_notification->token,
			"device_list"   => $user_notification->device_list,
			"extra"         => $user_notification->extra,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insertOrUpdate($table_key, $insert);
	}

	/**
	 * метод для обновления записи
	 */
	public static function set(int $user_id, array $set):void {

		$table_key = self::_getTableKey();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX='user_id')
		$query = "UPDATE `?p` SET ?u WHERE `user_id`=?i LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->update($query, $table_key, $set, $user_id, 1);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws cs_UserNotificationNotFound
	 */
	public static function getOne(int $user_id):Struct_Db_CompanyData_MemberNotification {

		$table_key = self::_getTableKey();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX='user_id')
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, $table_key, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new cs_UserNotificationNotFound();
		}
		return self::_formatRow($row);
	}

	/**
	 * метод для получения записи и установки на нее блокировки
	 *
	 * @throws cs_UserNotificationNotFound
	 */
	public static function getForUpdate(int $user_id):Struct_Db_CompanyData_MemberNotification {

		$table_key = self::_getTableKey();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX='user_id')
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, $table_key, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new cs_UserNotificationNotFound();
		}
		return self::_formatRow($row);
	}

	/**
	 * метод для удаления записей по пользователю
	 */
	public static function deleteByUser(int $user_id, int $limit = 1):int {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX='user_id')
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i LIMIT ?i";

		return ShardingGateway::database(self::_DB_KEY)->delete($query, $table_key, $user_id, $limit);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Отформатировать строку из запроса
	 */
	protected static function _formatRow(array $row):Struct_Db_CompanyData_MemberNotification {

		return new Struct_Db_CompanyData_MemberNotification(
			$row["user_id"],
			$row["snoozed_until"],
			$row["created_at"],
			$row["updated_at"],
			$row["token"],
			fromJson($row["device_list"]),
			fromJson($row["extra"])
		);
	}

	/**
	 * получает таблицу
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}