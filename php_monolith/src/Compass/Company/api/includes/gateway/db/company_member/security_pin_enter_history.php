<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для таблицы company_member.security_pin_enter_history
 */
class Gateway_Db_CompanyMember_SecurityPinEnterHistory extends Gateway_Db_CompanyMember_Main {

	protected const _TABLE_KEY = "security_pin_enter_history";

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
		int    $user_id,
		int    $status,
		int    $enter_pin_hash_version,
		string $enter_pin_hash,
		string $user_company_session_token
	):int {

		$insert = [
			"user_id"                    => $user_id,
			"status"                     => $status,
			"created_at"                 => time(),
			"enter_pin_hash_version"     => $enter_pin_hash_version,
			"enter_pin_hash"             => $enter_pin_hash,
			"user_company_session_token" => $user_company_session_token,
		];

		// осуществляем запрос
		return ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert);
	}
}