<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы company_conversation.user_invite_rel
 */
class Gateway_Db_CompanyConversation_UserInviteRel extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "user_invite_rel";

	// создание записи
	public static function insert(int $user_id, int $sender_user_id, string $invite_map, int $created_at, int $status, string $conversation_map):void {

		$insert = [
			"user_id"                => $user_id,
			"invite_map"             => $invite_map,
			"status"                 => $status,
			"created_at"             => $created_at,
			"updated_at"             => $created_at,
			"sender_user_id"         => $sender_user_id,
			"group_conversation_map" => $conversation_map,
		];

		static::_connect(self::_getDbKey())->insert(self::_getTable(), $insert, false);
	}

	// обновление записи
	public static function set(int $user_id, string $invite_map, int $expected_status, array $set):int {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "UPDATE `?p` SET ?u WHERE user_id = ?s AND `invite_map` = ?s AND `status` = ?i LIMIT ?i";
		return static::_connect(self::_getDbKey())->update($query, self::_getTable(), $set, $user_id, $invite_map, $expected_status, 1);
	}

	// получаем инвайты по id пользователю и статусу инвайта
	public static function getByUserIdAndStatus(int $user_id, int $status, int $limit, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX=`get_by_user_id_and_status`)
		$query = "SELECT * FROM `?p` USE INDEX (`get_by_user_id_and_status`) WHERE `user_id` = ?i AND `status` = ?i LIMIT ?i OFFSET ?i";
		return static::_connect(self::_getDbKey())->getAll($query, self::_getTable(), $user_id, $status, $limit, $offset);
	}

	// получаем название таблицы
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}