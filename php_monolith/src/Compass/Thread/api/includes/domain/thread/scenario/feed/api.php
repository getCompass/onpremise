<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии для api второй версии сообщений в треде
 */
class Domain_Thread_Scenario_Feed_Api {

	/**
	 * Возвращает сообщения из запрошенного списка блоков.
	 *
	 * @param int    $user_id
	 * @param string $thread_map
	 * @param int[]  $block_id_list
	 *
	 * @return array
	 *
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_UserNotMember
	 * @throws \BaseFrame\Exception\Request\CaseException
	 * @long
	 */
	public static function getMessages(int $user_id, string $thread_map, array $block_id_list):array {

		try {

			// получаем мету диалога, она нам по сути не очень-то и нужна,
			// но тут проверяются права на доступность треда, зарефакторить
			// все это дело займет много времени, поэтому простите меня
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $user_id);
		} catch (cs_Conversation_IsBlockedOrDisabled) {

			// игнорируем происходящее, просто сингл-диалог заблокирован на обновление
			$meta_row = Type_Thread_Meta::getOne($thread_map);
		}

		// получаем динамику треда,и формируем корректный список блоков для чтения
		$thread_dynamic = Type_Thread_Dynamic::get($thread_map);
		$block_id_list  = Domain_Thread_Entity_MessageBlock::resolveCorrectBlockIdList($thread_dynamic, $block_id_list);

		// если блоков не осталось, то завершаем исполнение и возвращаем пустоту
		if (count($block_id_list) === 0) {
			return [[], [], [], []];
		}

		// проверяем итоговый список блоков
		static::_assertBlockIdList($block_id_list);

		// данные для ответа
		$message_list = [];
		$user_list    = [];

		// получаем блоки с сообщениями и список реакций к ним
		$block_list          = Domain_Thread_Entity_MessageBlock::getList($thread_map, $block_id_list);
		$block_reaction_list = Gateway_Db_CompanyThread_MessageBlockReactionList::getList($thread_map, $block_id_list);

		foreach ($block_list as $block) {

			/** @var Struct_Db_CompanyThread_MessageBlockReaction $block_reaction получаем список реакций для блока */
			$block_reaction = $block_reaction_list[$block["block_id"]] ?? null;

			// проходимся по всем сообщениям в блоке и добавляем данные в ответ
			foreach (Domain_Thread_Entity_MessageBlock_Message::iterate($block) as $message) {

				// если сообщение скрыто для пользователя, то пропускаем его в выдаче
				if (Domain_Thread_Entity_Message::isInvisibleForUser($user_id, $message)) {
					continue;
				}

				// получаем информацию по реакциям для сообщения из блока реакций
				[$reaction_list, $reaction_last_edited_at] = ($block_reaction !== null)
					? Domain_Thread_Entity_MessageBlock_Reaction::fetchMessageReactionData($block_reaction, $message["message_map"])
					: [[], 0];

				// добавляем сообщение и фиксируем список пользователей для него
				$message_list[] = Type_Thread_Message_Main::getHandler($message)::prepareForFormat($message, $reaction_list, $reaction_last_edited_at);
				array_push($user_list, ...Type_Thread_Message_Main::getHandler($message)::getUsers($message));
			}
		}

		$prepared_thread_meta  = Type_Thread_Utils::prepareThreadMetaForFormat($meta_row, $user_id);
		$formatted_thread_meta = Apiv2_Format::threadMeta($prepared_thread_meta);

