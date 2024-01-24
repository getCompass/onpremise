<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_auth_{Y}.auth_phone_list_{m}
 */
class Gateway_Db_PivotAuth_AuthPhoneList extends Gateway_Db_PivotAuth_Main {

	protected const _TABLE_KEY = "auth_phone_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotAuth_AuthPhone $auth_phone):string {

		$shard_key  = self::_getDbKey(Type_Pack_Auth::getShardId($auth_phone->auth_map));
		$table_name = self::_getTableKey(Type_Pack_Auth::getTableId($auth_phone->auth_map));

		$insert = [
			"auth_map"       => $auth_phone->auth_map,
			"is_success"     => $auth_phone->is_success,
			"resend_count"   => $auth_phone->resend_count,
			"error_count"    => $auth_phone->error_count,
			"created_at"     => $auth_phone->created_at,
			"updated_at"     => $auth_phone->updated_at,
			"next_resend_at" => $auth_phone->next_resend_at,
			"sms_id"         => $auth_phone->sms_id,
			"sms_code_hash"  => $auth_phone->sms_code_hash,
			"phone_number"   => $auth_phone->phone_number,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(string $auth_map, array $set):int {

		$shard_key  = self::_getDbKey(Type_Pack_Auth::getShardId($auth_map));
		$table_name = self::_getTableKey(Type_Pack_Auth::getTableId($auth_map));

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotAuth_AuthPhone::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `auth_map` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $auth_map, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $auth_map):Struct_Db_PivotAuth_AuthPhone {

		$shard_key  = self::_getDbKey(Type_Pack_Auth::getShardId($auth_map));
		$table_name = self::_getTableKey(Type_Pack_Auth::getTableId($auth_map));

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `auth_map`=?s LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $auth_map, 1);
		if (!isset($row["auth_map"])) {
			throw new \cs_RowIsEmpty();
		}
		return self::_rowToStruct($row);
	}

	/**
	 * получаем историю за временной отрезок
	 *
	 * @return Struct_Db_PivotAuth_AuthPhone[]
	 */
	public static function getListByPeriod(int $date_start, int $date_end, int $limit):array {

		$shard_list = [
			self::_getDbKey(Type_Pack_Auth::getShardIdByTime($date_start)) . "-" . self::_getTableKey(Type_Pack_Auth::getTableIdByTime($date_start)),
			self::_getDbKey(Type_Pack_Auth::getShardIdByTime($date_end)) . "-" . self::_getTableKey(Type_Pack_Auth::getTableIdByTime($date_end)),
		];
		$shard_list = array_unique($shard_list);

		// проходимся по каждому шарду и получаем историю
		$output = [];
		foreach ($shard_list as $shard) {

			[$db_key, $table_key] = explode("-", $shard);

			// индекса нет, используется только в сервисных целях в редких случаях
			$query = "SELECT * FROM `?p` WHERE `created_at` >= ?i AND `created_at` <= ?i LIMIT ?i";
			$list  = ShardingGateway::database($db_key)->getAll($query, $table_key, $date_start, $date_end, $limit);

			// бежимся по полученным результатам и собираем объекты
			foreach ($list as $row) {
				$output[] = self::_rowToStruct($row);
			}
		}

		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotAuth_AuthPhone
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotAuth_AuthPhone {

		return new Struct_Db_PivotAuth_AuthPhone(
			$row["auth_map"],
			$row["is_success"],
			$row["resend_count"],
			$row["error_count"],
			$row["created_at"],
			$row["updated_at"],
			$row["next_resend_at"],
			$row["sms_id"],
			$row["sms_code_hash"],
			$row["phone_number"],
		);
	}

	// получает таблицу
	protected static function _getTableKey(int $table_id):string {

		return self::_TABLE_KEY . "_" . $table_id;
	}
}