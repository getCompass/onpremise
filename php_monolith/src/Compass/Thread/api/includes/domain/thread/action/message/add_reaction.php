<?php

namespace Compass\Thread;

/**
 * Действие для добавляени реакций
 */
class Domain_Thread_Action_Message_AddReaction {

	// добавляем реакцию на сообщение
	public static function do(string $message_map, string $thread_map, array $meta_row, string $reaction_name, int $user_id):void {

		$block_id = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

		// получаем блок треда
		$block_row = Type_Thread_Message_Block::get($thread_map, $block_id);

		// получаем сообщение из блока
		$message = Type_Thread_Message_Block::getMessage($message_map, $block_row);

		// проверяем, что на сообщение можно ставить реакции
		self::_throwIfMessageIsNotAllowedForReaction($message, $user_id);

		// добавляем реакцию
		$updated_at_ms         = intval(microtime(true) * 1000);
		$talking_user_list     = Type_Thread_Meta_Users::getTalkingUserList($meta_row["users"]);
		$ws_event_version_list = Gateway_Bus_Sender::makeEventDataForAddReaction($message_map, $reaction_name, $user_id, $updated_at_ms);

		// отправляем запрос на установку реакции
		Gateway_Bus_Company_Reaction::addInThread($message_map, $reaction_name, $user_id, $updated_at_ms, $talking_user_list, $ws_event_version_list);

		Gateway_Bus_Company_Rating::inc(Gateway_Bus_Company_Rating::REACTION, $user_id);
		Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::ADD_REACTION);

		// записываем время ответа если нужно
		$parent_conversation_map = Type_Thread_SourceParentRel::getMap($meta_row["source_parent_rel"]);
		Domain_Thread_Action_Message_UpdateConversationAnswerState::doByAddReaction($parent_conversation_map, $user_id, time());

		// инкрементим статистику
		Domain_User_Action_IncActionCount::incThreadReactionAdded($user_id, $parent_conversation_map);
	}

	// проверяем, что на сообщение можно ставить реакции
	protected static function _throwIfMessageIsNotAllowedForReaction(array $message, int $user_id):void {

		// сообщение удалено
		if (Type_Thread_Message_Main::getHandler($message)::isMessageDeleted($message)) {
			throw new cs_Message_IsDeleted();
		}

		// на сообщение можно ставить/удалять реакцию
		// - тип сообщения позволяет
		// - сообщение не архивное
		if (!Type_Thread_Message_Main::getHandler($message)::isAllowToReaction($message, $user_id)) {
			throw new cs_Message_IsNotAllowedForReaction();
		}
	}
}