		[$previous_block_id_list, $next_block_id_list] = Domain_Thread_Entity_MessageBlock::getAroundNBlocks($thread_dynamic, $block_id_list);
		return [$message_list, $formatted_thread_meta, $previous_block_id_list, $next_block_id_list, array_unique($user_list)];
	}

	/**
	 * Проверяем сформированный список блоков на корректность.
	 */
	protected static function _assertBlockIdList(array $block_id_list):void {

		// если вдруг запросили слишком много блоков за один раз
		if (count($block_id_list) > Domain_Thread_Entity_MessageBlock::MAX_GET_MESSAGES_BLOCK_COUNT) {
			throw new ParamException("to many block requested");
		}
	}

	/**
	 * Добавить репост к сообщению
	 *
	 * @param int    $user_id
	 * @param string $source_type
	 * @param string $from_source_map
	 * @param string $receiver_thread_map
	 * @param array  $message_map_list
	 * @param string $message_text
	 * @param string $client_message_id
	 * @param string $platform
	 *
	 * @return array
	 * @long - switch..case
	 * @throws Domain_Thread_Exception_Message_IsDuplicated
	 * @throws Domain_Thread_Exception_Message_IsFromDifferentSource
	 * @throws Domain_Thread_Exception_Message_IsNotFromThread
	 * @throws Domain_Thread_Exception_Message_IsTooLong
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws Domain_Thread_Exception_Message_RepostLimitExceeded
	 * @throws Domain_Thread_Exception_NoAccessUserbotDeleted
	 * @throws Domain_Thread_Exception_NoAccessUserbotDisabled
	 * @throws Domain_Thread_Exception_UserHaveNoAccess
	 * @throws Domain_Thread_Exception_UserHaveNoAccessToSource
	 * @throws Domain_Thread_Exception_User_IsAccountDeleted
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ParentMessage_IsDeleted
	 * @throws cs_ParentMessage_IsRespect
	 * @throws cs_ThreadIsReadOnly
	 */
	public static function addRepost(int   $user_id, string $source_type, string $from_source_map, string $receiver_thread_map,
									 array $message_map_list, string $message_text, string $client_message_id, string $platform):array {

		// проверяем, что у репостящего есть доступ к треду
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($receiver_thread_map, $user_id);
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {

			throw match ($e->getAllowStatus()) {
				Type_Thread_Utils::CONVERSATION_ALLOW_STATUS_USERBOT_IS_DISABLED => new Domain_Thread_Exception_NoAccessUserbotDisabled("userbot is disabled"),
				Type_Thread_Utils::CONVERSATION_ALLOW_STATUS_USERBOT_IS_DELETED  => new Domain_Thread_Exception_NoAccessUserbotDeleted("userbot is deleted"),
				Type_Thread_Utils::CONVERSATION_ALLOW_STATUS_MEMBER_IS_DELETED   => new Domain_Thread_Exception_User_IsAccountDeleted("user delete his account"),
				default                                                          => new Domain_Thread_Exception_UserHaveNoAccess("no access to receiver thread"),
			};
		} catch (cs_Message_HaveNotAccess|cs_Thread_UserNotMember) {
			throw new Domain_Thread_Exception_UserHaveNoAccess("no access to receiver thread");
		}

		// чистим текст от недопустимого шлака и фильтруем эмодзи
		$text = Type_Api_Filter::replaceEmojiWithShortName($message_text);
		if (mb_strlen($text) > Type_Api_Filter::MAX_MESSAGE_TEXT_LENGTH) {
			throw new Domain_Thread_Exception_Message_IsTooLong("message for repost is too long");
		}
		$text                 = Type_Api_Filter::sanitizeMessageText($text);
		$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($meta_row, $text);

		$repost_result = match ($source_type) {

			Domain_Thread_Entity_Repost::REPOST_FROM_CONVERSATION_TYPE                                                             => Domain_Thread_Action_Message_AddRepostFromConversation::do(
				$from_source_map, $receiver_thread_map, $meta_row, $message_map_list, $client_message_id, $user_id, $text, $mention_user_id_list, $platform),
			Domain_Thread_Entity_Repost::REPOST_FROM_THREAD_TYPE, Domain_Thread_Entity_Repost::REPOST_FROM_THREAD_WITH_PARENT_TYPE =>
			Domain_Thread_Action_Message_AddRepost::do($from_source_map, $receiver_thread_map, $meta_row,
				$message_map_list, $client_message_id, $user_id, $text, $mention_user_id_list, $platform, $source_type),
			default                                                                                                                => throw new ParseFatalException("incorrect source type"),
		};

		// подписываем пользователей, которых упомянули в треде
		Helper_Threads::attachUsersToThread($meta_row, $mention_user_id_list);

		return $repost_result;
	}
}