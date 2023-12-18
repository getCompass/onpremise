<?php

namespace Compass\Pivot;

/**
 * класс для работы с таблицей pivot_attribution . user_app_registration_log
 * @package Compass\Pivot
 */
class Gateway_Db_PivotAttribution_UserAppRegistrationLog extends Gateway_Db_PivotAttribution_Main {

	protected const _TABLE_KEY = "user_app_registration_log";

	/**
	 * Создаем запись
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotAttribution_UserAppRegistration $user_app_registration):void {

		$insert = [
			"user_id"             => $user_app_registration->user_id,
			"ip_address"          => $user_app_registration->ip_address,
			"platform"            => $user_app_registration->platform,
			"platform_os"         => $user_app_registration->platform_os,
			"timezone_utc_offset" => $user_app_registration->timezone_utc_offset,
			"screen_avail_width"  => $user_app_registration->screen_avail_width,
			"screen_avail_height" => $user_app_registration->screen_avail_height,
			"registered_at"       => $user_app_registration->registered_at,
			"created_at"          => $user_app_registration->created_at,
		];

		try {
			ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert, false);
		} catch (\PDOException $e) {

			// если это дупликат
			if ($e->getCode() == 23000) {
				throw new cs_RowDuplication();
			}

			throw $e;
		}
	}

	/**
	 * Получаем запись по PK
	 *
	 * @return Struct_Db_PivotAttribution_UserAppRegistration
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function get(int $user_id):Struct_Db_PivotAttribution_UserAppRegistration {

		// запрос проверен на explain (PRIMARY_KEY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1);

		return Struct_Db_PivotAttribution_UserAppRegistration::rowToStruct($row);
	}

	/**
	 * Удаляем старые записи
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function deleteOlder(int $older_than_timestamp):void {

		// запрос проверен на EXPLAIN (INDEX=registered_at)
		$query = "DELETE FROM `?p` WHERE `registered_at` < ?i LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, $older_than_timestamp, 1000);
	}

}