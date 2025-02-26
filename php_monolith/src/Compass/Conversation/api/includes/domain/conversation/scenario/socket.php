<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс обработки сценариев сокет событий
 */
class Domain_Conversation_Scenario_Socket {

	/**
	 * Добавления списка файлов в диалог
	 *
	 * @throws cs_Message_IsNotExist
	 * @throws \parseException
	 */
	public static function addThreadFileListToConversation(string $conversation_map, string $message_map, string $conversation_message_map, array $need_add_file_list):void {

		// получаем блок сообщения
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		$block_row   = Domain_Conversation_Entity_Message_Block_Get::getBlockRow($conversation_map, $conversation_message_map, $dynamic_row, true);

		// получаем сообщение из блока
		$message = Domain_Conversation_Entity_Message_Block_Message::get($conversation_message_map, $block_row);

		$insert_list = [];
		foreach ($need_add_file_list as $add_file) {

			$created_at    = Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message);
			$insert_list[] = Domain_Conversation_Entity_File_Main::createStructForFileFromThread(
				$conversation_map, $add_file["file_map"], $add_file["file_uuid"], $created_at, $message_map, $conversation_message_map, $add_file["sender_user_id"]
			);
		}
		Domain_Conversation_Entity_File_Main::addFileList($insert_list);
	}

	/**
	 * Добавления списка файлов в диалог найма и увольнения
	 */
	public static function addThreadFileListToHiringConversation(string $conversation_map, string $message_map, array $need_add_file_list, int $created_at):void {

		$insert_list = [];
		foreach ($need_add_file_list as $add_file) {

			$insert_list[] = Domain_Conversation_Entity_File_Main::createStructForFileFromThread(
				$conversation_map, $add_file["file_map"], $add_file["file_uuid"], $created_at, $message_map, "", $add_file["sender_user_id"]
			);
		}
		Domain_Conversation_Entity_File_Main::addFileList($insert_list);
	}

	/**
	 * Скрываем файлы для пользователя
	 */
	public static function hideThreadFileList(array $file_uuid_list, int $user_id):void {

		// скрываем файлы
		foreach ($file_uuid_list as $file_uuid) {
			Domain_Conversation_Entity_File_Main::doHideThreadFileForUser($file_uuid, $user_id);
		}
	}

	/**
	 * Удаляем файлы из треда
	 */
	public static function deleteThreadFileList(array $file_uuid_list):void {

		Domain_Conversation_Entity_File_Main::setDeletedList($file_uuid_list);
	}

	/**
	 * Получаем информацию о дилаогах
	 *
	 * @long
	 */
	public static function getConversationInfoList(array $conversation_key_list):array {

		$conversation_map_list = [];
		foreach ($conversation_key_list as $conversation_key) {

			try {
				$conversation_map_list[] = \CompassApp\Pack\Conversation::doDecrypt($conversation_key);
			} catch (\Exception) {

			}
		}

		// получаем мету диалогов
		$conversation_meta_list = Type_Conversation_Meta::getAll($conversation_map_list);

		$output = [];
		foreach ($conversation_meta_list as $meta_row) {

			$member_count = 0;
			foreach ($meta_row["users"] as $k => $v) {

				if (Type_Conversation_Meta_Users::isMember($k, $meta_row["users"])) {
					$member_count++;
				}
			}
			$output[] = [
				"conversation_key" => \CompassApp\Pack\Conversation::doEncrypt($meta_row["conversation_map"]),
				"name"             => $meta_row["conversation_name"],
				"member_count"     => $member_count,
				"avatar_file_map"  => $meta_row["avatar_file_map"],
			];
		}

		return $output;
	}

	/**
	 * Добавляем пользователя в группы после подтверждения заявки
	 *
	 * @long если делать короче то начинается неразбериха
	 *
	 * @throws \parseException
	 */
	public static function joinToGroupConversationList(int $inviter_user_id, int $invited_user_id, array $conversation_map_list):array|false {

		$is_not_owner_list                    = [];
		$is_not_exist_list                    = [];
		$is_not_group_list                    = [];
		$is_leaved_list                       = [];
		$is_kicked_list                       = [];
		$ok_conversation_map_list             = [];
		$already_joined_conversation_map_list = [];
		$is_no_errors                         = true;

		$inviter_member = Gateway_Bus_CompanyCache::getMember($inviter_user_id);

		$left_menu_list = Type_Conversation_LeftMenu::getList($inviter_user_id, $conversation_map_list, true);
		$meta_list      = Type_Conversation_Meta::getAll($conversation_map_list, true);
		foreach ($conversation_map_list as $conversation_map) {

			if (!isset($meta_list[$conversation_map])) {

				$is_not_exist_list[] = $conversation_map;
				$is_no_errors        = false;
				continue;
			}
			$meta_row = $meta_list[$conversation_map];

			// если приглашаемый уже участник диалога
			if (Type_Conversation_Meta_Users::isMember($invited_user_id, $meta_row["users"])) {

				$already_joined_conversation_map_list[] = $conversation_map;
				continue;
			}

			// если тип диалога не относится к группе
			if (!Type_Conversation_Meta::isSubtypeOfGroup($meta_row["type"])) {

				$is_not_group_list[] = $conversation_map;
				$is_no_errors        = false;
				continue;
			}

			// если приглашающий не является участником группы
			if (!Type_Conversation_Meta_Users::isMember($inviter_user_id, $meta_row["users"])) {

				if ($left_menu_list[$conversation_map]["leave_reason"] == Type_Conversation_LeftMenu::LEAVE_REASON_KICKED) {
					$is_kicked_list[] = $conversation_map;
				} elseif ($left_menu_list[$conversation_map]["leave_reason"] == Type_Conversation_LeftMenu::LEAVE_REASON_LEAVED) {
					$is_leaved_list[] = $conversation_map;
				}

				if (count($is_kicked_list) == 0 && count($is_leaved_list) == 0) {
					$is_not_exist_list[] = $conversation_map;
				}
				$is_no_errors = false;
				continue;
			}

			// приглашающий может пригласить пользователей в диалог?
			if (!Type_Conversation_Meta_Users::isGroupAdmin($inviter_member->user_id, $meta_row["users"])) {

				$is_not_owner_list[] = $conversation_map;
				$is_no_errors        = false;
				continue;
			}
			$ok_conversation_map_list[] = $conversation_map;
		}

		if (!$is_no_errors) {

			return [
				"ok_list"           => array_values(array_merge($ok_conversation_map_list, $already_joined_conversation_map_list)),
				"is_not_exist_list" => $is_not_exist_list,
				"is_not_owner_list" => $is_not_owner_list,
				"is_not_group_list" => $is_not_group_list,
				"is_leaved_list"    => $is_leaved_list,
				"is_kicked_list"    => $is_kicked_list,
			];
		}

		Gateway_Event_Dispatcher::dispatch(Type_Event_Conversation_JoinToGroupList::create($invited_user_id, $ok_conversation_map_list));
		return false;
	}

	/**
	 * Получить по массиву ключей список диалогов, управляемых пользователем
	 */
	public static function getManagedByMapList(int $user_id, array $conversation_map_list):array {

		$left_menu_list = Gateway_Db_CompanyConversation_UserLeftMenu::getList($user_id, $conversation_map_list);

		$conversation_meta_list = Type_Conversation_Meta::getAll(array_column($left_menu_list, "conversation_map"), true);

		// список диалогов, которые не существуют в текущей компании
		[$not_exist_in_company_conversation_map_list, $left_menu_list] = self::_makeNotExistInCompanyConversationMapList($left_menu_list, $conversation_map_list);

		// список диалогов, которые не являются групповыми
		[$not_group_conversation_map_list, $left_menu_list] = self::_makeNotGroupConversationMapList($left_menu_list, $conversation_meta_list);

		// список диалогов, которые покинул пользователь или где был исключен
		[$leaved_member_conversation_map_list, $kicked_member_conversation_map_list, $left_menu_list]
			= self::_makeLeavedKickedConversationMapList($left_menu_list);

		// раскидываем по массивам группы участника по возможности приглашения в группу
		[$can_send_invite_conversation_map_list, $cannot_send_invite_conversation_map_list]
			= self::_makeCanAndCannotSendInviteConversationMapList($left_menu_list);

		return [
			$can_send_invite_conversation_map_list,
			$cannot_send_invite_conversation_map_list,
			$leaved_member_conversation_map_list,
			$kicked_member_conversation_map_list,
			$not_exist_in_company_conversation_map_list,
			$not_group_conversation_map_list,
		];
	}

	/**
	 * Добавить пользователя в те, кто скрыл тред
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function hideThreadForUser(int $user_id, string $conversation_map, string $thread_map):void {

		$thread_rel = Gateway_Db_CompanyConversation_MessageThreadRel::getOneByThreadMap($conversation_map, $thread_map);

		$extra = Type_Conversation_ThreadRel_Extra::addUserToHideList($thread_rel->extra, $user_id);

		Gateway_Db_CompanyConversation_MessageThreadRel::set($conversation_map, $thread_rel->message_map, [
			"extra" => $extra,
		]);
	}

	/**
	 * Очистить список пользователей, скрывших тред
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function revealThread(string $conversation_map, string $thread_map):void {

		$thread_rel = Gateway_Db_CompanyConversation_MessageThreadRel::getOneByThreadMap($conversation_map, $thread_map);
		$extra      = Type_Conversation_ThreadRel_Extra::clearHideUserList($thread_rel->extra);
		$extra      = Type_Conversation_ThreadRel_Extra::setThreadHiddenForAllUsers($extra, false);

		Gateway_Db_CompanyConversation_MessageThreadRel::set($conversation_map, $thread_rel->message_map, [
			"extra" => $extra,
		]);
	}

	/**
	 * формирует список диалогов, которые не являются групповыми
	 *
	 */
	public static function _makeNotGroupConversationMapList(array $left_menu_list, array $member_conversation_meta_list):array {

		$not_group_conversation_map_list = [];

		foreach ($member_conversation_meta_list as $conversation_map => $member_conversation_meta_item) {

			if (!Type_Conversation_Meta::isSubtypeOfGroup($member_conversation_meta_item["type"])) {
				$not_group_conversation_map_list[] = $conversation_map;
			}
		}

		$updated_left_menu_list = array_filter($left_menu_list, function(array $left_menu_item) use ($not_group_conversation_map_list) {

			return !in_array($left_menu_item["conversation_map"], $not_group_conversation_map_list);
		});

		return [$not_group_conversation_map_list, $updated_left_menu_list];
	}

	/**
	 * формирует список групп, в которых пользователь был исключен или которые он покинул сам
	 *
	 */
	public static function _makeLeavedKickedConversationMapList(array $left_menu_list):array {

		// ушёл из диалога по собственному желанию
		$leaved_member_left_menu_list        = array_filter($left_menu_list, function(array $item) {

			return $item["is_leaved"] == "1" && $item["leave_reason"] == Type_Conversation_LeftMenu::LEAVE_REASON_LEAVED;
		});
		$leaved_member_conversation_map_list = array_column($leaved_member_left_menu_list, "conversation_map");

		// был исключен из диалога
		$kicked_member_left_menu_list        = array_filter($left_menu_list, function(array $item) {

			return $item["is_leaved"] == "1" && $item["leave_reason"] == Type_Conversation_LeftMenu::LEAVE_REASON_KICKED;
		});
		$kicked_member_conversation_map_list = array_column($kicked_member_left_menu_list, "conversation_map");

		$updated_left_menu_list = array_filter($left_menu_list, function(array $left_menu_item) use ($leaved_member_conversation_map_list, $kicked_member_conversation_map_list) {

			return !in_array($left_menu_item["conversation_map"], $leaved_member_conversation_map_list)
				&& !in_array($left_menu_item["conversation_map"], $kicked_member_conversation_map_list);
		});

		return [$leaved_member_conversation_map_list, $kicked_member_conversation_map_list, $updated_left_menu_list];
	}

	/**
	 * формирует список групп, которые не существуют в текущей компании
	 *
	 */
	public static function _makeNotExistInCompanyConversationMapList(array $left_menu_list, array $conversation_map_list):array {

		$conversation_map_in_company_list = array_column($left_menu_list, "conversation_map");

		$conversation_map_not_in_company_list = array_diff($conversation_map_list, $conversation_map_in_company_list);

		$updated_left_menu_list = array_filter($left_menu_list, function(array $left_menu_item) use ($conversation_map_not_in_company_list) {

			return !in_array($left_menu_item["conversation_map"], $conversation_map_not_in_company_list);
		});

		return [$conversation_map_not_in_company_list, $updated_left_menu_list];
	}

	/**
	 * формирует список групп, в которых у пользователя есть право приглашать и в которых нет
	 *
	 * @return array[]
	 */
	public static function _makeCanAndCannotSendInviteConversationMapList(array $left_menu_list):array {

		$can_send_invite_conversation_map_list    = [];
		$cannot_send_invite_conversation_map_list = [];

		foreach ($left_menu_list as $left_menu) {

			if (in_array($left_menu["role"], Type_Conversation_Meta_Users::MANAGED_ROLES) ||
				Type_Company_Default::checkIsDefaultGroupOnAddMember($left_menu["conversation_map"])
			) {

				$can_send_invite_conversation_map_list[] = $left_menu["conversation_map"];
				continue;
			}

			$cannot_send_invite_conversation_map_list[] = $left_menu["conversation_map"];
		}

		$updated_left_menu_list = array_filter($left_menu_list, function(array $left_menu_item) use ($cannot_send_invite_conversation_map_list) {

			return !in_array($left_menu_item["conversation_map"], $cannot_send_invite_conversation_map_list);
		});

		return [$can_send_invite_conversation_map_list, $cannot_send_invite_conversation_map_list, $updated_left_menu_list];
	}

	/**
	 * Пройдемся по всем диалогам и удалим оттуда пользователя
	 *
	 */
	public static function clearConversationsForUser(int $user_id, int $limit, int $offset):bool {

		return Domain_Conversation_Action_ClearConversations::run($user_id, $limit, $offset);
	}

	/**
	 * Пройдемся по всем инвайтам и пометим неактивными
	 *
	 */
	public static function clearInvitesForUser(int $user_id, int $limit, int $offset):bool {

		return Domain_Invite_Action_ClearInvites::run($user_id, $limit, $offset);
	}

	/**
	 * Проверим что у пользователя больше нет активных приглашений
	 *
	 */
	public static function checkClearInvitesForUser(int $user_id, int $limit, int $offset):bool {

		return Domain_Invite_Action_CheckClearInvites::run($user_id, $limit, $offset);
	}

	/**
	 * Проверим что пользователя больше нет в инвайтах
	 *
	 */
	public static function checkClearConversationsForUser(int $user_id, int $limit, int $offset):bool {

		return Domain_Conversation_Action_CheckClearConversations::run($user_id, $limit, $offset);
	}

	/**
	 * Возвращает список связей заявок на наем и сообщений в чате наемов.
	 */
	public static function getHiringRequestMessageRels(int $date_from, int ...$hiring_request_id_list):array {

		[$hiring_message_rel_list,] = Domain_Conversation_Action_Lazy_GetHiringConversationRequestMessageRelList::run($date_from, $hiring_request_id_list, []);
		return $hiring_message_rel_list;
	}

	/**
	 * Возвращает список связей заявок на увольнение и сообщений в чате наемов.
	 */
	public static function getDismissalRequestMessageRels(int $date_from, int ...$dismissal_request_id_list):array {

		[, $dismissal_message_rel_list] = Domain_Conversation_Action_Lazy_GetHiringConversationRequestMessageRelList::run($date_from, [], $dismissal_request_id_list);
		return $dismissal_message_rel_list;
	}

	/**
	 * создаём Напоминания для сообщения диалога
	 *
	 * @long
	 * @throws Domain_Conversation_Exception_Message_NotAllowForRemind
	 * @throws Domain_Conversation_Exception_Message_NotAllowForUser
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws Domain_Remind_Exception_AlreadyExist
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Message_IsTooLong
	 * @throws cs_UserIsNotMember
	 */
	public static function createRemindOnMessage(int $user_id, string $message_map, int $remind_at, string $comment):int {

		// проверяем корректность времени напоминания
		if ($remind_at <= time()) {
			throw new ParamException("incorrect remind_at");
		}

		// если сообщение не из диалога
		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParamException("the message is not from conversation");
		}

		// фильтруем коммент
		$comment = Type_Api_Filter::replaceEmojiWithShortName($comment);
		if (mb_strlen($comment) > Type_Api_Filter::MAX_REMIND_TEXT_LENGTH) {
			throw new cs_Message_IsTooLong("comment for remind is too long");
		}
		$comment = Type_Api_Filter::sanitizeMessageText($comment);

		// получаем мету диалога
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);

		// если наш пользователь не является участником диалога, то ругаемся
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			throw new cs_UserIsNotMember("not member of conversation");
		}

		// проверяем, что разрешено действие для данного типа диалога
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::REMIND_CREATE_FROM_CONVERSATION);

		// проверяем, может ли пользователь взаимодействовать с диалогом
		Helper_Conversations::checkIsAllowed($meta_row["conversation_map"], $meta_row, $user_id);

		// создаём Напоминание для сообщения диалога
		$remind = Domain_Conversation_Action_Message_AddRemind::do(
			$message_map, $conversation_map, $meta_row, $comment, $remind_at, $user_id, Domain_Remind_Entity_Remind::THREAD_PARENT_MESSAGE_TYPE
		);

		return $remind->remind_id;
	}

	/**
	 * отправляем сообщение-Напоминание в чат
	 *
	 * @long
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Message_IsTooLong
	 */
	public static function sendRemindMessage(string $message_map, string $comment):void {

		$sender_user_id = REMIND_BOT_USER_ID; // отправитель сообщения-Напоминания является бот Напоминание

		// получаем мету диалога
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);

		// проверяем, что разрешено действие для данного типа диалога
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::ADD_MESSAGE_FROM_CONVERSATION);

		// фильтруем комментарий для Напоминания
		$comment = Domain_Remind_Action_FilteredComment::do($comment);

		// получаем данные Напоминания из сообщения-оригинала
		[$original_message, $remind_id] = self::_getRemindData($message_map, $conversation_map);

		// получаем напоминание
		$remind = Gateway_Db_CompanyData_RemindList::getOne($remind_id);

		// получаем время очистки диалога у всех
		$clear_until_for_all = Type_Conversation_Meta_Extra::getConversationClearUntilForAll($meta_row["extra"]);

		// проверяем на возможность отправки
		Domain_Conversation_Action_Message_CheckForSendRemind::do($original_message, $sender_user_id, $remind->created_at, $clear_until_for_all);

		// получаем упомянутых из комментария к Напоминанию
		$mention_user_id_list = Helper_Conversations::getMentionUserIdListFromText($meta_row, $comment);

		// получаем отправителя оригинала-сообщения
		$recipient_message_sender_id = Type_Conversation_Message_Main::getHandler($original_message)::getSenderUserId($original_message);

		// подготавливаем сообщение к напоминанию
		$message = Type_Message_Utils::prepareMessageForRepostQuoteRemind($original_message);

		// формируем структуру сообщения-Напоминания
		$remind_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemBotRemind(
			$sender_user_id, $comment, generateUUID(), [$message], $recipient_message_sender_id
		);

		// добавляем упомянутых к сообщению
		$remind_message = Type_Conversation_Message_Main::getHandler($remind_message)::addMentionUserIdList($remind_message, $mention_user_id_list);

		// отправляем сообщение-Напоминание в чат
		Helper_Conversations::addMessage(
			$meta_row["conversation_map"], $remind_message, $meta_row["users"], $meta_row["type"], $meta_row["conversation_name"], $meta_row["extra"]
		);

		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getOne($conversation_map);

		// отправляем ws-событие о том, что Напоминание исчезло
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);
		Gateway_Bus_Sender::remindDeleted($remind_id, $message_map, $conversation_map, $dynamic->messages_updated_version, $talking_user_list);

		// форматируем текст для интеркома
		$intercom_message_text = self::_prepareSendRemindMessageToIntercom($message, $comment);

		// отправляем сообщение в интерком о том что создали напоминание
		Helper_Conversations::addMessageToIntercomQueueByText($intercom_message_text, $message, $meta_row["type"]);
	}

	/**
	 * получаем данные Напоминания из сообщения-оригинала
	 *
	 * @throws ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	protected static function _getRemindData(string $message_map, string $conversation_map):array {

		// получаем сообщение-оригинал для Напоминания
		$block_id         = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
		$block_row        = Gateway_Db_CompanyConversation_MessageBlock::getOne($conversation_map, $block_id);
		$original_message = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		$remind_id = Type_Conversation_Message_Main::getHandler($original_message)::getRemindId($original_message);

		// удаляем из структуры данные Напоминания
		$original_message = Type_Conversation_Message_Main::getHandler($original_message)::removeRemindData($original_message);

		return [$original_message, $remind_id];
	}

	/**
	 * Формируем нужный формат сообщения для отправки в интерком
	 */
	protected static function _prepareSendRemindMessageToIntercom(array $message, string $comment):string {

		$message_text = Type_Conversation_Message_Main::getHandler($message)::getText($message);
		$system_info  = Gateway_Socket_Intercom::SYSTEM_SEND_REMIND_MESSAGE;

		$comment_for_remind = mb_strlen($comment) > 0 ? "\n\n<b>Комментарий к напоминанию</b>\n" : "";

		return "{$system_info}\n\n<b>Сообщение</b>\n{$message_text}{$comment_for_remind}{$comment}";
	}

	/**
	 * актуализируем данные Напоминания для сообщения-оригинала
	 *
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 */
	public static function actualizeTestRemindForMessage(string $message_map):void {

		assertTestServer();

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// получаем сообщение из блока
		$block_id         = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
		$block_row        = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);
		$original_message = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		// устанавливаем время на текущее
		$message = Type_Conversation_Message_Main::getHandler($original_message)::setRemindAt($original_message, time());

		// обновляем сообщение в блоке
		Domain_Conversation_Entity_Message_Block_Main::updateDataInMessageBlock($conversation_map, $message_map, $block_row, $block_id, $message);

		Gateway_Db_CompanyConversation_Main::commitTransaction();

		// дополнительно отправляем запрос, чтобы почистить данные сообщения в кэше для тредов
		Gateway_Socket_Thread::doClearParentMessageCache($message_map);
	}

	/**
	 * Прикрепить превью к чату
	 *
	 * @param int    $user_id
	 * @param string $thread_message_map
	 * @param string $conversation_message_map
	 * @param string $preview_map
	 * @param int    $message_created_at
	 * @param array  $link_list
	 *
	 * @return void
	 * @throws ReturnFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws cs_Message_IsNotExist
	 * @long создается структура для треда
	 */
	public static function attachPreview(int $user_id, string $thread_message_map, string $conversation_message_map, string $preview_map,
							 int $message_created_at, array $link_list):void {

		try {

			Gateway_Db_CompanyConversation_Main::beginTransaction();

			$preview_row = Gateway_Db_CompanyConversation_ConversationPreview::getForUpdate(
				Domain_Conversation_Entity_Preview_Main::PARENT_TYPE_THREAD, $thread_message_map);

			Domain_Conversation_Entity_Preview_Main::updatePreviewAndLinkList(
				$preview_row->parent_type, $preview_row->parent_message_map, $preview_map, $link_list);

			Gateway_Db_CompanyConversation_Main::commitTransaction();
		} catch (RowNotFoundException) {

			Gateway_Db_CompanyConversation_Main::rollback();

			// получаем блок сообщения
			$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($conversation_message_map);
			$dynamic_row      = Domain_Conversation_Entity_Dynamic::get(\CompassApp\Pack\Message\Conversation::getConversationMap($conversation_message_map));
			$block_row        = Domain_Conversation_Entity_Message_Block_Get::getBlockRow(
				$conversation_map, $conversation_message_map, $dynamic_row, true);

			// получаем сообщение из блока
			$message = Domain_Conversation_Entity_Message_Block_Message::get($conversation_message_map, $block_row);

			// нет записи - создаем новую
			$conversation_preview = Domain_Conversation_Entity_Preview_Main::createStructForThread(
				$user_id,
				\CompassApp\Pack\Message\Conversation::getConversationMap($conversation_message_map),
				$thread_message_map,
				$conversation_message_map,
				$preview_map,
				Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message),
				$message_created_at,
				$link_list
			);

			Domain_Conversation_Entity_Preview_Main::add($conversation_preview);
		}
	}

	/**
	 * Удалить превью из чата
	 *
	 * @param array $thread_message_map_list
	 *
	 * @return void
	 */
	public static function deletePreviewList(array $thread_message_map_list):void {

		Domain_Conversation_Entity_Preview_Main::setDeletedList(Domain_Conversation_Entity_Preview_Main::PARENT_TYPE_THREAD, $thread_message_map_list);
	}

	/**
	 * Прячем превью в чате
	 *
	 * @param int   $user_id
	 * @param array $thread_message_map_list
	 *
	 * @return void
	 * @throws RowNotFoundException
	 * @throws ReturnFatalException
	 */
	public static function hidePreviewList(int $user_id, array $thread_message_map_list):void {

		Domain_Conversation_Entity_Preview_Main::hideList(
			$user_id, Domain_Conversation_Entity_Preview_Main::PARENT_TYPE_THREAD, $thread_message_map_list);
	}

	/**
	 * обновляем временную метку и версию обновления тредов
	 */
	public static function updateThreadsUpdatedData(string $conversation_map):Struct_Db_CompanyConversation_ConversationDynamic {

		Gateway_Db_CompanyConversation_ConversationDynamic::beginTransaction();

		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getForUpdate($conversation_map);

		$dynamic->threads_updated_at      = time();
		$dynamic->threads_updated_version = $dynamic->threads_updated_version + 1;
		$dynamic->updated_at              = time();

		$set = [
			"updated_at"              => $dynamic->updated_at,
			"threads_updated_at"      => $dynamic->threads_updated_at,
			"threads_updated_version" => $dynamic->threads_updated_version,
		];
		Gateway_Db_CompanyConversation_ConversationDynamic::set($conversation_map, $set);

		Gateway_Db_CompanyConversation_ConversationDynamic::commitTransaction();

		return $dynamic;
	}

	/**
	 * Получаем данные по диалогам для карточки
	 */
	public static function getConversationCardList(int $user_id, int $opponent_user_id):array {

		$heroes_conversation = [];
		$single_conversation = [];

		// получаем чат heroes
		$conversation_type = Type_UserConversation_UserConversationRel::getTypeByName("public_heroes");
		try {

			$user_conversation_rel_obj               = Type_UserConversation_UserConversationRel::get($user_id, $conversation_type);
			$heroes_conversation["conversation_key"] = \CompassApp\Pack\Conversation::doEncrypt($user_conversation_rel_obj->conversation_map);
		} catch (\cs_RowIsEmpty) {
			$heroes_conversation["conversation_key"] = "";
		}

		$single_conversation["conversation_key"] = "";
		$single_conversation["is_muted"]         = 0;

		// проверяем существование личного диалога
		$conversation_map = Type_Conversation_Single::getMapByUsers($user_id, $opponent_user_id);

		if ($conversation_map === false) {
			return [$single_conversation, $heroes_conversation];
		}

		// получаем запись из лм
		$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $conversation_map);

		if (count($left_menu_row) > 0) {

			$single_conversation["conversation_key"] = \CompassApp\Pack\Conversation::doEncrypt($left_menu_row["conversation_map"]);
			$single_conversation["is_muted"]         = $left_menu_row["is_muted"];
		}

		return [$single_conversation, $heroes_conversation];
	}

	/**
	 * Отправляем сообщение об успешной авторизации устройства в чат поддержки
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 */
	public static function sendDeviceLoginSuccess(int $user_id, string $login_type, string $device_name, string $app_version, string $server_version, string $locale):void {

		if (ServerProvider::isOnPremise() || !defined(__NAMESPACE__ . "\IS_ALLOW_SEND_DEVICE_LOGIN_SUCCESS") || !IS_ALLOW_SEND_DEVICE_LOGIN_SUCCESS) {
			return;
		}

		try {

			$left_menu_row    = Type_Conversation_LeftMenu::getSupportGroupByUser($user_id);
			$conversation_map = $left_menu_row["conversation_map"];
		} catch (RowNotFoundException) {
			return;
		}

		Type_Conversation_Support::sendDeviceLoginSuccess($conversation_map, $login_type, $device_name, $app_version, $server_version, $locale);
	}
}
