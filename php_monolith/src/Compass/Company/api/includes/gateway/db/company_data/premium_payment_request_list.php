<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.premium_payment_request_list
 */
class Gateway_Db_CompanyData_PremiumPaymentRequestList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "premium_payment_request_list";

	/**
	 * Метод для вставки/обновления записи
	 *
	 * @param Struct_Db_CompanyData_PremiumPaymentRequest $premium_payment_request
	 *
	 * @return string
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_CompanyData_PremiumPaymentRequest $premium_payment_request):string {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		$insert = [
			"requested_by_user_id" => $premium_payment_request->requested_by_user_id,
			"is_payed"             => $premium_payment_request->is_payed,
			"requested_at"         => $premium_payment_request->requested_at,
			"created_at"           => $premium_payment_request->created_at,
			"updated_at"           => $premium_payment_request->updated_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_key, $insert);
	}

	/**
	 * Получить запись
	 *
	 * @param int $requested_by_user_id
	 *
	 * @return Struct_Db_CompanyData_PremiumPaymentRequest
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 */
	public static function getOne(int $requested_by_user_id):Struct_Db_CompanyData_PremiumPaymentRequest {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `requested_by_user_id` = ?i LIMIT ?i";

		// осуществляем запрос
		$row = ShardingGateway::database($shard_key)->getOne($query, $table_key, $requested_by_user_id, 1);

		if (!isset($row["requested_by_user_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("premium payment request not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * Получить несколько записей
	 *
	 * @param array $requested_by_user_id_list
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function getList(array $requested_by_user_id_list):array {

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `requested_by_user_id` IN (?a) LIMIT ?i";

		// осуществляем запрос
		$list = ShardingGateway::database($shard_key)->getAll($query, $table_key, $requested_by_user_id_list, count($requested_by_user_id_list));

		$struct_list = [];
		foreach ($list as $row) {
			$struct_list[] = self::_formatRow($row);
		}

		return $struct_list;
	}

	/**
	 * Изменить запись
	 *
	 * @param int   $requested_by_user_id
	 * @param array $set
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function set(int $requested_by_user_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_PremiumPaymentRequest::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		$table_key = self::_getTableKey();
		$shard_key = self::_getDbKey();

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `requested_by_user_id` = ?i LIMIT ?i";

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->update($query, $table_key, $set, $requested_by_user_id, 1);
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
	 * @return Struct_Db_CompanyData_PremiumPaymentRequest
	 */
	protected static function _formatRow(array $row):Struct_Db_CompanyData_PremiumPaymentRequest {

		return new Struct_Db_CompanyData_PremiumPaymentRequest(
			(int) $row["requested_by_user_id"],
			(bool) $row["is_payed"],
			(int) $row["requested_at"],
			(int) $row["created_at"],
			(int) $row["updated_at"]
		);
	}
}