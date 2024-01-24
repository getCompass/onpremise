<?php

namespace Compass\Conversation;

/**
 * класс для работы с сущностью приглашения
 */
class Type_Conversation_Invite {

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// получить информацию о приглашении в соотвествующий диалог
	public static function getByConversationMapAndUserId(int $sender_user_id, int $user_id, string $group_conversation_map):array {

		return Gateway_Db_CompanyConversation_ConversationInviteList::getByConversationMapAndUserId($user_id, $sender_user_id, $group_conversation_map);
	}

	// получить список всех инватов по диалогу
	public static function getInviteListForGroupConversation(string $conversation_map, int $status, int $count, int $offset):array {

		return Gateway_Db_CompanyConversation_ConversationInviteList::getAll($conversation_map, $status, $count, $offset);
	}

	// получить все записи для получателя инвайта
	public static function getAllByStatusAndConversationMapAndUserId(int $user_id, string $group_conversation_map, int $status, int $count):array {

		return Gateway_Db_CompanyConversation_ConversationInviteList::getAllByStatusAndConversationMapAndUserId($user_id, $group_conversation_map, $status, $count);
	}

	// получить все записи для отправителя инвайта
	public static function getAllByStatusAndConversationMapAndSenderUserId(int $user_id, string $group_conversation_map, int $status):array {

		return Gateway_Db_CompanyConversation_ConversationInviteList::getAllByStatusAndConversationMapAndSenderUserId($user_id, $group_conversation_map, $status);
	}
}