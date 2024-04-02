<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_phone.phone_uniq_list_{0-f}
 */
class Gateway_Db_PivotPhone_PhoneUniqList extends Gateway_Db_PivotPhone_Main {

	protected const _TABLE_KEY = "phone_uniq_list";

	/**
	 * Добавляет новую запись в базу.
	 */
	public static function insertOrUpdate(string $phone_number_hash, int $user_id, bool $has_sso_account, int $created_at, int $updated_at, int $binding_count, int $last_binding_at, int $last_unbinding_at, array $previous_user_list):Struct_Db_PivotPhone_PhoneUniq {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($phone_number_hash);

		$insert = [
			"phone_number_hash"  => $phone_number_hash,
			"user_id"            => $user_id,
			"has_sso_account"    => intval($has_sso_account),
			"binding_count"      => $binding_count,
			"last_binding_at"    => $last_binding_at,
			"last_unbinding_at"  => $last_unbinding_at,
			"created_at"         => $created_at,
			"updated_at"         => $updated_at,
			"previous_user_list" => toJson($previous_user_list),
		];

		// осуществляем запрос
		ShardingGateway::database($shard_key)->insertOrUpdate($table_name, $insert);
		return static::_fromRow($insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(string $phone_number_hash, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($phone_number_hash);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotPhone_PhoneUniq::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// EXPLAIN: INDEX PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `phone_number_hash` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $phone_number_hash, 1);
	}

	/**
	 * Метод для обновления записи по PK и user_id
	 *
	 * @throws \parseException
	 */
	public static function setByPhoneAndUserId(string $phone_number_hash, int $user_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotPhone_PhoneUniq::class, $field)) {
				throw new ParseFatalException("attempt to set unknown field");
			}
		}

		// EXPLAIN: INDEX PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `phone_number_hash` = ?s AND `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey($phone_number_hash), $set, $phone_number_hash, $user_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOneWithUserId(string $phone_number_hash):Struct_Db_PivotPhone_PhoneUniq {

		$db_key     = self::_getDbKey();
		$table_name = self::_getTableKey($phone_number_hash);

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE `phone_number_hash`=?s AND `user_id`!=?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_name, $phone_number_hash, 0, 1);

		if (!isset($row["phone_number_hash"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("phone not found");
		}

		return static::_fromRow($row);
	}

	/**
	 * Метод для чтения одного номера.
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(string $phone_number_hash):Struct_Db_PivotPhone_PhoneUniq {

		$db_key     = self::_getDbKey();
		$table_name = self::_getTableKey($phone_number_hash);

		// EXPLAIN: INDEX PRIMARY
		$query = "SELECT * FROM `?p` WHERE `phone_number_hash` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_name, $phone_number_hash, 1);

		if (!isset($row["phone_number_hash"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("phone not found");
		}

		return static::_fromRow($row);
	}

	/**
	 * Метод для чтения с блокировкой.
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getForUpdate(string $phone_number_hash):Struct_Db_PivotPhone_PhoneUniq {

		$db_key     = self::_getDbKey();
		$table_name = self::_getTableKey($phone_number_hash);

		// EXPLAIN: INDEX PRIMARY
		$query = "SELECT * FROM `?p` WHERE `phone_number_hash` = ?s LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_name, $phone_number_hash, 1);

		if (!isset($row["phone_number_hash"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("phone not found");
		}

		return static::_fromRow($row);
	}

	/**
	 * метод для удаления записи
	 */
	public static function delete(string $phone_number_hash):void {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey($phone_number_hash);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `phone_number_hash` = ?s LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, $table_name, $phone_number_hash, 1);
	}

	# region protected

	/**
	 * Возвращает шард таблицы по хэшу номера.
	 */
	protected static function _getTableKey(string $phone_hash):string {

		return self::_TABLE_KEY . "_" . substr($phone_hash, -1);
	}

	/**
	 * Конвертирует запись в структуру.
	 */
	protected static function _fromRow(array $row):Struct_Db_PivotPhone_PhoneUniq {

		$row["previous_user_list"] = fromJson($row["previous_user_list"]);
		return new Struct_Db_PivotPhone_PhoneUniq(...$row);
	}

	# endregion protected
}