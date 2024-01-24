<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для работы с таблицей pivot_sms_service.observer_provider
 */
class Gateway_Db_PivotSmsService_ObserverProvider extends Gateway_Db_PivotSmsService_Main {

	protected const _TABLE_KEY = "observer_provider";

	/**
	 * Создаем запись
	 *
	 * @throws \queryException
	 */
	public static function insert(
		string $provider_id,
		int    $need_work,
		int    $created_at,
		array  $extra
	):void {

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, [
			"provider_id" => $provider_id,
			"need_work"   => $need_work,
			"created_at"  => $created_at,
			"extra"       => $extra,
		]);
	}

	/**
	 * Удаляем запись
	 *
	 */
	public static function delete(string $provider_id):int {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "DELETE FROM `?p` WHERE `provider_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, $provider_id, 1);
	}

	/**
	 * Обновляем запись
	 *
	 */
	public static function update(string $provider_id, array $set):int {

		$query = "UPDATE `?p` SET ?u WHERE `provider_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $provider_id, 1);
	}

	/**
	 * Получение количества записей
	 *
	 * @return int
	 */
	public static function getTotalCount():int {

		// запрос проверен на EXPLAIN (INDEX=need_work)
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

		// запрос проверен на EXPLAIN (INDEX=need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `need_work` < ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $need_work, 1);
		return $row["count"];
	}
}