<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы user_dynamic в company_conversation
 */
class Gateway_Db_CompanyConversation_UserDynamic extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "user_dynamic";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод создает запись
	public static function insert(string $conversation_map, int $sender_user_id):void {

		$insert = [
			"conversation_map"           => $conversation_map,
			"user_id"                    => $sender_user_id,
			"count_sender_active_invite" => 0,
			"created_at"                 => time(),
			"updated_at"                 => 0,
		];

		static::_connect(static::_getDbKey())->insert(self::_getTable(), $insert);
	}

	// метод для инкремента счетчика активных приглашений отправленных пользователем в данный диалог, если количество меньше лимита
	public static function incActiveInviteCount(string $conversation_map, int $user_id):int {

		$set = [
			"count_sender_active_invite" => "count_sender_active_invite + 1",
			"updated_at"                 => time(),
		];

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "UPDATE `?p` SET ?u WHERE conversation_map = ?s AND `user_id` = ?i AND `count_sender_active_invite` < ?i LIMIT ?i";
		return static::_connect(static::_getDbKey())
			->update($query, self::_getTable(), $set, $conversation_map, $user_id, Type_Invite_Handler::LIMIT_SEND_ACTIVE_INVITE, 1);
	}

	// метод для декремента счетчика активных приглашений отправленных пользователем в данный диалог
	public static function decActiveInviteCount(string $conversation_map, int $user_id):int {

		$set = [
			"count_sender_active_invite" => "count_sender_active_invite - 1",
			"updated_at"                 => time(),
		];

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "UPDATE `?p` SET ?u WHERE conversation_map = ?s AND `user_id` = ?i LIMIT ?i";
		return static::_connect(static::_getDbKey())->update($query, self::_getTable(), $set, $conversation_map, $user_id, 1);
	}

	// метод для получения записи
	public static function get(string $conversation_map, int $user_id):array {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "SELECT * FROM `?p` WHERE conversation_map = ?s AND `user_id` = ?i LIMIT ?i";
		return static::_connect(static::_getDbKey())->getOne($query, self::_getTable(), $conversation_map, $user_id, 1);
	}

	// метод для получения записей на обновление
	public static function getForUpdate(string $conversation_map, int $user_id):array {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "SELECT * FROM `?p` WHERE conversation_map = ?s AND `user_id` = ?i LIMIT ?i FOR UPDATE";
		return static::_connect(static::_getDbKey())->getOne($query, self::_getTable(), $conversation_map, $user_id, 1);
	}

	// получаем все записи
	public static function getAll(int $sender_user_id, array $conversation_map_list):array {

		$conversation_map_list_grouped_by_shard_id = [];
		foreach ($conversation_map_list as $conversation_map) {

			$shard_key                                               = self::_getDbKey();
			$conversation_map_list_grouped_by_shard_id[$shard_key][] = $conversation_map;
		}

		$output = [];
		foreach ($conversation_map_list_grouped_by_shard_id as $shard_key => $v) {

			// получаем инвайты
			$query = "SELECT * FROM `?p` WHERE conversation_map IN (?a) AND user_id = ?i LIMIT ?i";
			$list  = static::_connect($shard_key)->getAll($query, self::_getTable(), $conversation_map_list, $sender_user_id, count($conversation_map_list));

			foreach ($list as $row) {
				$output[] = $row;
			}
		}

		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}