<?php

namespace Compass\Thread;

/**
 * отправляем ws о добавлении списка сообщений в диалог
 */
class Domain_Thread_Action_Message_SendWsOnMessageListReceived {

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(array $meta_row, array $follower_row, array $talking_user_list,
					  array $message_list, Struct_SourceParentRel_Dynamic $source_parent_rel_dynamic,
					  string $parent_conversation_map, int $threads_updated_version):void {

		// список фолловеров
		$followers_users = Type_Thread_Followers::getFollowerUsersDiff($follower_row);

		// мета треда
		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($meta_row, 0, true);
		$thread_meta          = Apiv1_Format::threadMeta($prepared_thread_meta);

		$ws_users = Type_Thread_Meta::getActionUsersList($meta_row);

		$formatted_message_list = [];
		foreach ($message_list as $message) {

			$formatted_message_list[] = (object) Apiv1_Format::threadMessage(Type_Thread_Message_Main::getHandler($message)::prepareForFormat($message));

			$ws_users = array_merge($ws_users, Type_Thread_Message_Main::getHandler($message)::getUsers($message));
		}

		// отправляем список сообщений получателям
		Gateway_Bus_Sender::threadMessageListReceived(
			$talking_user_list,
			$formatted_message_list,
			[],
			$thread_meta,
			array_unique($ws_users),
			$followers_users,
			$source_parent_rel_dynamic->location_type,
			$parent_conversation_map,
			$threads_updated_version
		);
	}
}