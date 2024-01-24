<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для таблицы pivot_user_{n}m.premium_prolongation_history_{m}
 */
class Gateway_Db_PivotUser_PremiumProlongationHistory extends Gateway_Db_PivotUser_Main {

	/** @var string имя таблицы */
	protected const _TABLE_KEY = "premium_prolongation_history";

	/**
	 * Добавляет одну запись в историю продления премиум-статуса.
	 */
	public static function insert(int $user_id, int $action, int $duration, int $active_till, int $doer_user_id, string $payment_id, array $extra):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$insert = [
			"user_id"      => $user_id,
			"action"       => $action,
			"created_at"   => time(),
			"duration"     => $duration,
			"active_till"  => $active_till,
			"doer_user_id" => $doer_user_id,
			"payment_id"   => $payment_id,
			"extra"        => $extra,
		];

		ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * Возвращает одну запись премиум-статуса для пользователя.
	 */
	public static function getByUserId(int $user_id, int $limit, int $offset):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// EXPLAIN INDEX get_by_user_id
		$query  = "SELECT * FROM `?p` WHERE `user_id` = ?i ORDER BY `created_at` DESC LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $limit, $offset);

		return static::_toStructList($result);
	}

	# region protected

	/**
	 * Возвращает шард таблицы для пользователя.
	 */
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}

	/**
	 * Конвертирует массив записей в массив структур.
	 * @return Struct_Db_PivotUser_PremiumProlongationHistory[]
	 */
	protected static function _toStructList(array $row_list):array {

		return array_map(static fn(array $row) => static::_toStruct($row), $row_list);
	}

	/**
	 * Конвертирует запись в структуру.
	 */
	protected static function _toStruct(array $row):Struct_Db_PivotUser_PremiumProlongationHistory {

		return new Struct_Db_PivotUser_PremiumProlongationHistory(...$row);
	}

	# endregion protected
}