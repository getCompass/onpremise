<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы invite в company_conversation
 */
class Gateway_Db_CompanyConversation_ConversationInviteList extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "conversation_invite_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод создает запись
	public static function insert(int $user_id, int $sender_user_id, string $invite_map, int $created_at, int $status, string $conversation_map):void {

		$insert = [
			"conversation_map" => $conversation_map,
			"invite_map"       => $invite_map,
			"status"           => $status,
			"created_at"       => $created_at,
			"updated_at"       => $created_at,
			"user_id"          => $user_id,
			"sender_user_id"   => $sender_user_id,
		];
		static::_connect(static::_getDbKey())->insert(self::_getTable(), $insert, false);
	}

	// метод для обновления записи
	public static function set(string $invite_map, int $status, array $set):int {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "UPDATE `?p` SET ?u WHERE invite_map = ?s AND `status` = ?i LIMIT ?i";
		return static::_connect(static::_getDbKey())->update($query, self::_getTable(), $set, $invite_map, $status, 1);
	}

	// метод для получения записи
	public static function getByConversationMapAndUserId(int $user_id, int $sender_user_id, string $conversation_map):array {

		// запрос проверен на EXPLAIN (INDEX=`conversation_map`, `user_id`, `sender_user_id`)
		$query = "SELECT * FROM `?p` USE INDEX (`conversation_map`, `user_id`, `sender_user_id`) WHERE `conversation_map` = ?s AND `user_id` = ?i AND `sender_user_id` = ?i LIMIT ?i";
		return static::_connect(static::_getDbKey())
			->getOne($query, self::_getTable(), $conversation_map, $user_id, $sender_user_id, 1);
	}

	// метод для получения всех записей для получателя инвайта
	public static function getAllByStatusAndConversationMapAndUserId(int $user_id, string $conversation_map, int $status, int $count):array {

		// запрос проверен на EXPLAIN (INDEX=`conversation_map`, `user_id`, `status`)
		$query = "SELECT * FROM `?p` USE INDEX (`conversation_map`, `user_id`, `status`) WHERE `conversation_map` = ?s AND `user_id` = ?i AND `status` = ?i LIMIT ?i";
		return static::_connect(static::_getDbKey())
			->getAll($query, self::_getTable(), $conversation_map, $user_id, $status, $count);
	}

	// метод для получения записей всех записей для отправителя инвайта
	public static function getAllByStatusAndConversationMapAndSenderUserId(int $user_id, string $conversation_map, int $status):array {

		// запрос проверен на EXPLAIN (INDEX=`conversation_map`, `sender_user_id`, `status`)
		$query = "SELECT * FROM `?p` USE INDEX (`conversation_map`, `sender_user_id`, `status`) WHERE `conversation_map` = ?s AND `sender_user_id` = ?i AND `status` = ?i LIMIT ?i";
		return static::_connect(static::_getDbKey())
			->getAll($query, self::_getTable(), $conversation_map, $user_id, $status, Type_Invite_Handler::LIMIT_SEND_ACTIVE_INVITE);
	}

	// метод для получения списка приглашений по диалогу и статусу
	public static function getAll(string $conversation_map, int $status, int $count, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX=`status`)
		$query = "SELECT * FROM `?p` USE INDEX (`status`) WHERE `conversation_map` = ?s AND `status` = ?i ORDER BY `updated_at` ASC LIMIT ?i OFFSET ?i";
		return static::_connect(static::_getDbKey())->getAll($query, self::_getTable(), $conversation_map, $status, $count, $offset);
	}

	/**
	 * Метод для получения списка приглашений по диалогу и статусу без пагинации
	 *
	 * @param string $conversation_map
	 * @param int    $status
	 *
	 * @return array
	 */
	public static function getAllWithoutPagination(string $conversation_map, int $status):array {

		// запрос проверен на EXPLAIN (INDEX=`status`)
		$query = "SELECT * FROM `?p` USE INDEX (`status`) WHERE `conversation_map` = ?s AND `status` = ?i ORDER BY `updated_at` ASC LIMIT ?i";
		return static::_connect(static::_getDbKey())->getAll($query, self::_getTable(), $conversation_map, $status, 10000);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}