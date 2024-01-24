<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для работы с таблицей pivot_history_logs_{Y}.send_history
 */
class Gateway_Db_PivotHistoryLogs_SendHistory extends Gateway_Db_PivotHistoryLogs_Main {

	protected const _TABLE_KEY = "send_history";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Создать запись
	 *
	 * @throws \queryException
	 */
	public static function insert(
		string           $sms_id,
		int              $is_success,
		int              $task_created_at_ms,
		int              $send_to_provider_at_ms,
		int              $sms_sent_at_ms,
		int              $created_at,
		string           $provider_id,
		int              $provider_response_code,
		int|string|array $provider_response,
		array            $extra_alias
	):int {

		$db_key = self::_getDbKey(self::getShardIdByTime(time()));

		return ShardingGateway::database($db_key)->insert(self::_TABLE_KEY, [
			"sms_id"                 => $sms_id,
			"is_success"             => $is_success,
			"task_created_at_ms"     => $task_created_at_ms,
			"send_to_provider_at_ms" => $send_to_provider_at_ms,
			"sms_sent_at_ms"         => $sms_sent_at_ms,
			"created_at"             => $created_at,
			"provider_id"            => $provider_id,
			"provider_response_code" => $provider_response_code,
			"provider_response"      => $provider_response,
			"extra_alias"            => $extra_alias,
		]);
	}

	/**
	 * Обновляем запись
	 *
	 */
	public static function update(int $row_id, array $set):int {

		$db_key = self::_getDbKey(self::getShardIdByTime(time()));

		$query = "UPDATE `?p` SET ?u WHERE `row_id` = ?s LIMIT ?i";
		return ShardingGateway::database($db_key)->update($query, self::_TABLE_KEY, $set, $row_id, 1);
	}

	/**
	 * Получить историю по провайдеру с офсетоп
	 *
	 * @return Struct_PivotHistoryLogs_SendHistory[]
	 */
	public static function getByProviderAndOffset(string $provider_id, int $last_row_id, int $count):array {

		$db_key = self::_getDbKey(self::getShardIdByTime(time()));

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `row_id` > ?i AND `provider_id` = ?s LIMIT ?i";
		$list  = ShardingGateway::database($db_key)->getAll($query, self::_TABLE_KEY, $last_row_id, $provider_id, $count);

		// бежимся по полученным результатам и собираем объекты
		$output = [];
		foreach ($list as $row) {
			$output[] = self::_rowToStruct($row);
		}

		return $output;
	}

	/**
	 * Получить историю отправки по sms_id
	 *
	 * @return Struct_PivotHistoryLogs_SendHistory[]
	 */
	public static function getBySmsId(string $sms_id):array {

		$db_key = self::_getDbKey(self::getShardIdByTime(time()));

		// запрос проверен на EXPLAIN (INDEX=`sms_id`)
		$query = "SELECT * FROM `?p` WHERE `sms_id` = ?s LIMIT ?i";
		$list  = ShardingGateway::database($db_key)->getAll($query, self::_TABLE_KEY, $sms_id, 50);

		// бежимся по полученным результатам и собираем объекты
		$output = [];
		foreach ($list as $row) {
			$output[] = self::_rowToStruct($row);
		}

		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * конвертим запись в структуру
	 *
	 * @return Struct_PivotHistoryLogs_SendHistory
	 */
	protected static function _rowToStruct(array $row):Struct_PivotHistoryLogs_SendHistory {

		// проделываем такой трюк, потому что не все провайдеры отвечают в json формате
		$temp              = fromJson($row["provider_response"]);
		$provider_response = $temp == [] ? $row["provider_response"] : $temp;

		return new Struct_PivotHistoryLogs_SendHistory(
			$row["row_id"],
			$row["sms_id"],
			$row["is_success"],
			$row["task_created_at_ms"],
			$row["send_to_provider_at_ms"],
			$row["sms_sent_at_ms"],
			$row["created_at"],
			$row["provider_id"],
			$row["provider_response_code"],
			$provider_response,
			fromJson($row["extra_alias"])
		);
	}
}