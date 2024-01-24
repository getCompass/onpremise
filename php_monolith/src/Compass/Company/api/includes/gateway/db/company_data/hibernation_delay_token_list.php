<?php

namespace Compass\Company;

use JetBrains\PhpStorm\Pure;

/**
 * Интерфейс для работы с таблицы company_data.hibernation_delay_token_list
 */
class Gateway_Db_CompanyData_HibernationDelayTokenList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "hibernation_delay_token_list";

	/**
	 * Получение 1 токена после определенного времени
	 */
	public static function getOneAfterTime(int $after_time):Struct_Db_CompanyData_HibernationDelayTokenList {

		$query = "SELECT * FROM `?p` WHERE `hibernation_delayed_till` > ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $after_time, 1);

		if (!isset($row["token_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Получение 1 токена
	 */
	public static function getAny():Struct_Db_CompanyData_HibernationDelayTokenList {

		assertTestServer();

		$query = "SELECT * FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, 1);

		if (!isset($row["token_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Устанавливаем для всех токенов время
	 */
	public static function setForAll(int $hibernation_delayed_till):void {

		assertTestServer();

		$query = "UPDATE `?p` SET ?u WHERE TRUE LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, ["hibernation_delayed_till" => $hibernation_delayed_till], 10000000);
	}

	/**
	 * Вставить или обновить запись
	 *
	 * @param string $token_uniq
	 * @param int    $user_id
	 *
	 * @param int    $hibernation_delayed_till
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function insertOrUpdate(string $token_uniq, int $user_id, int $hibernation_delayed_till):void {

		$insert_row = [
			"token_uniq"               => $token_uniq,
			"user_id"                  => $user_id,
			"hibernation_delayed_till" => $hibernation_delayed_till,
			"created_at"               => time(),
			"updated_at"               => time(),
		];

		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, $insert_row);
	}

	/**
	 * получаем последнюю запись активности
	 *
	 * @return Struct_Db_CompanyData_HibernationDelayTokenList
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getLastActivity():Struct_Db_CompanyData_HibernationDelayTokenList {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` FORCE INDEX (`hibernation_delayed_till`) WHERE TRUE ORDER BY `hibernation_delayed_till` DESC LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, 1);

		if (!isset($row["token_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 */
	#[Pure] protected static function _rowToObject(array $row):Struct_Db_CompanyData_HibernationDelayTokenList {

		return new Struct_Db_CompanyData_HibernationDelayTokenList(
			$row["token_uniq"],
			$row["user_id"],
			$row["hibernation_delayed_till"],
			$row["created_at"],
			$row["updated_at"],
		);
	}
}
