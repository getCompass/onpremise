<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Интерфейс для работы с БД подозрительных ip адресов
 */
class Gateway_Db_PivotSystem_AntispamSuspectIp extends Gateway_Db_PivotSystem_Main {

	protected const _TABLE_KEY = "antispam_suspect_ip";

	/**
	 * Добавление записи
	 *
	 * @throws \queryException
	 * @throws ParseFatalException
	 */
	public static function insert(Struct_Db_PivotSystem_AntispamSuspectIp $antispam_suspect_ip):void {

		$insert = [
			"ip_address"   => $antispam_suspect_ip->ip_address,
			"phone_code"   => $antispam_suspect_ip->phone_code,
			"created_at"   => $antispam_suspect_ip->created_at,
			"expires_at"   => $antispam_suspect_ip->expires_at,
			"delayed_till" => $antispam_suspect_ip->delayed_till,
		];

		// осуществляем запрос
		ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * Получение записи
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws ParseFatalException
	 */
	public static function get(string $ip_address):Struct_Db_PivotSystem_AntispamSuspectIp {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `ip_address` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $ip_address, 1);

		if (!isset($row["ip_address"])) {
			throw new \cs_RowIsEmpty();
		}
		return self::_convertRowToStruct($row);
	}

	/**
	 * Обновляем запись
	 *
	 * @throws ParseFatalException
	 */
	public static function set(string $ip_address, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotSystem_AntispamSuspectIp::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `ip_address` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $ip_address, 1);
	}

	/**
	 * Получаем число недавно добавленных записей
	 */
	public static function getRecentCount($phone_code):int {

		// запрос проверен на EXPLAIN (INDEX=created_at)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `phone_code` = ?s AND `created_at` > ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $phone_code, time() - Domain_Antispam_Entity_SuspectIp::RECENT_IP_CREATED_AT, 1);

		return $row["count"];
	}

	/**
	 * Удаляем ip
	 */
	public static function delete(string $ip_address):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `ip_address` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_TABLE_KEY, $ip_address, 1);
	}

	/**
	 * Очистка всех блокировок
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function clearAll():void {

		// только для тестовых
		ServerProvider::assertTest();

		// чистим все блокировки
		ShardingGateway::database(self::_DB_KEY)->delete("DELETE FROM `?p` WHERE ?i = ?i LIMIT ?i", self::_TABLE_KEY, 1, 1, 1000000);
	}

	/**
	 * Конвертировать запись в структуру
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotSystem_AntispamSuspectIp
	 */
	protected static function _convertRowToStruct(array $row):Struct_Db_PivotSystem_AntispamSuspectIp {

		return new Struct_Db_PivotSystem_AntispamSuspectIp(
			$row["ip_address"],
			$row["phone_code"],
			$row["created_at"],
			$row["expires_at"],
			$row["delayed_till"],
		);
	}
}