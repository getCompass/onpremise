<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы invite_group_via_single
 */
class Gateway_Db_CompanyConversation_InviteGroupViaSingle extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "invite_group_via_single";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для создание записи с ивайтом в таблице
	public static function create(int $user_id, int $sender_user_id, array $group_meta_row, string $single_conversation_map, string $invite_map, int $created_at):array {

		$status = Type_Invite_Handler::STATUS_ACTIVE;

		if (Type_Conversation_Meta_Users::isMember($user_id, $group_meta_row["users"])) {
			$status = Type_Invite_Handler::STATUS_INACTIVE;
		}

		$insert = [
			"invite_map"              => $invite_map,
			"status"                  => $status,
			"inactive_reason"         => 0,
			"created_at"              => $created_at,
			"updated_at"              => $created_at,
			"user_id"                 => $user_id,
			"sender_user_id"          => $sender_user_id,
			"conversation_name"       => $group_meta_row["conversation_name"],
			"avatar_file_map"         => $group_meta_row["avatar_file_map"],
			"group_conversation_map"  => $group_meta_row["conversation_map"],
			"single_conversation_map" => $single_conversation_map,
		];

		self::insert($insert);
		return $insert;
	}

	// метод создает запись
	public static function insert(array $insert):void {

		try {
			static::_connect(self::_getDbKey())->insert(self::_getTable(), $insert, false);
		} catch (\PDOException $e) {

			// если это дубликат
			if ($e->getCode() == 23000) {
				throw new cs_InviteIsDuplicated();
			}

			throw $e;
		}
	}

	// метод для обновления записи
	public static function set(string $invite_map, int $status, array $set):int {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "UPDATE `?p` SET ?u WHERE invite_map = ?s AND `status` = ?i LIMIT ?i";
		return static::_connect(self::_getDbKey())->update($query, self::_getTable(), $set, $invite_map, $status, 1);
	}

	// метод для получения записи
	public static function getOne(string $invite_map):array {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "SELECT * FROM `?p` WHERE invite_map = ?s LIMIT ?i";
		return static::_connect(self::_getDbKey())->getOne($query, self::_getTable(), $invite_map, 1);
	}

	// получаем все записи
	public static function getAll(array $invite_map_list):array {

		$table_key = self::_getTable();

		// получаем инвайты
		$query = "SELECT * FROM `?p` WHERE invite_map IN (?a) LIMIT ?i";
		return static::_connect(self::_getDbKey())->getAll($query, $table_key, $invite_map_list, count($invite_map_list));
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}