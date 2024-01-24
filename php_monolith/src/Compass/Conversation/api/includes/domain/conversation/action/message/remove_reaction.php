<?php

namespace Compass\Conversation;

/**
 * Действие для добавляени реакций
 */
class Domain_Conversation_Action_Message_RemoveReaction {

	// убираем реакцию с сообщения
	public static function do(string $message_map, string $conversation_map, string $reaction_name, int $user_id, array $users):void {

		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		$block_row   = Domain_Conversation_Entity_Message_Block_Get::getBlockRow($conversation_map, $message_map, $dynamic_row, true);

		// получаем сообщение из блока
		$message = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		// проверяем, что с сообщения можно убирать реакции
		self::_throwIfMessageIsNotAllowedForReaction($message, $user_id);

		// убираем реакицю
		$updated_at_ms         = intval(microtime(true) * 1000);
		$talking_user_list     = Type_Conversation_Meta_Users::getTalkingUserList($users);
		$ws_event_version_list = Gateway_Bus_Sender::makeEventVersionListForRemoveReaction($conversation_map, $message_map, $reaction_name, $user_id, $updated_at_ms);

		/*
		 * здесь никаких проверок не может быть, потому что существует как минимум одна ситуация исключающая их:
		 * например: пользователь поставил и тут же убрал реакцию - горутина микросервиса, которая разгребает кэш,
		 * имеет интервал и если попасть в этот интервал, то факта, поставлена ли реакция пользователем или нет, в базе не будет
		 */

		// отправляем запрос на удаление реакции
		Gateway_Bus_Company_Reaction::removeInConversation($message_map, $reaction_name, $user_id, $updated_at_ms, $talking_user_list, $ws_event_version_list);
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
}