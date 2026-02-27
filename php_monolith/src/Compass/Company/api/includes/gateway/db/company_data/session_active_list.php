<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.session_active_list
 */
class Gateway_Db_CompanyData_SessionActiveList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "session_active_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 * @mixed
	 */
	public static function insert(
		string $session_uniq,
		int    $user_id,
		string $user_company_session_token,
		int    $created_at,
		int    $updated_at,
		int    $login_at,
		string $ip_address,
		string $user_agent,
		array  $extra
	):string {

		$insert = [
			"session_uniq"               => $session_uniq,
			"user_id"                    => $user_id,
			"user_company_session_token" => $user_company_session_token,
			"created_at"                 => $created_at,
			"updated_at"                 => $updated_at,
			"login_at"                   => $login_at,
			"ip_address"                 => $ip_address,
			"user_agent"                 => $user_agent,
			"extra"                      => $extra,
		];

		// осуществляем запрос
		return ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(string $session_uniq, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_SessionActive::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE session_uniq = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $session_uniq, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $session_uniq):Struct_Db_CompanyData_SessionActive {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `session_uniq`=?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $session_uniq, 1);
		if (!isset($row["session_uniq"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_CompanyData_SessionActive(
			$row["session_uniq"],
			$row["user_id"],
			$row["user_company_session_token"],
			$row["created_at"],
			$row["updated_at"],
			$row["login_at"],
			$row["ip_address"],
			$row["user_agent"],
			fromJson($row["extra"]),
		);
	}

	/**
	 * метод для получения записи с блокировкой
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(string $session_uniq):Struct_Db_CompanyData_SessionActive {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `session_uniq`=?s LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $session_uniq, 1);

		if (!isset($row["session_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return new Struct_Db_CompanyData_SessionActive(
			$row["session_uniq"],
			$row["user_id"],
			$row["user_company_session_token"],
			$row["created_at"],
			$row["updated_at"],
			$row["login_at"],
			$row["ip_address"],
			$row["user_agent"],
			fromJson($row["extra"]),
		);
	}

	/**
	 * метод для удаление записи
	 */
	public static function delete(int $user_id, string $session_uniq):int {

		// формируем и осуществляем запрос
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `session_uniq` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, $user_id, $session_uniq, 1);
	}

	/**
	 * метод для удаления записей по пользователю
	 */
	public static function deleteByUser(int $user_id):int {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`user_id`)
		$query = "SELECT COUNT(session_uniq) count_session_uniq FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1);

		// запрос проверен на EXPLAIN (INDEX=`user_id`)
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i LIMIT ?i";

		return ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, $user_id, $row["count_session_uniq"]);
	}

	/**
	 * метод для получения списка активных сессий по пользователю
	 * @return Struct_Db_CompanyData_SessionActive[]
	 */
	public static function getByUser(int $user_id):array {

		$output_list = [];

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`user_id`)
		$query = "SELECT COUNT(session_uniq) count_session_uniq FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1);

		// запрос проверен на EXPLAIN (INDEX=`user_id`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";

		$query_list = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $user_id, $row["count_session_uniq"]);

		foreach ($query_list as $row) {
			$output_list[] = self::_rowToObject($row);
		}

		return $output_list;
	}

	/**
	 * метод для получения записи
	 */
	public static function deleteByUserCompanySessionToken(array $user_company_session_token_list):int {

		// формируем и осуществляем запрос
		$query = "DELETE FROM `?p` WHERE user_company_session_token IN (?a) LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, $user_company_session_token_list, count($user_company_session_token_list));
	}

	/**
	 * метод для очищения таблицы
	 */
	public static function truncate():void {

		$table_name = self::_TABLE_KEY;

		$query = "TRUNCATE TABLE `{$table_name}`";
		ShardingGateway::database(self::_DB_KEY)->execQuery($query);
	}

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Db_CompanyData_SessionActive {

		return new Struct_Db_CompanyData_SessionActive(
			$row["session_uniq"],
			$row["user_id"],
			$row["user_company_session_token"],
			$row["created_at"],
			$row["updated_at"],
			$row["login_at"],
			$row["ip_address"],
			$row["user_agent"],
			fromJson($row["extra"]),
		);
	}
}