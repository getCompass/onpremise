<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.notification_company_push_token_{1}
 */
class Gateway_Db_PivotUser_NotificationCompanyPushToken extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "notification_company_push_token";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotUser_NotificationCompanyPushToken $token):string {

		$table_key = self::_getTableKey($token->user_id);
		$shard_key = self::_getDbKey($token->user_id);

		$insert = [
			"user_id"    => $token->user_id,
			"company_id" => $token->company_id,
			"token_hash" => $token->token_hash,
			"created_at" => $token->created_at,
			"updated_at" => $token->updated_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_key, $insert);
	}

	/**
	 * метод для вставки записи
	 *
	 */
	public static function insertOrUpdate(Struct_Db_PivotUser_NotificationCompanyPushToken $token):string {

		$table_key = self::_getTableKey($token->user_id);
		$shard_key = self::_getDbKey($token->user_id);

		$insert = [
			"user_id"    => $token->user_id,
			"company_id" => $token->company_id,
			"token_hash" => $token->token_hash,
			"created_at" => $token->created_at,
			"updated_at" => $token->updated_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insertOrUpdate($table_key, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 */
	public static function set(int $user_id, array $set):void {

		$table_key = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX='user_id')
		$query = "UPDATE `?p` SET ?u WHERE `user_id`=?i LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->update($query, $table_key, $set, $user_id, 1);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id):Struct_Db_PivotUser_NotificationCompanyPushToken {

		$table_key = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX='user_id')
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, $table_key, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_PivotUser_NotificationCompanyPushToken(
			$row["token_hash"],
			$row["user_id"],
			$row["company_id"],
			$row["created_at"],
			$row["updated_at"],
		);
	}

	/**
	 * метод для получения записи и установки на нее блокировки
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $user_id):Struct_Db_PivotUser_NotificationCompanyPushToken {

		$table_key = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX='user_id')
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, $table_key, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_PivotUser_NotificationCompanyPushToken(
			$row["token_hash"],
			$row["user_id"],
			$row["company_id"],
			$row["created_at"],
			$row["updated_at"],
		);
	}

	/**
	 * метод для удаления записей по пользователю и компании
	 *
	 */
	public static function deleteByUserAndCompany(int $user_id, int $company_id, int $limit = 100):int {

		$table_key = self::_getTableKey($user_id);
		$shard_key = self::_getDbKey($user_id);

		// запрос проверен на EXPLAIN (INDEX='user_id')
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `company_id` = ?i LIMIT ?i";

		return ShardingGateway::database($shard_key)->delete($query, $table_key, $user_id, $company_id, $limit);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу

	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}
}