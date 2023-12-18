<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для таблицы pivot_user_{n}m.used_premium_promo_product_{m}
 */
class Gateway_Db_PivotUser_UsedPremiumPromoProduct extends Gateway_Db_PivotUser_Main {

	/** @var string имя таблицы */
	protected const _TABLE_KEY = "used_premium_promo_product";

	/**
	 * Вставляет запись активации промо-товара пользователем.
	 */
	public static function insertList(int $user_id, array $label_list):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$insert = array_map(static fn(string $label) => [
			"user_id"    => $user_id,
			"label"      => $label,
			"created_at" => time(),
		], $label_list);

		ShardingGateway::database($shard_key)->insertArray($table_name, $insert);
	}

	/**
	 * Возвращает одну запись премиум-статуса для пользователя.
	 */
	public static function getUsedByUserId(int $user_id, array $label_list):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// EXPLAIN INDEX PRIMARY
		$query  = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `label` IN (?a) LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $label_list, count($label_list));

		return array_column($result, "label");
	}

	# region protected

	/**
	 * Возвращает шард таблицы для пользователя.
	 */
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}

	# endregion protected
}