<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\ActionNotAllowed;
use CompassApp\Domain\Member\Exception\UserIsGuest;

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
	 * @long - switch..case
	 * @throws Domain_Group_Exception_NotEnoughRights
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
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BlockException
	 * @throws CaseException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ParentMessage_IsDeleted
	 * @throws cs_ParentMessage_IsRespect
	 * @throws cs_ThreadIsReadOnly
	 */
	public static function addRepost(int   $user_id, string $source_type, string $from_source_map, string $receiver_thread_map,
						   array $message_map_list, string $message_text, string $client_message_id, string $platform, int $method_version):array {

		// проверяем, что у репостящего есть доступ к треду
		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($receiver_thread_map, $user_id);

			if ($method_version >= 3) {
				Domain_Group_Entity_Options::checkCommentRestrictionByThreadMeta($user_id, $meta_row);
			}
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {

			throw match ($e->getAllowStatus()) {
				Type_Thread_Utils::CONVERSATION_ALLOW_STATUS_USERBOT_IS_DISABLED => new Domain_Thread_Exception_NoAccessUserbotDisabled("userbot is disabled"),
				Type_Thread_Utils::CONVERSATION_ALLOW_STATUS_USERBOT_IS_DELETED => new Domain_Thread_Exception_NoAccessUserbotDeleted("userbot is deleted"),
				Type_Thread_Utils::CONVERSATION_ALLOW_STATUS_MEMBER_IS_DELETED => new Domain_Thread_Exception_User_IsAccountDeleted("user delete his account"),
				default => new Domain_Thread_Exception_UserHaveNoAccess("no access to receiver thread"),
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

			Domain_Thread_Entity_Repost::REPOST_FROM_CONVERSATION_TYPE => Domain_Thread_Action_Message_AddRepostFromConversation::do(
				$from_source_map, $receiver_thread_map, $meta_row, $message_map_list, $client_message_id, $user_id, $text, $mention_user_id_list, $platform),
			Domain_Thread_Entity_Repost::REPOST_FROM_THREAD_TYPE, Domain_Thread_Entity_Repost::REPOST_FROM_THREAD_WITH_PARENT_TYPE =>
			Domain_Thread_Action_Message_AddRepost::do($from_source_map, $receiver_thread_map, $meta_row,
				$message_map_list, $client_message_id, $user_id, $text, $mention_user_id_list, $platform, $source_type),
			default => throw new ParseFatalException("incorrect source type"),
		};

		// подписываем пользователей, которых упомянули в треде
		Helper_Threads::attachUsersToThread($meta_row, $mention_user_id_list);

		return $repost_result;
	}

	/**
	 * Получить список просмотревших сообщение
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 * @param int    $user_role
	 * @param int    $method_version
	 *
	 * @return array
	 * @throws ActionNotAllowed
	 * @throws BusFatalException
	 * @throws CaseException
	 * @throws ControllerMethodNotFoundException
	 * @throws Domain_Thread_Exception_Message_ExpiredForGetReadParticipants
	 * @throws Domain_Thread_Exception_Message_IsNotFromThread
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws UserIsGuest
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Thread_UserNotMember
	 */
	public static function getMessageReadParticipants(int $user_id, string $message_map, int $user_role, int $method_version):array {

		// проверяем, включен ли в команде просмотр статуса для прочитанных
		$can_show_message_read_status = Domain_Company_Action_Config_GetShowMessageReadStatus::do();
		!$can_show_message_read_status && throw new ActionNotAllowed("action not allowed");

		// проверяем что сообщение из диалога
		if (!\CompassApp\Pack\Message::isFromThread($message_map)) {
			throw new Domain_Thread_Exception_Message_IsNotFromThread("Message is not from thread");
		}

		// узнаем, есть у пользователя доступ к треду
		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		$meta_row   = Helper_Threads::getMetaIfUserMember($thread_map, $user_id);

		[$_, $_, $_, $location_type] = Type_Thread_SourceParentDynamic::get($meta_row["source_parent_rel"]);

		$can_get_read_participants = true;

		// если тред находится в сингл чате, то проверять ничего не нужно - вернуть противоположного участника можно всегда
		if (!Type_Thread_SourceParentDynamic::isSubtypeOfSingle($location_type)) {

			// если запрещено смотреть участников группы - завершаем выполнение
			if (!Type_Thread_Meta_Users::isCanManage($user_id, $meta_row["users"])) {
				$can_get_read_participants = Domain_Member_Entity_Permission::get($user_id, Permission::IS_SHOW_GROUP_MEMBER_ENABLED);
			}

			// у гостя никогда нет прав получать участников группы
			if ($user_role === Member::ROLE_GUEST) {
				$can_get_read_participants = false;
			}
		}

		// до второй версии метода возвращаем ошибку прав
		if (!$can_get_read_participants && $method_version < 2) {
			throw new ActionNotAllowed("action not allowed");
		}

		// получаем прочитавших участников
		$read_participants = Domain_Thread_Action_GetMessageReadParticipants::do($meta_row, $location_type, $message_map);

		return self::_formatReadParticipants($read_participants, $can_get_read_participants);
	}

	/**
	 * Отформатировать список прочитавших
	 *
	 * @param Struct_Db_CompanyThread_MessageReadParticipant_Participant[] $read_participants
	 * @param bool                                                         $can_get_read_participants
	 *
	 * @return array
	 */
	protected static function _formatReadParticipants(array $read_participants, bool $can_get_read_participants):array {

		$formatted_read_participants = [];

		if ($can_get_read_participants) {

			// форматируем ответ
			$formatted_read_participants = array_map(
				fn(Struct_Db_CompanyThread_MessageReadParticipant_Participant $a) => Apiv2_Format::messageReadParticipant($a),
				$read_participants);
		}

		return [$formatted_read_participants, count($read_participants)];
	}
}