<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_data.device_token_voip_list_{0-f}
 */
class Gateway_Db_PivotData_DeviceTokenVoipList extends Gateway_Db_PivotData_Main {

	protected const _TABLE_KEY = "device_token_voip_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotData_DeviceTokenVoipList $token):string {

		$table_key = self::_getTableKey($token->token_hash);
		$shard_key = self::_getDbKey();

		$insert = [
			"token_hash" => $token->token_hash,
			"user_id"    => $token->user_id,
			"created_at" => $token->created_at,
			"updated_at" => $token->updated_at,
			"device_id"  => $token->device_id,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_key, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 */
	public static function set(string $token_hash, array $set):void {

		$table_key = self::_getTableKey($token_hash);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX='token_hash')
		$query = "UPDATE `?p` SET ?u WHERE `token_hash`=?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->update($query, $table_key, $set, $token_hash, 1);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 */
	public static function getOne(string $token_hash):Struct_Db_PivotData_DeviceTokenVoipList {

		$table_key = self::_getTableKey($token_hash);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `token_hash`=?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, $table_key, $token_hash, 1);

		if (!isset($row["token_hash"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("cant find voip token");
		}

		return new Struct_Db_PivotData_DeviceTokenVoipList(
			$row["token_hash"],
			$row["user_id"],
			$row["created_at"],
			$row["updated_at"],
			$row["device_id"],
		);
	}

	/**
	 * метод для получения записи и установки на нее блокировки
	 *
	 * @param string $token_hash
	 *
	 * @return Struct_Db_PivotData_DeviceTokenVoipList
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getForUpdate(string $token_hash):Struct_Db_PivotData_DeviceTokenVoipList {

		$table_key = self::_getTableKey($token_hash);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `token_hash`=?s LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, $table_key, $token_hash, 1);

		if (!isset($row["token_hash"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("token not found");
		}
		return new Struct_Db_PivotData_DeviceTokenVoipList(
			$row["token_hash"],
			$row["user_id"],
			$row["created_at"],
			$row["updated_at"],
			$row["device_id"],
		);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получает таблицу
	 *
	 */
	protected static function _getTableKey(string $token_hash):string {

		return self::_TABLE_KEY . "_" . substr($token_hash, -1);
	}
}