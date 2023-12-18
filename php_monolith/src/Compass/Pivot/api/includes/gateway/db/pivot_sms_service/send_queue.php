<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для работы с таблицей pivot_sms_service.send_queue
 */
class Gateway_Db_PivotSmsService_SendQueue extends Gateway_Db_PivotSmsService_Main {

	protected const _TABLE_KEY = "send_queue";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Создать запись
	 *
	 * @throws \queryException
	 */
	public static function insert(
		string $phone_number,
		string $text,
		string $sms_id,
		int    $stage,
		int    $need_work,
		int    $expires_at,
		string $provider_id,
		array  $extra
	):void {

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, [
			"sms_id"        => $sms_id,
			"stage"         => $stage,
			"need_work"     => $need_work,
			"error_count"   => 0,
			"created_at_ms" => timeMs(),
			"updated_at"    => 0,
			"expires_at"    => $expires_at,
			"phone_number"  => $phone_number,
			"text"          => $text,
			"provider_id"   => $provider_id,
			"extra"         => $extra,
		]);
	}

	/**
	 * Обновляем запись
	 *
	 */
	public static function update(string $sms_id, array $set):int {

		$query = "UPDATE `?p` SET ?u WHERE `sms_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $sms_id, 1);
	}

	/**
	 * Получаем запись
	 *
	 */
	public static function getOne(string $sms_id):null|Struct_PivotSmsService_SendQueue {

		$query = "SELECT * FROM `?p` WHERE `sms_id` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $sms_id, 1);

		if (!isset($row["sms_id"])) {
			return null;
		}

		return self::_convertRowToStruct($row);
	}

	/**
	 * получаем последнюю запись из очереди по номеру телефона
	 *
	 * @return Struct_PivotSmsService_SendQueue
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \parseException
	 */
	public static function getLastByPhoneNumber(string $phone_number):Struct_PivotSmsService_SendQueue {

		// доступно только на тестовых серверах в сервисных целях!
		assertTestServer();

		// нет проверки explain, потому что используется только на тестовых серверах в сервисных целях!
		$query = "SELECT * FROM `?p` WHERE `phone_number` = ?s ORDER BY `created_at_ms` DESC LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $phone_number, 1);

		if (!isset($row["sms_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("row not found");
		}

		return self::_convertRowToStruct($row);
	}

	/**
	 * Получение количества записей
	 *
	 * @return int
	 */
	public static function getTotalCount():int {

		// запрос проверен на EXPLAIN (INDEX=cron_sms_dispatcher)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param int $need_work
	 *
	 * @return int
	 */
	public static function getExpiredCount(int $need_work):int {

		// запрос проверен на EXPLAIN (INDEX=cron_sms_dispatcher)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `need_work` < ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $need_work, 1);
		return $row["count"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	protected static function _convertRowToStruct(array $row):Struct_PivotSmsService_SendQueue {

		return new Struct_PivotSmsService_SendQueue(
			$row["sms_id"],
			$row["stage"],
			$row["need_work"],
			$row["error_count"],
			$row["created_at_ms"],
			$row["updated_at"],
			$row["expires_at"],
			$row["phone_number"],
			$row["text"],
			$row["provider_id"],
			fromJson($row["extra"]),
		);
	}
}