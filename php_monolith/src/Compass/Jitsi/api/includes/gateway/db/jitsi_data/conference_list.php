<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей jitsi_data . conference_list
 * @package Compass\Jitsi
 */
class Gateway_Db_JitsiData_ConferenceList extends Gateway_Db_JitsiData_Main {

	protected const _TABLE_NAME = "conference_list";

	/**
	 * Создаем запись
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 * @throws RowDuplicationException
	 */
	public static function insert(Struct_Db_JitsiData_Conference $conference):void {

		$insert_array = [
			"conference_id"              => $conference->conference_id,
			"space_id"                   => $conference->space_id,
			"status"                     => $conference->status,
			"is_private"                 => intval($conference->is_private),
			"is_lobby"                   => intval($conference->is_lobby),
			"creator_user_id"            => $conference->creator_user_id,
			"conference_url_custom_name" => $conference->conference_url_custom_name,
			"description"                => $conference->description,
			"password"                   => $conference->password,
			"jitsi_instance_domain"      => $conference->jitsi_instance_domain,
			"created_at"                 => $conference->created_at,
			"updated_at"                 => $conference->updated_at,
			"data"                       => $conference->data,
		];

		try {
			ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array, false);
		} catch (\PDOException $e) {

			// если это дупликат
			if ($e->getCode() == 23000) {
				throw new RowDuplicationException("row duplication");
			}

			throw $e;
		}
	}

	/**
	 * Получаем запись из базы по PK
	 *
	 * @throws RowNotFoundException
	 * @throws ParseFatalException
	 */
	public static function getOne(string $conference_id):Struct_Db_JitsiData_Conference {

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `conference_id` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $conference_id, 1);

		if (!isset($row["conference_id"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_JitsiData_Conference::rowToStruct($row);
	}

	/**
	 * Получаем массив записей
	 *
	 * @throws ParseFatalException
	 */
	public static function getList(array $conference_id_list):array {

		// EXPLAIN PRIMARY KEY
		$query  = "SELECT * FROM `?p` WHERE `conference_id` IN (?a) LIMIT ?i";
		$result = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_NAME, $conference_id_list, count($conference_id_list));

		return array_map(fn(array $row) => Struct_Db_JitsiData_Conference::rowToStruct($row), $result);
	}

	/**
	 * Обновляем запись в базе по PK
	 *
	 * @throws ParseFatalException
	 */
	public static function set(string $conference_id, array $set):int {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `conference_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $conference_id, 1);
	}

	/**
	 * Возвращаем кол-во записей, созданных за период
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function getCountByPeriod(int $from_date, int $to_date):int {

		// SERVICE QUERY, NO EXPLAIN
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `created_at` >= ?i AND `created_at` <= ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $from_date, $to_date, 1)["count"] ?? 0;
	}
}