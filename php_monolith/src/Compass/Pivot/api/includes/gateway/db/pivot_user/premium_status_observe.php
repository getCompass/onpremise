<?php

namespace Compass\Pivot;

/**
 * Класс для работы с таблицей pivot_user.premium_status_observe
 */
class Gateway_Db_PivotUser_PremiumStatusObserve extends Gateway_Db_PivotUser_Main {

	/** @var string имя таблицы */
	protected const _TABLE_KEY = "premium_status_observe";

	/**
	 * Добавляет одну запись.
	 */
	public static function insert(int $user_id, int $observe_at, int $action):void {

		$insert = [
			"user_id"    => $user_id,
			"observe_at" => $observe_at,
			"action"     => $action,
			"created_at" => time(),
		];

		$shard_key = self::_getDbKey($user_id);
		ShardingGateway::database($shard_key)->insert(static::_TABLE_KEY, $insert);
	}

	/**
	 * Возвращает записи, подходящие для observe действия.
	 * @return Struct_Db_PivotUser_PremiumStatusObserve[]
	 */
	public static function getForObserve(int $shard_user_id, int $observe_at, int $limit, int $offset):array {

		$shard_key = self::_getDbKey($shard_user_id);

		// explain: index get_for_observe
		$query  = "SELECT * FROM `?p` WHERE `observe_at` <= ?i LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database($shard_key)->getAll($query, static::_TABLE_KEY, $observe_at, $limit, $offset);

		return array_map(static fn(array $el) => static::_fromRow($el), $result);
	}

	/**
	 * Удаляет записи после обработки.
	 */
	public static function deleteAfterObserve(int $shard_user_id, array $to_delete_id_list):void {

		$shard_key = self::_getDbKey($shard_user_id);

		// explain: index PRIMARY
		$query = "DELETE FROM `?p` WHERE `id` IN (?a) LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, static::_TABLE_KEY, $to_delete_id_list, count($to_delete_id_list));
	}

	/**
	 * Получение количества записей
	 *
	 * @param int $shard_user_id
	 *
	 * @return int
	 */
	public static function getTotalCount(int $shard_user_id):int {

		// запрос проверен на EXPLAIN (INDEX=get_for_observe)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey($shard_user_id))->getOne($query, self::_TABLE_KEY, 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param int $shard_user_id
	 * @param int $need_work
	 *
	 * @return int
	 */
	public static function getExpiredCount(int $shard_user_id, int $need_work):int {

		// запрос проверен на EXPLAIN (INDEX=get_for_observe)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `observe_at` < ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey($shard_user_id))->getOne($query, self::_TABLE_KEY, $need_work, 1);
		return $row["count"];
	}

	# region protected

	/**
	 * Конвертирует запись в структуру.
	 */
	protected static function _fromRow(array $row):Struct_Db_PivotUser_PremiumStatusObserve {

		return new Struct_Db_PivotUser_PremiumStatusObserve(...$row);
	}

	# endregion protected
}