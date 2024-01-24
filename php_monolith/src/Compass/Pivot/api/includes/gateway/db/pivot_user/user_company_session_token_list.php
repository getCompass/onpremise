<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.user_company_session_token_list_{1}
 */
class Gateway_Db_PivotUser_UserCompanySessionTokenList extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "user_company_session_token_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @param string $user_company_session_token
	 * @param int    $user_id
	 * @param string $session_uniq
	 * @param int    $status
	 * @param int    $company_id
	 * @param int    $created_at
	 *
	 * @return string
	 * @throws \queryException
	 * @mixed
	 */
	public static function insert(
		string $user_company_session_token,
		int    $user_id,
		string $session_uniq,
		int    $status,
		int    $company_id,
		int    $created_at
	):string {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$insert = [
			"user_company_session_token" => $user_company_session_token,
			"user_id"                    => $user_id,
			"session_uniq"               => $session_uniq,
			"status"                     => $status,
			"company_id"                 => $company_id,
			"created_at"                 => $created_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для получения токена
	 *
	 * @param int    $user_id
	 * @param string $user_company_session_token
	 *
	 * @return Struct_Db_PivotUser_UserCompanySessionToken
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id, string $user_company_session_token):Struct_Db_PivotUser_UserCompanySessionToken {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "SELECT * FROM `?p` WHERE user_company_session_token =?s AND `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_company_session_token, $user_id, 1);
		if (!isset($row["user_company_session_token"])) {
			throw new \cs_RowIsEmpty();
		}

		return new Struct_Db_PivotUser_UserCompanySessionToken(
			$row["user_company_session_token"],
			$row["user_id"],
			$row["session_uniq"],
			$row["status"],
			$row["company_id"],
			$row["created_at"],
		);
	}

	/**
	 * метод для получения записей по сессии
	 *
	 * @param int    $user_id
	 * @param string $session_uniq
	 *
	 * @return Struct_Db_PivotUser_UserCompanySessionToken[]
	 */
	public static function getBySessionUniq(int $user_id, string $session_uniq):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		// проверил запрос в EXPLAIN: key=session_uniq
		$query = "SELECT * FROM `?p` WHERE user_id=?i AND `session_uniq`=?s LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $session_uniq, 10000);

		$output = [];
		foreach ($list as $row) {

			$output[] = new Struct_Db_PivotUser_UserCompanySessionToken(
				$row["user_company_session_token"],
				$row["user_id"],
				$row["session_uniq"],
				$row["status"],
				$row["company_id"],
				$row["created_at"],
			);
		}
		return $output;
	}

	/**
	 * метод для получения записей по списку сессий
	 *
	 * @param int   $user_id
	 * @param array $session_uniq_list
	 *
	 * @return Struct_Db_PivotUser_UserCompanySessionToken[]
	 */
	public static function getBySessionUniqList(int $user_id, array $session_uniq_list):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		// проверил запрос в EXPLAIN: key=session_uniq
		$query = "SELECT * FROM `?p` WHERE user_id=?i AND `session_uniq` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $session_uniq_list, 10000);

		$output = [];
		foreach ($list as $row) {

			$output[] = new Struct_Db_PivotUser_UserCompanySessionToken(
				$row["user_company_session_token"],
				$row["user_id"],
				$row["session_uniq"],
				$row["status"],
				$row["company_id"],
				$row["created_at"],
			);
		}
		return $output;
	}

	/**
	 * метод для обновления по сессии
	 *
	 * @param int    $user_id
	 * @param string $session_uniq
	 * @param array  $set
	 *
	 * @return int
	 */
	public static function setBySessionUniq(int $user_id, string $session_uniq, array $set):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		// проверил запрос в EXPLAIN: key=PRIMARY, session_uniq проигнорирован
		$query = "UPDATE `?p` SET ?u WHERE user_id=?i AND `session_uniq`=?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $session_uniq, 10000);
	}

	/**
	 * метод для обновления по списку сессий
	 *
	 * @param int   $user_id
	 * @param array $session_uniq_list
	 * @param array $set
	 *
	 * @return int
	 */
	public static function setBySessionUniqList(int $user_id, array $session_uniq_list, array $set):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		// проверил запрос в EXPLAIN: key=PRIMARY, session_uniq проигнорирован
		$query = "UPDATE `?p` SET ?u WHERE user_id=?i AND `session_uniq` IN (?a) LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $session_uniq_list, 10000);
	}

	/**
	 * метод для обновления записи
	 *
	 * @param int    $user_id
	 * @param string $user_company_session_token
	 * @param array  $set
	 *
	 * @return int
	 * @throws \parseException
	 */
	public static function set(int $user_id, string $user_company_session_token, array $set):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotUser_UserCompanySessionToken::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE user_company_session_token = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_company_session_token, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}
}