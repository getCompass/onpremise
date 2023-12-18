<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Action для репоста
 */
class Domain_Thread_Action_Message_AddRepost {

	/**
	 * Репостим сообщение
	 *
	 * @param string $from_thread_map
	 * @param string $receiver_thread_map
	 * @param array  $meta_row
	 * @param array  $message_map_list
	 * @param string $client_message_id
	 * @param int    $user_id
	 * @param string $text
	 * @param array  $mention_user_id_list
	 * @param string $platform
	 * @param string $source_type
	 *
	 * @return array
	 * @throws Domain_Thread_Exception_Message_IsDuplicated
	 * @throws Domain_Thread_Exception_Message_IsFromDifferentSource
	 * @throws Domain_Thread_Exception_Message_IsNotFromThread
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws Domain_Thread_Exception_Message_RepostLimitExceeded
	 * @throws Domain_Thread_Exception_UserHaveNoAccessToSource
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ParentMessage_IsDeleted
	 * @throws cs_ParentMessage_IsRespect
	 * @throws cs_ThreadIsReadOnly
	 * @long
	 */
	public static function do(string $from_thread_map, string $receiver_thread_map, array $meta_row, array $message_map_list, string $client_message_id,
					  int    $user_id, string $text, array $mention_user_id_list, string $platform, string $source_type):array {

		// узнаем, есть ли доступ к треду-отправителю
		try {
			$from_meta_row = Helper_Threads::getMetaIfUserMember($from_thread_map, $user_id);
		} catch (cs_Message_HaveNotAccess | cs_Thread_UserNotMember) {
			throw new Domain_Thread_Exception_UserHaveNoAccessToSource("no access to source thread");
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			$from_meta_row = $e->getMetaRow();
		}

		// получаем родительское сообщение, если нужно
		$parent_message = Type_Thread_Rel_Parent::getParentMessageIfNeed(
			$user_id, $from_meta_row, $source_type == Domain_Thread_Entity_Repost::REPOST_FROM_THREAD_WITH_PARENT_TYPE);

		// подготавливаем сообщения для репоста
		$message_map_list = Domain_Thread_Action_Message_PrepareListForRepost::do($message_map_list, $from_thread_map);

		$prepared_message_list = [];
		$dynamic_obj           = Type_Thread_Dynamic::get($from_thread_map);

		// формируем массивы со всеми сообщениями
		[$chunk_repost_message_list, $total_message_count] = Helper_Threads::getChunkMessageList($message_map_list, $dynamic_obj, $parent_message, $user_id);

		if ($total_message_count > Type_Thread_Message_Handler_Default::MAX_SELECTED_MESSAGE_COUNT_WITH_REPOST_OR_QUOTE) {
			throw new Domain_Thread_Exception_Message_RepostLimitExceeded("too many messages for repost");
		}

		// отправляем репосты
		$add_message_result = self::_sendRepostMessages(
			$receiver_thread_map, $meta_row, $chunk_repost_message_list, $text, $user_id, $mention_user_id_list, $client_message_id, $platform);

		// форматируем мету треда
		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($add_message_result["meta_row"], $user_id);

		// форматируем сообщения
		foreach ($add_message_result["message_list"] as $message) {
			$prepared_message_list[] = Type_Thread_Message_Main::getHandler($message)::prepareForFormat($message);
		}

		// записываем в базу запись о репосте
		self::_afterRepost($from_thread_map, $receiver_thread_map, $user_id, $message_map_list);

		return [$prepared_thread_meta, $prepared_message_list];
	}

	/**
	 * Отправляем репосты
	 *
	 * @param string $receiver_thread_map
	 * @param array  $meta_row
	 * @param array  $chunk_repost_message_list
	 * @param string $text
	 * @param int    $user_id
	 * @param array  $mention_user_id_list
	 * @param string $client_message_id
	 * @param string $platform
	 *
	 * @return array
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	protected static function _sendRepostMessages(string $receiver_thread_map, array $meta_row, array $chunk_repost_message_list, string $text,
								    int    $user_id, array $mention_user_id_list, string $client_message_id, string $platform):array {

		// собираем список сообщений для репоста
		$repost_list = [];
		foreach ($chunk_repost_message_list as $k => $repost_message_list) {

			// текст должен быть только у первого сообщения - у остальных убираем
			if ($k != 0) {
				$text = "";
			}

			// формируем репост
			$repost        = Type_Thread_Message_Main::getLastVersionHandler()::makeRepost(
				$user_id,
				$text,
				"{$client_message_id}_{$k}",
				$repost_message_list,
				$platform);
			$repost_list[] = Type_Thread_Message_Main::getHandler($repost)::addMentionUserIdList($repost, $mention_user_id_list);
		}
		if (count($repost_list) < 1) {
			throw new Domain_Thread_Exception_Message_ListIsEmpty("message list is empty");
		}
		return Domain_Thread_Action_Message_AddList::do($receiver_thread_map, $meta_row, $repost_list);
	}

	/**
	 * Действия после репоста
	 *
	 * @param string $from_thread_map
	 * @param string $receiver_thread_map
	 * @param int    $user_id
	 * @param array  $message_map_list
	 *
	 * @return void
	 */
	protected static function _afterRepost(string $from_thread_map, string $receiver_thread_map, int $user_id, array $message_map_list):void {

		$set = [];
		foreach ($message_map_list as $v) {

			$set[] = [
				"thread_map"          => $from_thread_map,
				"message_map"         => $v,
				"receiver_thread_map" => $receiver_thread_map,
				"user_id"             => $user_id,
				"is_deleted"          => 0,
				"created_at"          => time(),
				"updated_at"          => 0,
				"deleted_at"          => 0,
			];
		}

		// если что то репостнули по итогу, добавляем в базу
		if (count($set) > 0) {
			Gateway_Db_CompanyThread_MessageRepostThreadRel::insertArray($set);
		}
	}
}