<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для таблицы pivot_user_{n}m.premium_status_{m}
 */
class Gateway_Db_PivotUser_PremiumStatus extends Gateway_Db_PivotUser_Main {

	/** @var string имя таблицы */
	protected const _TABLE_KEY = "premium_status";

	/**
	 * Выполняет создание новой записи премиум-статуса для пользователя.
	 *
	 * Данные всегда вставляются одинаковые, меняется только user_id
	 * и дата создания. После вставки данные нужно сразу обновить.
	 * Такая реализация позволит не потерять флажок безлимитного
	 * доступа по случайности.
	 */
	public static function insert(int $user_id, array $extra):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$insert = [
			"user_id"                      => $user_id,
			"need_block_if_inactive"       => 1,
			"free_active_till"             => 0,
			"active_till"                  => 0,
			"created_at"                   => time(),
			"updated_at"                   => time(),
			"last_prolongation_at"         => 0,
			"last_prolongation_duration"   => 0,
			"last_prolongation_user_id"    => 0,
			"last_prolongation_payment_id" => "",
			"extra"                        => toJson($extra),
		];

		ShardingGateway::database($shard_key)->insert($table_name, $insert);
		return static::_fromRow($insert);
	}

	/**
	 * Метод обновления данных статуса премиума для пользователя.
	 */
	public static function update(int $user_id, array $update):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// EXPLAIN INDEX PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $update, $user_id, 1);
	}

	/**
	 * Возвращает одну запись премиум-статуса для пользователя.
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// EXPLAIN INDEX PRIMARY
		$query  = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($result["user_id"])) {
			throw new \cs_RowIsEmpty("premium not found");
		}

		return static::_fromRow($result);
	}

	/**
	 * Возвращает одну запись премиум-статуса для пользователя.
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $user_id):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// EXPLAIN INDEX PRIMARY
		$query  = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i FOR UPDATE";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($result["user_id"])) {
			throw new \cs_RowIsEmpty("premium not found");
		}

		return static::_fromRow($result);
	}

	/**
	 * Возвращает все существующие записи для пользователей.
	 *
	 * @param int[] $user_id_list
	 *
	 * @return array
	 */
	public static function getList(array $user_id_list):array {

		$aggregated_user_id_list = [];
		$output                  = [];

		// распихиваем пользователей по базам и таблицам, в которых хранятся их данные
		foreach ($user_id_list as $user_id) {
			$aggregated_user_id_list[self::_getDbKey($user_id)][self::_getTableKey($user_id)][] = $user_id;
		}

		// пробегаем по всем шардам
		foreach ($aggregated_user_id_list as $database => $table_list_in_shard) {

			// и по всем таблицам
			foreach ($table_list_in_shard as $table => $user_id_list_in_table) {

				// EXPLAIN INDEX PRIMARY
				$query  = "SELECT * FROM `?p` WHERE `user_id` in (?a) LIMIT ?i";
				$result = ShardingGateway::database($database)->getAll($query, $table, $user_id_list_in_table, count($user_id_list_in_table));

				$output += static::_fromRowList($result);
			}
		}

		return $output;
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
	 * @return array[]
	 */
	protected static function _fromRowList(array $row_list):array {

		return array_map(static fn(array $row) => static::_fromRow($row), $row_list);
	}

	/**
	 * Конвертирует запись в структуру.
	 */
	protected static function _fromRow(array $row):array {

		$row["extra"] = fromJson($row["extra"]);
		return $row;
	}

	# endregion protected
}