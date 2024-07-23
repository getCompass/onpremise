<?php

declare(strict_types = 1);

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Action для создания тредов при миграции со слака
 */
class Domain_Thread_Action_AddByMigration {

	/**
	 * выполняем
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 * @param string $conversation_map
	 *
	 * @return string
	 * @throws ReturnFatalException
	 * @throws cs_ConversationIsLocked
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Thread_ParentEntityNotFound
	 * @long
	 */
	public static function do(int $user_id, string $message_map, string $conversation_map):string {

		$response = self::_sendSocketGetMetaForCreateThread($user_id, $message_map);
		if ($response["is_exist"] == 1) {
			return $response["thread_map"];
		}

		$creator_user_id    = $response["creator_user_id"];
		$message_created_at = $response["message_created_at"];

		// создаем тред, передавая пустой $user_mute_info, потому что треды на данный момент больше не мьютятся,
		// поэтому мы не обращаем внимания на то, что сам диалог/группа могли быть замьючены
		$thread_meta_row = self::_createPrivateThread(
			$conversation_map,
			$response["users"],
			$user_id,
			$creator_user_id,
			$message_map,
			$message_created_at,
		);

		// отправляем сокет запрос на прикрепление треда к сообщению
		$response = self::_sendSocketAddThreadToMessage($user_id, $thread_meta_row["thread_map"], $message_map, false);
		if ($response["thread_map"] != $thread_meta_row["thread_map"]) {
			return $response["thread_map"];
		}

		$message_map = Type_Thread_ParentRel::getMap($thread_meta_row["parent_rel"]);

		$source_parent_map = "";

		$source_parent_type = Type_Thread_ParentRel::getType($thread_meta_row["parent_rel"]);
		if (Type_Thread_Utils::isConversationMessageParent($source_parent_type)) {
			$source_parent_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		}

		$insert_list[] = [
			"user_id"               => $user_id,
			"thread_map"            => $thread_meta_row["thread_map"],
			"source_parent_map"     => $source_parent_map,
			"source_parent_type"    => $source_parent_type,
			"is_hidden"             => 1,
			"is_follow"             => 0,
			"is_muted"              => 0,
			"unread_count"          => 0,
			"created_at"            => time(),
			"updated_at"            => time(),
			"last_read_message_map" => "",
			"parent_rel"            => $thread_meta_row["parent_rel"],
		];
		ShardingGateway::database("company_thread")->insertArray("user_thread_menu", $insert_list);

		$insert_array[] = [
			"user_id"              => $user_id,
			"thread_unread_count"  => 0,
			"message_unread_count" => 0,
			"created_at"           => time(),
			"updated_at"           => 0,
		];
		ShardingGateway::database("company_thread")->insertArray("user_inbox", $insert_array);

		return $thread_meta_row["thread_map"];
	}

	/**
	 * получаем мету диалога для создания треда
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Thread_ParentEntityNotFound
	 */
	protected static function _sendSocketGetMetaForCreateThread(int $user_id, string $message_map):array {

		// запрос к php_conversation, спрашиваем можно ли прикрепить тред к этому сообщению
		try {
			return Gateway_Socket_Conversation::getMetaForMigrationCreateThread($user_id, $message_map);
		} catch (Gateway_Socket_Exception_Conversation_MessageIsDeleted) {
			throw new cs_Message_IsDeleted();
		} catch (Gateway_Socket_Exception_Conversation_NotFound) {
			throw new cs_Thread_ParentEntityNotFound();
		}
	}

	/**
	 * метод для создания треда
	 *
	 * @param string $conversation_map
	 * @param array  $users
	 * @param int    $user_id
	 * @param int    $creator_user_id
	 * @param string $message_map
	 * @param int    $message_created_at
	 *
	 * @return array
	 * @long
	 */
	protected static function _createPrivateThread(
		string $conversation_map,
		array $users,
		int $user_id,
		int $creator_user_id,
		string $message_map,
		int $message_created_at,
	):array {

		$users      = self::_makeThreadMetaUsers($users);
		$parent_rel = Type_Thread_ParentRel::create(
			PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE,
			$creator_user_id,
			$message_map,
			$message_created_at,
			[]
		);

		$source_parent_rel = Type_Thread_SourceParentRel::create($conversation_map, SOURCE_PARENT_ENTITY_TYPE_CONVERSATION);
		return Type_Thread_Private::create(
			$users,
			$source_parent_rel,
			$parent_rel,
			$user_id,
			$creator_user_id,
			true,
			[]
		);
	}

	/**
	 * формируем участников треда на основе участников meta сущности
	 *
	 */
	protected static function _makeThreadMetaUsers(array $users):array {

		$thread_meta_users = [];
		foreach ($users as $user_info) {
			$thread_meta_users[$user_info["user_id"]] = Type_Thread_Meta_Users::initUserSchema(THREAD_MEMBER_ACCESS_ALL);
		}

		return $thread_meta_users;
	}

	/**
	 * получаем мету диалога для создания треда
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 * @param string $message_map
	 * @param bool   $is_thread_hidden_for_all_users
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws cs_ConversationIsLocked
	 */
	protected static function _sendSocketAddThreadToMessage(int $user_id, string $thread_map, string $message_map, bool $is_thread_hidden_for_all_users):array {

		// запрос к php_conversation, добавляем тред к сообщению
		try {
			return Gateway_Socket_Conversation::addThreadToMessage($user_id, $message_map, $thread_map, $is_thread_hidden_for_all_users);
		} catch (Gateway_Socket_Exception_Conversation_IsLocked) {
			throw new cs_ConversationIsLocked();
		}
	}
}