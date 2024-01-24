<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.session_history_list
 */
class Gateway_Db_CompanyData_SessionHistoryList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "session_history_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @return string|void
	 *
	 * @throws \queryException
	 * @mixed
	 */
	public static function insert(
		string $session_uniq,
		int    $user_id,
		string $user_company_session_token,
		int    $status,
		int    $created_at,
		int    $login_at,
		int    $logout_at,
		string $ip_address,
		string $user_agent,
		array  $extra
	):string {

		$insert = [
			"session_uniq"               => $session_uniq,
			"user_id"                    => $user_id,
			"user_company_session_token" => $user_company_session_token,
			"status"                     => $status,
			"created_at"                 => $created_at,
			"login_at"                   => $login_at,
			"logout_at"                  => $logout_at,
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

			if (!property_exists(Struct_Db_CompanyData_SessionHistory::class, $field)) {
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
	public static function getOne(string $session_uniq):Struct_Db_CompanyData_SessionHistory {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE session_uniq=?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $session_uniq, 1);
		if (!isset($row["session_uniq"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_CompanyData_SessionHistory(
			$row["session_uniq"],
			$row["user_id"],
			$row["user_company_session_token"],
			$row["created_at"],
			$row["updated_at"],
			$row["login_at"],
			$row["logout_at"],
			$row["ip_address"],
			$row["user_agent"],
			fromJson($row["extra"]),
		);
	}
}