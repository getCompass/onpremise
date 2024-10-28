<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Gateway\RowDuplicationException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с таблицей jitsi_data . conference_member_list
 * @package Compass\Jitsi
 */
class Gateway_Db_JitsiData_ConferenceMemberList extends Gateway_Db_JitsiData_Main {

	protected const _TABLE_NAME = "conference_member_list";

	/**
	 * получаем запись из базы по PK
	 *
	 * @return Struct_Db_JitsiData_ConferenceMember
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(string $conference_id, int $member_type, string $member_id):Struct_Db_JitsiData_ConferenceMember {

		// EXPLAIN PRIMARY KEY
		$query = "SELECT * FROM `?p` WHERE `conference_id` = ?s AND `member_type` = ?i AND `member_id` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_NAME, $conference_id, $member_type, $member_id, 1);

		if (!isset($row["conference_id"])) {
			throw new RowNotFoundException("row not found");
		}

		return Struct_Db_JitsiData_ConferenceMember::rowToStruct($row);
	}

	/**
	 * обновляем запись в базе по PK
	 *
	 * @return int
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $conference_id, int $member_type, string $member_id, array $set):int {

		// EXPLAIN PRIMARY KEY
		$query = "UPDATE `?p` SET ?u WHERE `conference_id` = ?s AND `member_type` = ?i AND `member_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_NAME, $set, $conference_id, $member_type, $member_id, 1);
	}

	/**
	 * создаем новую запись
	 *
	 * @throws RowDuplicationException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_JitsiData_ConferenceMember $conference_member):void {

		$insert_array = [
			"conference_id" => $conference_member->conference_id,
			"member_type"   => $conference_member->member_type->value,
			"member_id"     => $conference_member->member_id,
			"is_moderator"  => $conference_member->is_moderator,
			"status"        => $conference_member->status->value,
			"ip_address"    => $conference_member->ip_address,
			"user_agent"    => $conference_member->user_agent,
			"created_at"    => $conference_member->created_at,
			"updated_at"    => $conference_member->updated_at,
			"data"          => $conference_member->data,
		];
		try {
			ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_NAME, $insert_array, false);
		} catch (\PDOException $e) {

			// если это дупликат записи
			if ($e->getCode() == 23000) {
				throw new RowDuplicationException($e->getMessage());
			}

			throw $e;
		}
	}

	/**
	 * создаем новую запись или обновляем существующую
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function insertOrUpdate(Struct_Db_JitsiData_ConferenceMember $conference_member):void {

		$insert_array = [
			"conference_id" => $conference_member->conference_id,
			"member_type"   => $conference_member->member_type->value,
			"member_id"     => $conference_member->member_id,
			"is_moderator"  => $conference_member->is_moderator,
			"status"        => $conference_member->status->value,
			"ip_address"    => $conference_member->ip_address,
			"user_agent"    => $conference_member->user_agent,
			"created_at"    => $conference_member->created_at,
			"updated_at"    => $conference_member->updated_at,
			"data"          => $conference_member->data,
		];
		$update_array = [
			"is_moderator" => $conference_member->is_moderator,
			"status"       => $conference_member->status->value,
			"ip_address"   => $conference_member->ip_address,
			"user_agent"   => $conference_member->user_agent,
			"updated_at"   => time(),
			"data"         => $conference_member->data,
		];
		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_NAME, $insert_array, $update_array);
	}

	/**
	 * получаем список записей с участниками конкретной конференции
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getList(string $conference_id):array {

		// EXPLAIN INDEX conference_id.created_at
		$query = "SELECT * FROM `?p` WHERE `conference_id` = ?s ORDER BY `created_at` DESC LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_NAME, $conference_id, 10000);

		return array_map(static fn(array $row) => Struct_Db_JitsiData_ConferenceMember::rowToStruct($row), $list);
	}
}