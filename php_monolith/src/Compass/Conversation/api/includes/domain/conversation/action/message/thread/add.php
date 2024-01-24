<?php

namespace Compass\Conversation;

/**
 * добавляем тред к сообщению
 */
class Domain_Conversation_Action_Message_Thread_Add {

	public static function do(string $conversation_map, string $thread_map, string $message_map, bool $is_thread_hidden_for_all_users):string {

		// пытаемся внести запись, если существует, отдаем сущесвующую запись
		try {
			Type_Conversation_ThreadRel::add($conversation_map, $thread_map, $message_map, $is_thread_hidden_for_all_users);
		} catch (cs_Message_AlreadyContainsThread) {

			$thread_relation_row = Gateway_Db_CompanyConversation_MessageThreadRel::getOneByMessageMap($conversation_map, $message_map);
			$thread_map          = $thread_relation_row->thread_map;
		}
		return $thread_map;
	}
}