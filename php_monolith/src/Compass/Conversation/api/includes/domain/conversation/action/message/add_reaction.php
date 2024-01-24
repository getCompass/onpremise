<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Gateway\BusFatalException;
use CompassApp\Pack\Message;

/**
 * Действие для добавляени реакций
 */
class Domain_Conversation_Action_Message_AddReaction {

	// добавляем реакцию на сообщение
	public static function do(string $message_map, string $conversation_map, array $meta_row, string $reaction_name, int $user_id):void {

		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		// получаем блок сообщения
		$block_row = Domain_Conversation_Entity_Message_Block_Get::getBlockRow($conversation_map, $message_map, $dynamic_row, true);

		// получаем сообщение из блока
		$message = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		// проверяем, что на сообщение можно ставить реакции
		self::_throwIfMessageIsNotAllowedForReaction($message, $user_id);

		// добавляем реакцию
		$updated_at_ms         = intval(microtime(true) * 1000);
		$talking_user_list     = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);
		$ws_event_version_list = Gateway_Bus_Sender::makeEventDataForAddReaction($conversation_map, $message_map, $reaction_name, $user_id, $updated_at_ms);

		// отправляем запрос на установку реакции
		Gateway_Bus_Company_Reaction::addInConversation($message_map, $reaction_name, $user_id, $updated_at_ms, $talking_user_list, $ws_event_version_list);

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::REACTION, $user_id);
		Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::ADD_REACTION);

		// отправляем реакцию в intercom
		self::_addMessagesToIntercomQueue($user_id, $message, $conversation_map, $reaction_name, $meta_row["type"]);

		// записываем время ответа если нужно
		Domain_Conversation_Action_Message_UpdateConversationAnswerState::doByAddReaction($conversation_map, $meta_row["type"], $user_id, time());

		// инкрементим количество действий
		Domain_User_Action_IncActionCount::incConversationReactionAdded($user_id, $conversation_map);
	}

	// проверяем, что на сообщение можно ставить реакции
	protected static function _throwIfMessageIsNotAllowedForReaction(array $message, int $user_id):void {

		// сообщение удалено
		if (Type_Conversation_Message_Main::getHandler($message)::isDeleted($message)) {
			throw new cs_Message_IsDeleted();
		}

		// на сообщение можно ставить/удалять реакцию
		// - тип сообщения позволяет
		// - сообщение не архивное
		if (!Type_Conversation_Message_Main::getHandler($message)::isAllowToReaction($message, $user_id)) {
			throw new cs_Message_IsNotAllowedForReaction();
		}
	}

	/**
	 * добавляем сообщения в очередь на отправку в intercom
	 *
	 * @param array  $message
	 * @param string $conversation_map
	 * @param string $reaction_name
	 * @param int    $conversation_type
	 *
	 * @throws BusFatalException
	 * @throws \parseException
	 */
	protected static function _addMessagesToIntercomQueue(int $user_id, array $message, string $conversation_map, string $reaction_name, int $conversation_type):void {

		// отправляем только из чата службы поддержки
		if (!Type_Conversation_Meta::isGroupSupportConversationType($conversation_type)) {
			return;
		}

		// если отправитель сообщения не человек и не оператор - не отправляем
		$sender_user_id = Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message);
		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$sender_user_id], false);
		if (!Type_User_Main::isHuman($user_info_list[$sender_user_id]->npc_type) && !Type_User_Main::isOperator($user_info_list[$sender_user_id]->npc_type)) {
			return;
		}

		$message_map         = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
		$message_to_intercom = [
			"text"           => "Поставил реакцию {$reaction_name}",
			"type"           => "text",
			"sender_user_id" => $user_id,
			"message_key"    => Message::doEncrypt($message_map),
		];

		// отправляем в очередь на отправку в intercom
		Gateway_Socket_Intercom::addMessageListToQueue(
			\CompassApp\Pack\Conversation::doEncrypt($conversation_map),
			getIp(),
			\BaseFrame\System\UserAgent::getUserAgent(),
			[$message_to_intercom]
		);
	}
}