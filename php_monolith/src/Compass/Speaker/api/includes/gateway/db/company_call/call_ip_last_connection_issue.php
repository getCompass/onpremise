<?php

namespace Compass\Speaker;

/**
 * класс-интерфейс для работы с таблицей company_call . call_ip_last_connection_issue
 */
class Gateway_Db_CompanyCall_CallIpLastConnectionIssue extends Gateway_Db_CompanyCall_Main {

	protected const _TABLE_KEY = "call_ip_last_connection_issue";

	/**
	 * получаем запись
	 *
	 * @return array
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function getOne(string $ip_address):array {

		// конвертируем ip адресс в integer
		$ip_address_int = ip2long($ip_address);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `ip_address_int` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $ip_address_int, 1);

		if (!isset($row["ip_address_int"])) {
			throw new \cs_RowIsEmpty();
		}

		return $row;
	}

	/**
	 * сохраняем факт о случившейся проблеме
	 *
	 * @throws \parseException
	 */
	public static function insertOrUpdate(string $ip_address, int $issue_happened_at):void {

		// конвертируем ip адресс в integer
		$ip_address_int = ip2long($ip_address);

		// массив для вставки
		$insert = [
			"ip_address_int"   => $ip_address_int,
			"last_happened_at" => $issue_happened_at,
			"created_at"       => time(),
		];

		// массив для обновления (на тот случай, если запись уже существует)
		$update = [
			"last_happened_at" => $issue_happened_at,
		];
		ShardingGateway::database(self::_getDbKey())->insertOrUpdate(self::_TABLE_KEY, $insert, $update);
	}

}
