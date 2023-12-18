<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_member.security_list
 */
class Gateway_Db_CompanyMember_SecurityList extends Gateway_Db_CompanyMember_Main {

	protected const _TABLE_KEY = "security_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для вставки записи
	 *
	 * @mixed
	 */
	public static function insertOrUpdate(
		int    $user_id,
		bool   $is_pin_required,
		int    $created_at,
		int    $updated_at,
		int    $last_enter_pin_at,
		int    $pin_hash_version,
		string $pin_hash
	):string {

		$insert = [
			"user_id"           => $user_id,
			"is_pin_required"   => $is_pin_required,
			"created_at"        => $created_at,
			"updated_at"        => $updated_at,
			"last_enter_pin_at" => $last_enter_pin_at,
			"pin_hash_version"  => $pin_hash_version,
			"pin_hash"          => $pin_hash,
		];
		return ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $user_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyMember_Security::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE user_id=?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $user_id, 1);
	}

	/**
	 * метод для обновления группы записей
	 *
	 * @throws \parseException
	 */
	public static function setList(array $user_id_list, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyMember_Security::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE user_id IN (?a) LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $user_id_list, count($user_id_list));
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id):Struct_Db_CompanyMember_Security {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE user_id=?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1);
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return self::_rowToObject($row);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $user_id):Struct_Db_CompanyMember_Security {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE user_id=?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1);
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}
		return self::_rowToObject($row);
	}

	/**
	 * метод для получения списка пользователей
	 */
	public static function getList(array $user_id_list):array {

		$output_list = [];

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query      = "SELECT * FROM `?p` WHERE user_id IN (?a) LIMIT ?i";
		$query_list = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $user_id_list, count($user_id_list));

		foreach ($query_list as $row) {
			$output_list[] = self::_rowToObject($row);
		}

		return $output_list;
	}

	/**
	 * метод для получения списка пользователей на основе флага обязательного пин-кода
	 */
	public static function getIsRequiredPinList(int $is_pin_required):array {

		$output_list = [];

		// получаем количество участников компании для лимита
		$member_count = Domain_User_Action_Config_GetMemberCount::do();

		// запрос проверен на EXPLAIN (INDEX=NULL)
		$query      = "SELECT * FROM `?p` WHERE is_pin_required = ?i LIMIT ?i";
		$query_list = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $is_pin_required, $member_count);

		foreach ($query_list as $row) {
			$output_list[] = self::_rowToObject($row);
		}

		return $output_list;
	}

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Db_CompanyMember_Security {

		return new Struct_Db_CompanyMember_Security(
			$row["user_id"],
			$row["is_pin_required"],
			$row["created_at"],
			$row["updated_at"],
			$row["last_enter_pin_at"],
			$row["pin_hash_version"],
			$row["pin_hash"],
		);
	}
}
