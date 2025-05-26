<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Сценарии тредов для Socket API
 */
class Domain_Thread_Scenario_Socket {

	/**
	 * действия в тредах при создании заявки найма и увольнения
	 *
	 * @return array
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	public static function onCreateDismissalRequest(int $user_id, int $request_type, string $user_message_text, string $thread_map, string $platform = Type_Thread_Message_Handler_Default::WITHOUT_PLATFORM, bool $is_dismissal_self = false):array {

		// если тип заявки не поддерживается
		if (!Type_Thread_Utils::isDismissalRequestParent($request_type)) {
			throw new ParseFatalException("Dont allow this type of request");
		}
		$thread_meta_row = Type_Thread_Meta::getOne($thread_map);

		// формируем системное сообщение, дефолтное при создании заявки
		if ($is_dismissal_self) {
			$message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemCreateDismissalRequestSelf($user_id);
		} else {
			$message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemCreateDismissalRequest($user_id);
		}

		// добавляем также сообщение от лица пользователя, если тот добавил комментарий к заявке
		if (!isEmptyString($user_message_text)) {
			$message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::makeText($user_id, $user_message_text, generateUUID(), [], $platform);
		}

		// добавляем в тред сообщения
		Domain_Thread_Action_Message_AddList::do($thread_meta_row["thread_map"], $thread_meta_row, $message_list);

		return $thread_meta_row;
	}

	/**
	 * создаем системные сообщения в треде заявки на увольнение
	 *
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	public static function addSystemMessageToDismissalRequestThread(int $creator_user_id, int $dismissal_user_id, string $thread_map):void {

		// создаем тред у заявки на увольнение, плюс сразу пишем первое системное сообщение
		$thread_meta_row = Domain_Thread_Scenario_Socket::onCreateDismissalRequest($creator_user_id, PARENT_ENTITY_TYPE_DISMISSAL_REQUEST, "", $thread_map);

		// добавляем системные сообщения в новосозданный тред
		$message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemApproveDismissalRequest($creator_user_id);
		$message_list[] = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemDismissalRequestOnUserLeftCompany($dismissal_user_id);
		Domain_Thread_Action_Message_AddList::do($thread_meta_row["thread_map"], $thread_meta_row, $message_list, not_send_ws_event_user_list: [$dismissal_user_id]);
	}

	/**
	 * Пройдемся по всем тредам и отпишем пользователя
	 *
	 */
	public static function clearThreadsForUser(int $user_id, int $limit, int $offset):bool {

		return Domain_Thread_Action_ClearThreads::run($user_id, $limit, $offset);
	}

	/**
	 * Проверим что пользователя больше нет в тредах
	 *
	 */
	public static function checkClearThreadsForUser(int $user_id, int $limit, int $offset):bool {

		return Domain_Thread_Action_CheckClearThreads::run($user_id, $limit, $offset);
	}

	/**
	 * действия в тредах при создании заявки найма и увольнения
	 *
	 * @throws \parseException
	 */
	public static function clearConversationForUserIdList(array $user_id_list, string $conversation_map):void {

		// делим на чанки по 100 пользователей и пушим событие через go_event
		$chunk_user_id_list = array_chunk($user_id_list, 100);
		foreach ($chunk_user_id_list as $user_id_list) {
			Gateway_Event_Dispatcher::dispatch(Type_Event_Thread_OnClearConversationForUserList::create($conversation_map, $user_id_list));
		}
	}

	/**
	 * получаем список тредов
	 *
	 * @throws \parseException
	 */
	public static function getThreadListForFeed(int $user_id, array $thread_map_list):array {

		// пробуем получить данные о метах тредов
		$data = Helper_Threads::getMetaListIfUserMember($thread_map_list, $user_id);

		$thread_meta_list           = $data["allowed_meta_list"];
		$not_access_thread_map_list = $data["not_allowed_thread_map_list"];

		// отправляем задачу на отписывание от тредов
		Type_Phphooker_Main::doUnfollowThreadList($not_access_thread_map_list, $user_id);

		// получаем только доступные треды
		$allowed_thread_map_list = array_column($thread_meta_list, "thread_map");

		// получаем конкретные записи из меню, игнорируя скрытые
		$menu_list = Type_Thread_Menu::getMenuItems($user_id, $allowed_thread_map_list);

		// формируем ответ
		$prepared_meta_row_list = [];
		foreach ($thread_meta_list as $item) {

			// приводим сущность threads под формат frontend
			$prepared_meta_row_list[] = Type_Thread_Utils::prepareThreadMetaForFormat($item, $user_id);
		}

		$prepared_thread_menu_list = [];
		foreach ($menu_list as $item) {

			// форматируем сущность thread_menu
			$prepared_thread_menu_list[] = Type_Thread_Utils::prepareThreadMenuForFormat($item);
		}
		return [$prepared_meta_row_list, $prepared_thread_menu_list];
	}

