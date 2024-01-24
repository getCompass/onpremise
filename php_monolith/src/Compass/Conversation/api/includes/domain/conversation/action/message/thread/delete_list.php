<?php

namespace Compass\Conversation;

/**
 * удаляем треды у сообщений
 */
class Domain_Conversation_Action_Message_Thread_DeleteList {

	public static function do(array $thread_map_list, array $message_map_list):void {

		if (count($thread_map_list) < 1) {
			return;
		}

		// отправляем задачу в phphooker, чтобы поставить тредам is_deleted = 1
		Type_Phphooker_Main::setParentMessageListIsDeletedIfThreadExist($thread_map_list);

		// удаляем родительские сообщения из кэша
		Type_Phphooker_Main::sendClearParentMessageListCache($message_map_list);
	}
}