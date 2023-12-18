<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.premium_payment_request_menu
 */
class Gateway_Db_CompanyData_PremiumPaymentRequestMenu extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "premium_payment_request_menu";

	/**
	 * Метод для вставки/обновления записи
	 *
	 * @param Struct_Db_CompanyData_PremiumPaymentRequestMenu $premium_payment_request_menu
	 *
	 * @return string
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_CompanyData_PremiumPaymentRequestMenu $premium_payment_request_menu):string {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		$insert = [
			"user_id"              => (int) $premium_payment_request_menu->user_id,
			"requested_by_user_id" => (int) $premium_payment_request_menu->requested_by_user_id,
			"is_unread"            => (int) $premium_payment_request_menu->is_unread,
			"created_at"           => (int) $premium_payment_request_menu->created_at,
			"updated_at"           => (int) $premium_payment_request_menu->updated_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_key, $insert);
	}

	/**
	 * Метод для вставки нескольких записей
	 *
	 * @param Struct_Db_CompanyData_PremiumPaymentRequestMenu[] $premium_payment_request_menu_list
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function insertList(array $premium_payment_request_menu_list):string {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		$insert_list = array_map(fn(Struct_Db_CompanyData_PremiumPaymentRequestMenu $v) => (array) $v, $premium_payment_request_menu_list);

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insertArray($table_key, $insert_list);
	}

	/**
	 * Получить запись
	 *
	 * @param int $user_id
	 * @param int $requested_by_user_id
	 *
	 * @return Struct_Db_CompanyData_PremiumPaymentRequestMenu
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $user_id, int $requested_by_user_id):Struct_Db_CompanyData_PremiumPaymentRequestMenu {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `requested_by_user_id` = ?i LIMIT ?i";

		// осуществляем запрос
		$row = ShardingGateway::database($shard_key)->getOne($query, $table_key, $user_id, $requested_by_user_id, 1);

		if (!isset($row["user_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("premium payment request not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить несколько записей
	 *
	 * @param array $user_id_list
	 * @param int   $requested_by_user_id
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getList(array $user_id_list, int $requested_by_user_id):array {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		$query = "SELECT * FROM `?p` WHERE `user_id` IN (?a) AND `requested_by_user_id` = ?i LIMIT ?i";

		// осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$result = ShardingGateway::database($shard_key)->getAll($query, $table_key, $user_id_list, $requested_by_user_id, count($user_id_list));

		return array_map(fn(array $row) => self::_formatRow($row), $result);
	}

	/**
	 * Получить непрочитанные запросы
	 *
	 * @param int $user_id
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function getUnreadCount(int $user_id):int {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// запрос проверен на EXPLAIN(INDEX=user_id.is_unread)
		$query = "SELECT COUNT(*) count FROM `?p` WHERE `user_id` = ?i AND `is_unread` = ?i LIMIT ?i";

		// осуществляем запрос
		$row = ShardingGateway::database($shard_key)->getOne($query, $table_key, $user_id, 1, 1);

		return $row["count"];
	}

	/**
	 * получаем количество непрочитанных для пользователей
	 *
	 * @throws ParseFatalException
	 */
	public static function getUnreadList(array $user_id_list):array {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// запрос проверен на EXPLAIN(INDEX=user_id.is_unread)
		$query = "SELECT `user_id`, count(*) AS unread_count FROM `?p` WHERE `user_id` IN (?a) AND `is_unread` = ?i GROUP BY `user_id` LIMIT ?i";

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->getAll($query, $table_key, $user_id_list, 1, count($user_id_list));
	}

	/**
	 * Изменить запись
	 *
	 * @param int   $user_id
	 * @param array $set
	 * @param int   $limit
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function setRead(int $user_id, array $set, int $limit):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_PremiumPaymentRequestMenu::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// запрос проверен на EXPLAIN(INDEX=user_id.is_unread)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `is_unread` = ?i LIMIT ?i";

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->update($query, $table_key, $set, $user_id, 1, $limit);
	}

	/**
	 * Метод для обновления нескольких записей
	 *
	 * @param array $user_id_list
	 * @param int   $requested_by_user_id
	 * @param array $set
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function setList(array $user_id_list, int $requested_by_user_id, array $set):string {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		$query = "UPDATE `?p` SET ?u WHERE `user_id` IN (?a) AND `requested_by_user_id` = ?i LIMIT ?i";

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->update($query, $table_key, $set, $user_id_list, $requested_by_user_id, count($user_id_list));
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить ключ таблицы
	 *
	 * @return string
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}

	/**
	 * Форматируем запись
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_CompanyData_PremiumPaymentRequestMenu
	 */
	protected static function _formatRow(array $row):Struct_Db_CompanyData_PremiumPaymentRequestMenu {

		return new Struct_Db_CompanyData_PremiumPaymentRequestMenu(
			(int) $row["user_id"],
			(int) $row["requested_by_user_id"],
			(bool) $row["is_unread"],
			(int) $row["created_at"],
			(int) $row["updated_at"]
		);
	}
}