	/**
	 * получаем список тредов для батчинг
	 *
	 * @throws \parseException
	 */
	public static function getThreadListForBatchingFeed(int $user_id, array $thread_map_list, array $conversation_dynamic_by_conversation_map, array $conversation_meta_by_conversation_map):array {

		[$meta_list_by_source_parent_rel, $meta_list_for_hire_requests] = Helper_Threads::getMetaListGroupedByParentMetaMap($thread_map_list);

		// фильтруем доступные меты тредов для пользователя
		$allowed_meta_list = [];
		foreach ($meta_list_by_source_parent_rel as $parent_map => $meta_list) {

			$conversation_dynamic      = $conversation_dynamic_by_conversation_map[$parent_map];
			$conversation_meta         = $conversation_meta_by_conversation_map[$parent_map];
			$source_parent_rel_dynamic = Domain_Thread_Action_PrepareConversationDynamic::do($conversation_dynamic, $conversation_meta);

			$allowed_meta_list = array_merge(
				$allowed_meta_list, Domain_Thread_Action_FilterMetaListIfAccessParent::do($user_id, $meta_list, $source_parent_rel_dynamic)
			);
		}

		// получаем меты тредов, закрепленные за заявками найма/увольнения
		if (count($meta_list_for_hire_requests) > 0) {

			[$meta_list_for_hire_requests] = Domain_Thread_Action_GetMetaForHireRequests::do($user_id, $meta_list_for_hire_requests);
			$allowed_meta_list = array_merge($allowed_meta_list, $meta_list_for_hire_requests);
		}

		// получаем конкретные записи из меню
		$menu_list = Type_Thread_Menu::getMenuItems($user_id, array_column($allowed_meta_list, "thread_map"));

		// формируем ответ
		$prepared_meta_row_list = [];
		foreach ($allowed_meta_list as $item) {

			// приводим сущность threads под формат frontend
			$prepared_meta_row_list[] = Type_Thread_Utils::prepareThreadMetaForFormat($item, $user_id);
		}

		$prepared_thread_menu_list = [];
		foreach ($menu_list as $item) {

			// форматируем сущность thread_menu
			$prepared_thread_menu_list[] = Type_Thread_Utils::prepareThreadMenuForFormat($item);
		}
		return [$prepared_meta_row_list, $prepared_thread_menu_list];
	}

	/**
	 * отправляем сообщение-Напоминание в тред
	 *
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \busException
	 * @throws \parseException
	 * @throws cs_ConversationIsLocked
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Message_IsTooLong
	 * @throws cs_ParentMessage_IsDeleted
	 * @throws cs_ThreadIsReadOnly
	 * @throws cs_Thread_ParentEntityNotFound
	 */
	public static function sendRemindMessage(string $message_map, int $creator_user_id, string $comment, int $remind_type):void {

		$sender_user_id = REMIND_BOT_USER_ID; // отправителем сообщения-Напоминания является бот Напоминание

		// фильтруем коммент для Напоминания
		$comment = Domain_Remind_Action_FilteredComment::do($comment);

		// получаем данные для отправки Напоминания
		[$thread_meta_row, $original_message] = self::_getMessageForSendRemind($message_map, $remind_type, $sender_user_id, $creator_user_id);

		// проверяем на возможность отправки
		Domain_Thread_Action_Message_CheckForSendRemind::do($original_message, $sender_user_id);

		// достаём всех упомянутых из комментария
		$mention_user_id_list = Helper_Threads::getMentionUserIdListFromText($thread_meta_row, $comment);

		// отдельно подписываем создателя напоминания
		// не добавляем в массив $mention_user_id_list а делаем отдельный чтобы не добавить его ниже в сообщение как упомянутого
		$need_attach_to_thread_user_id_list   = $mention_user_id_list;
		$need_attach_to_thread_user_id_list[] = $creator_user_id;
		Helper_Threads::attachUsersToThread($thread_meta_row, $need_attach_to_thread_user_id_list);

		// получаем отправителя оригинала-сообщения
		$recipient_message_sender_id = Type_Thread_Message_Main::getHandler($original_message)::getSenderUserId($original_message);

		// подготавливаем сообщение к напоминанию
		$message = Helper_Threads::prepareMessageForQuoteRemind($original_message);

		// формируем сообщение-Напоминание
		$remind_message = Type_Thread_Message_Main::getLastVersionHandler()::makeSystemBotRemind(
			$sender_user_id, $comment, generateUUID(), [$message], $recipient_message_sender_id, $creator_user_id
		);

		// добавляем упомянутых к сообщению
		$remind_message = Type_Thread_Message_Main::getHandler($remind_message)::addMentionUserIdList($remind_message, $mention_user_id_list);

		// отправляем сообщение-Напоминание в тред
		Domain_Thread_Action_Message_AddList::do($thread_meta_row["thread_map"], $thread_meta_row, [$remind_message]);
	}

