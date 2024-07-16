<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей jitsi_data . user_active_conference_rel
 * @package Compass\Jitsi
 */
class Gateway_Db_JitsiData_UserActiveConferenceRel extends Gateway_Db_JitsiData_Main {

	protected const _TABLE_NAME = "user_active_conference_rel";

	/**
	 * создаем новую запись или обновляем существующую
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function insertOrUpdate(Struct_Db_JitsiData_UserActiveConference $user_active_conference):void {

		$insert_array = [
			"user_id"              => $user_active_conference->user_id,
			"active_conference_id" => $user_active_conference->active_conference_id,
			"created_at"           => $user_active_conference->created_at,
			"updated_at"           => 0,
		];
		$update_array = [
			"user_id"              => $user_active_conference->user_id,
			"active_conference_id" => $user_active_conference->active_conference_id,
			"updated_at"           => time(),
		];
		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_NAME, $insert_array, $update_array);
	}

	/**
	 * создаем запись
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_JitsiData_UserActiveConference $user_active_conference):void {

		$insert_array = [
			"user_id"              => $user_active_conference->user_id,
			"active_conference_id" => $user_active_conference->active_conference_id,
			"created_at"           => $user_active_conference->created_at,
			"updated_at"           => $user_active_conference->updated_at,
		];
		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array, false);
	}

	/**
	 * получаем запись из базы по PK
	 *
	 * @return Struct_Db_JitsiData_UserActiveConference
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(int $user_id):Struct_Db_JitsiData_UserActiveConference {

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $user_id, 1);

		if (!isset($row["user_id"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_JitsiData_UserActiveConference::rowToStruct($row);
	}

	/**
	 * получаем несколько записей из базы по PK
	 *
	 * @return array|Struct_Db_JitsiData_UserActiveConference[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getList(array $user_id_list):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY) 28.05.2024 Федореев М.
		$query  = "SELECT * FROM `?p` WHERE `user_id` IN (?a) LIMIT ?i";
		$result = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_NAME, $user_id_list, count($user_id_list));

		return array_map(fn(array $row) => Struct_Db_JitsiData_UserActiveConference::rowToStruct($row), $result);
	}

	/**
	 * получаем все записи по значению active_conference_id
	 *
	 * @param string $active_conference_id
	 *
	 * @return Struct_Db_JitsiData_UserActiveConference[]
	 * @throws ParseFatalException
	 */
	public static function getByActiveConferenceId(string $active_conference_id):array {

		// EXPLAIN INDEX active_conference_id
		$query  = "SELECT * FROM `?p` WHERE `active_conference_id` = ?s LIMIT ?i";
		$result = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_NAME, $active_conference_id, 10000);

		return array_map(fn(array $row) => Struct_Db_JitsiData_UserActiveConference::rowToStruct($row), $result);
	}

	/**
	 * обновляем запись в базе по PK
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $user_id, array $set):int {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $user_id, 1);
	}

	/**
	 * обновляем все записи по значению active_conference_id
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function setByActiveConferenceId(string $active_conference_id, array $set):int {

		// EXPLAIN INDEX active_conference_id
		$query = "UPDATE `?p` SET ?u WHERE `active_conference_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $active_conference_id, 10000);
	}
}