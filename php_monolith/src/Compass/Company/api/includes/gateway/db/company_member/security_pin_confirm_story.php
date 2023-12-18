<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_member.security_pin_confirm_story
 */
class Gateway_Db_CompanyMember_SecurityPinConfirmStory extends Gateway_Db_CompanyMember_Main {

	protected const _TABLE_KEY = "security_pin_confirm_story";

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
		string $confirm_key,
		int    $user_id,
		int    $status,
		int    $created_at,
		int    $updated_at,
		int    $expires_at
	):string {

		$insert = [
			"confirm_key" => $confirm_key,
			"user_id"     => $user_id,
			"status"      => $status,
			"created_at"  => $created_at,
			"updated_at"  => $updated_at,
			"expires_at"  => $expires_at,
		];

		// осуществляем запрос
		return ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $user_id, string $confirm_key, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyMember_SecurityPinConfirmStory::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `confirm_key`=?s AND `user_id`=?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $confirm_key, $user_id, 1);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $confirm_key, int $user_id):Struct_Db_CompanyMember_SecurityPinConfirmStory {

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `confirm_key`=?s AND `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $confirm_key, $user_id, 1);
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return new Struct_Db_CompanyMember_SecurityPinConfirmStory(
			$confirm_key,
			$user_id,
			$row["status"],
			$row["created_at"],
			$row["updated_at"],
			$row["expires_at"]
		);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getLastByUser(int $user_id):Struct_Db_CompanyMember_SecurityPinConfirmStory {

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i ORDER BY `created_at` DESC LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1);
		if (!isset($row["confirm_key"])) {
			throw new \cs_RowIsEmpty();
		}

		return new Struct_Db_CompanyMember_SecurityPinConfirmStory(
			$row["confirm_key"],
			$row["user_id"],
			$row["status"],
			$row["created_at"],
			$row["updated_at"],
			$row["expires_at"]
		);
	}
}