	/**
	 * получаем сообщения для дальнейшей отправки Напоминания
	 *
	 * @long
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \busException
	 * @throws \parseException
	 * @throws cs_ConversationIsLocked
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Message_IsDeleted
	 * @throws cs_ParentMessage_IsDeleted
	 * @throws cs_Thread_ParentEntityNotFound
	 */
	protected static function _getMessageForSendRemind(string $message_map, int $remind_type, int $sender_user_id, int $creator_user_id):array {

		// если Напоминание было создано на родительском сообщении с отправкой в тред,
		// то первым делом проверяем наличие треда, и создаём если тот отсутствует
		if ($remind_type == Domain_Remind_Entity_Remind::THREAD_PARENT_MESSAGE_TYPE) {

			// создаём тред у сообщения
			$thread_meta_row = Domain_Thread_Action_AddToConversationMessage::do($sender_user_id, $message_map);

			// получаем родительское сообщение
			try {
				$response = Gateway_Socket_Conversation::getMessageDataForSendRemind($sender_user_id, $message_map);
			} catch (Gateway_Socket_Exception_Conversation_MessageHaveNotAccess) {
				throw new ParamException("User not have permissions for repost this message");
			} catch (Gateway_Socket_Exception_Conversation_IsNotAllowed) {
				throw new ParamException("Message is not exist");
			}

			if ($response["message_data"]["message"]["type"] == CONVERSATION_MESSAGE_TYPE_DELETED) {
				throw new cs_ParentMessage_IsDeleted("Parent message was deleted");
			}

			// переводим родительское сообщение из диалога в формат сообщения треда
			$parent_message   = $response["message_data"]["message"];
			$original_message = Type_Thread_Message_Main::getLastVersionHandler()::makeStructureForConversationMessage($parent_message);

			// так как сообщение из диалога, то выставляем индекс
			$original_message["thread_message_index"] = 0;
		} else {

			$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);

			// проверяем, может создатель Напоминания уже не имеет доступа к треду
			try {
				$thread_meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $creator_user_id);
			} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess|cs_Message_IsDeleted|cs_Conversation_IsBlockedOrDisabled) {

				Domain_Thread_Action_Follower_Unfollow::do($creator_user_id, $thread_map, true);

				// далее получаем мету треда без проверки доступов
				$thread_meta_row = Type_Thread_Meta::getOne($thread_map);
			}

			// получаем сообщение-оригинал для Напоминания
			$block_id         = \CompassApp\Pack\Message\Thread::getBlockId($message_map);
			$block_row        = Gateway_Db_CompanyThread_MessageBlock::getOne($thread_map, $block_id);
			$original_message = Type_Thread_Message_Block::getMessage($message_map, $block_row);

			$remind_id = Type_Thread_Message_Main::getHandler($original_message)::getRemindId($original_message);

			// удаляем данные Напоминания, чтобы получить оригинальное сообщение
			$original_message = Type_Thread_Message_Main::getHandler($original_message)::removeRemindData($original_message);

			// отправляем ws-событие об удалении Напоминании
			$talking_user_list = Type_Thread_Meta_Users::getTalkingUserList($thread_meta_row["users"]);
			Gateway_Bus_Sender::remindDeleted($remind_id, $message_map, $thread_map, $talking_user_list);
		}

		return [$thread_meta_row, $original_message];
	}

	/**
	 * актуализируем данные Напоминания для сообщения-оригинала
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \parseException
	 * @throws ParseFatalException
	 */
	public static function actualizeTestRemindForMessage(string $message_map):void {

		assertTestServer();

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);

		Gateway_Db_CompanyThread_Main::beginTransaction();

		// получаем сообщение из блока
		$block_id         = \CompassApp\Pack\Message\Thread::getBlockId($message_map);
		$block_row        = Gateway_Db_CompanyThread_MessageBlock::getForUpdate($thread_map, $block_id);
		$original_message = Type_Thread_Message_Block::getMessage($message_map, $block_row);

		// устанавливаем время на текущее
		$message = Type_Thread_Message_Main::getHandler($original_message)::setRemindAt($original_message, time());

		// обновляем сообщение в блоке
		$block_row["data"][$message_map] = $message;
		Gateway_Db_CompanyThread_MessageBlock::set($thread_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		Gateway_Db_CompanyThread_Main::commitTransaction();
	}

	/**
	 * Проверяем есть ли у пользователя доступ к треду
	 *
	 * @throws ParamException
	 */
	public static function checkIsUserMember(int $user_id, string $thread_key):bool {

		try {
			$thread_map = \CompassApp\Pack\Thread::tryDecrypt($thread_key);
		} catch (\Exception) {
			return false;
		}

		try {

			Helper_Threads::getMetaIfUserMember($thread_map, $user_id);
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess|cs_Message_IsDeleted|cs_Conversation_IsBlockedOrDisabled) {
			return false;
		}

		return true;
	}
}