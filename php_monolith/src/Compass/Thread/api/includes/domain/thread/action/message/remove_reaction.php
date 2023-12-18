<?php

namespace Compass\Thread;

/**
 * Действие для добавляени реакций
 */
class Domain_Thread_Action_Message_RemoveReaction {

	// убираем реакцию с сообщения
	public static function do(string $message_map, string $thread_map, string $reaction_name, int $user_id, array $users):void {

		$block_id = \CompassApp\Pack\Message\Thread::getBlockId($message_map);

		// получаем блок треда
		$block_row = Type_Thread_Message_Block::get($thread_map, $block_id);

		// получаем сообщение из блока
		$message = Type_Thread_Message_Block::getMessage($message_map, $block_row);

		// проверяем, что с сообщения можно убирать реакции
		self::_throwIfMessageIsNotAllowedForReaction($message, $user_id);

		// убираем реакицю
		$updated_at_ms         = intval(microtime(true) * 1000);
		$talking_user_list     = Type_Thread_Meta_Users::getTalkingUserList($users);
		$ws_event_version_list = Gateway_Bus_Sender::makeEventDataForRemoveReaction($message_map, $reaction_name, $user_id, $updated_at_ms);

		/*
		 * здесь никаких проверок не может быть, потому что существует как минимум одна ситуация исключающая их:
		 * например: пользователь поставил и тут же убрал реакцию - горутина микросервиса, которая разгребает кэш,
		 * имеет интервал и если попасть в этот интервал, то факта, поставлена ли реакция пользователем или нет, в базе не будет
		 */

		// отправляем запрос на удаление реакции
		Gateway_Bus_Company_Reaction::removeInThread($message_map, $reaction_name, $user_id, $updated_at_ms, $talking_user_list, $ws_event_version_list);
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