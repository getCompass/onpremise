<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * Класс для работы с таблицей jitsi_data . permanent_conference_list
 * @package Compass\Jitsi
 */
class Gateway_Db_JitsiData_PermanentConferenceList extends Gateway_Db_JitsiData_Main {

	protected const _TABLE_NAME = "permanent_conference_list";

	/**
	 * Создаем запись
	 *
	 * @throws ParseFatalException
	 * @throws \queryException
	 * @throws RowDuplicationException
	 */
	public static function insert(Struct_Db_JitsiData_PermanentConference $conference):void {

		$insert_array = [
			"conference_id"              => $conference->conference_id,
			"space_id"                   => $conference->space_id,
			"is_deleted"                 => (int) $conference->is_deleted,
			"creator_user_id"            => $conference->creator_user_id,
			"conference_url_custom_name" => $conference->conference_url_custom_name,
			"created_at"                 => $conference->created_at,
			"updated_at"                 => $conference->updated_at,
		];

		try {
			ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array, false);
		} catch (\PDOException $e) {

			// если такая запись уже есть
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
	public static function getOne(string $conference_id):Struct_Db_JitsiData_PermanentConference {

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `conference_id` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $conference_id, 1);

		if (!isset($row["conference_id"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_JitsiData_PermanentConference::rowToStruct($row);
	}

	/**
	 * Получаем число активных постоянных конференций пользователя
	 *
	 * @throws ParseFatalException
	 */
	public static function getActiveCount(int $creator_user_id, $space_id):int {

		// EXPLAIN get_by_user
		$query = "SELECT COUNT(*) AS count FROM `?p` WHERE `space_id` = ?i AND `is_deleted` = ?i AND `creator_user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $space_id, 0, $creator_user_id, 1);

		return $row["count"];
	}

	/**
	 * Получаем конференцию по ссылке
	 *
	 * @throws RowNotFoundException
	 * @throws ParseFatalException
	 */
	public static function getByLinkForUser(int $creator_user_id, int $space_id, string $conference_url_custom_name):Struct_Db_JitsiData_PermanentConference {

		// EXPLAIN get_by_unique
		$query = "SELECT * FROM `?p` WHERE `space_id` = ?i AND `is_deleted` = ?i AND `creator_user_id` = ?i AND `conference_url_custom_name` =?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $space_id, 0, $creator_user_id, $conference_url_custom_name, 1);

		if (!isset($row["conference_id"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_JitsiData_PermanentConference::rowToStruct($row);
	}

	/**
	 * Получаем список активных постоянных конференций пользователя
	 *
	 * @throws ParseFatalException
	 */
	public static function getListByUser(int $creator_user_id, int $space_id):array {

		// EXPLAIN get_by_user
		$query  = "SELECT * FROM `?p` WHERE `space_id` = ?i AND `is_deleted` = ?i AND `creator_user_id` = ?i LIMIT ?i";
		$result = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_NAME, $space_id, 0, $creator_user_id, 100);

		return array_map(fn(array $row) => Struct_Db_JitsiData_PermanentConference::rowToStruct($row), $result);
	}

	/**
	 * Обновляем запись в базе по PK
	 *
	 * @param string $conference_id
	 * @param array  $set
	 *
	 * @return int
	 * @throws ParseFatalException
	 */
	public static function set(string $conference_id, array $set):int {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `conference_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $conference_id, 1);
	}

	/**
	 * Обновляем запись в базе
	 *
	 * @throws ParseFatalException
	 */
	public static function setBySpace(int $creator_user_id, int $space_id, array $set):int {

		// EXPLAIN get_by_user
		$query = "UPDATE `?p` SET ?u WHERE `space_id` = ?i AND `is_deleted` = ?i AND `creator_user_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $space_id, 0, $creator_user_id, 100);
	}
}