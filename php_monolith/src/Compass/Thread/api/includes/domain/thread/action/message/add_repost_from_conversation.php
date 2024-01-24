<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Action для репоста
 */
class Domain_Thread_Action_Message_AddRepostFromConversation {

	/**
	 * Репостим сообщение из чата
	 *
	 * @param string $from_conversation_map
	 * @param string $receiver_thread_map
	 * @param array  $meta_row
	 * @param array  $message_map_list
	 * @param string $client_message_id
	 * @param int    $user_id
	 * @param string $text
	 * @param array  $mention_user_id_list
	 * @param string $platform
	 *
	 * @return array
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws Domain_Thread_Exception_Message_RepostLimitExceeded
	 * @throws Domain_Thread_Exception_UserHaveNoAccessToSource
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	public static function do(string $from_conversation_map, string $receiver_thread_map, array $meta_row, array $message_map_list, string $client_message_id,
					  int    $user_id, string $text, array $mention_user_id_list, string $platform):array {

		$prepared_message_list = [];

		try {

			$repost_message_list = Gateway_Socket_Conversation::getRepostMessages(
				$user_id, $from_conversation_map, $message_map_list, $client_message_id, $text, $platform);
		} catch (Gateway_Socket_Exception_Conversation_UserIsNotMember|Gateway_Socket_Exception_Conversation_IsLocked) {
			throw new Domain_Thread_Exception_UserHaveNoAccessToSource("user has no access to conversation");
		} catch (Gateway_Socket_Exception_Conversation_MessageListIsEmpty) {
			throw new Domain_Thread_Exception_Message_ListIsEmpty("message list is empty");
		}

		// подготавливаем сообщения для репоста из чата
		$repost_list = self::_prepareChunkRepostMessageListFromConversation(
			$user_id, $repost_message_list, $text, $client_message_id, $mention_user_id_list, $platform);

		// отправляем сообщения репостов и готовим ответ
		$add_message_result   = Domain_Thread_Action_Message_AddList::do($receiver_thread_map, $meta_row, $repost_list);
		$prepared_thread_meta = Type_Thread_Utils::prepareThreadMetaForFormat($add_message_result["meta_row"], $user_id);

		// форматируем сообщения
		foreach ($add_message_result["message_list"] as $message) {

			$prepared_message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::prepareForFormat($message);
		}

		// подтверждаем, что репост прошел успешно, и можно записать в базу историю
		Gateway_Socket_Conversation::confirmThreadRepost($user_id, $from_conversation_map, $receiver_thread_map, $message_map_list);

		return [$prepared_thread_meta, $prepared_message_list];
	}

	/**
	 * Подготавливаем сообщения для репоста из чата
	 *
	 * @throws \parseException
	 * @throws Domain_Thread_Exception_Message_RepostLimitExceeded
	 */
	protected static function _prepareChunkRepostMessageListFromConversation(int    $user_id, array $repost_list, string $text,
													 string $client_message_id, array $mention_user_id_list, string $platform):array {

		$total_repost_message_count = 0;

		$message_list = [];
		foreach ($repost_list as $k => $reposted_message_list) {

			// текст нужен только у первого сообщения
			if ($k !== 0) {
				$text = "";
			}

			// подготавливаем сообщения для репоста из треда
			[$prepared_reposted_message_list, $repost_message_count, $is_with_quote_or_repost] =
				Type_Thread_Message_Handler_Default::prepareConversationMessageListBeforeRepost($reposted_message_list);

			$total_repost_message_count += $repost_message_count;

			// завершаем работу, если перевалили за лимит
			self::_throwIfTooManyRepostMessages($total_repost_message_count, $is_with_quote_or_repost);

			// создаем сообщение типа тред-репост
			$handler_class = Type_Thread_Message_Main::getLastVersionHandler();
			$message       = $handler_class::makeRepost(
				$user_id, $text, "{$client_message_id}_{$k}", $prepared_reposted_message_list, $platform
			);

			$message_list[] = Type_Thread_Message_Main::getHandler($message)::addMentionUserIdList($message, $mention_user_id_list);
		}

		return $message_list;
	}

	/**
	 * Выкинуть исключение, если для репоста слишком много сообщений
	 *
	 * @param int $is_with_quote_or_repost
	 * @param int $count
	 *
	 * @return void
	 * @throws Domain_Thread_Exception_Message_RepostLimitExceeded
	 */
	protected static function _throwIfTooManyRepostMessages(int $count, int $is_with_quote_or_repost):void {

		// слишком много сообщений в репостах
		if (($is_with_quote_or_repost && $count > Type_Thread_Message_Handler_Default::MAX_SELECTED_MESSAGE_COUNT_WITH_REPOST_OR_QUOTE)
			|| (!$is_with_quote_or_repost && $count > Type_Thread_Message_Handler_Default::MAX_SELECTED_MESSAGE_COUNT_WITHOUT_REPOST_OR_QUOTE)) {

			throw new Domain_Thread_Exception_Message_RepostLimitExceeded("too many messages in repost");
		}
	}
}