<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для сокет методов класса threads
 */
class Socket_Threads extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"clearConversationForUserIdList",
		"doFollowUserListOnThread",
		"setThreadAsUnfollow",
		"setRepostRelDeleted",
		"setMuteFlag",
		"setThreadIsReadOnly",
		"setParentMessageIsDeleted",
		"setParentMessageIsDeletedByThreadMapList",
		"setParentMessageIsHiddenOrUnhiddenByThreadMapList",
		"doClearMetaThreadCache",
		"doClearParentMessageCache",
		"doClearParentMessageListCache",
		"doUnfollowThreadListIfRoleChangeToEmployee",
		"addSystemMessageOnHireRequestStatusChanged",
		"clearThreadsForUser",
		"checkClearThreadsForUser",
		"addSystemMessageToDismissalRequestThread",
		"getThreadList",
		"doUnfollowThreadList",
		"doUnfollowThreadListByConversationMap",
		"getThreadMenuForMigration",
		"getThreadListForFeed",
		"getThreadListForBatchingFeed",
		"addThreadForHiringRequest",
		"addThreadForDismissalRequest",
		"sendRemindMessage",
		"actualizeTestRemindForMessage",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * обновляем user_clear_info у диалога для списка пользователей
	 *
	 * @throws \parseException
	 * @throws ParamException
	 */
	public function clearConversationForUserIdList():array {

		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");
		$user_id_list     = $this->post(\Formatter::TYPE_ARRAY_INT, "user_id_list");

		Domain_Thread_Scenario_Socket::clearConversationForUserIdList($user_id_list, $conversation_map);

		return $this->ok();
	}

	// подписать пользователей на тред
	public function doFollowUserListOnThread():array {

		$thread_map   = $this->post("?s", "thread_map");
		$parent_rel   = $this->post("?a", "parent_rel");
		$user_id_list = $this->post("?ai", "user_id_list");

		// подписываем пользователя на тред
		Type_Thread_Menu::setFollowUserList($user_id_list, $thread_map, $parent_rel);

		return $this->ok();
	}

	// отписаться от треда
	public function setThreadAsUnfollow():array {

		$thread_map = $this->post("?s", "thread_map");

		// убираем пользователя из всех списков
		Type_Thread_Followers::doClearFollowUser($this->user_id, $thread_map);

		// отписываем пользователя, помечаем тред в thread_menu скрытым и отписанным
		Type_Thread_Menu::setUnfollow($this->user_id, $thread_map, false);

		// получаем total_unread_count
		$total_unread_count = Domain_Thread_Action_GetTotalUnreadCount::do($this->user_id);

		return $this->ok([
			"threads_unread_count"  => (int) $total_unread_count["threads_unread_count"],
			"messages_unread_count" => (int) $total_unread_count["messages_unread_count"],
		]);
	}

	// пометить удаленной запись с историей о репосте в диалог
	public function setRepostRelDeleted():array {

		$thread_map  = $this->post("?s", "thread_map");
		$message_map = $this->post("?s", "message_map");

		// обновляем запись
		Type_Thread_RepostRel::setMessageDeleted($thread_map, $message_map);

		return $this->ok();
	}

	// метод для изменения поля is_muted в тред меню пользователя
	public function setMuteFlag():array {

		// получаем параметры из post_data
		$thread_map = $this->post("?s", "thread_map");
		$is_muted   = $this->post("?i", "is_muted");

		// проверяем is_muted
		if ($is_muted != 0 && $is_muted != 1) {
			throw new ParamException(__METHOD__ . ": is_muted has wrong value");
		}

		// переводим is_muted в bool чтобы передать в аргументы модели
		$is_muted = $is_muted == 1;

		// изменяем поле is_muted в левом меню пользователя
		Type_Thread_Menu::setIsMuted($this->user_id, $thread_map, $is_muted);

		// возвращаем успешный ответ
		return $this->ok();
	}

	// пометить тред доступным только для чтения(т.к родительское сообщение удалено)
	public function setThreadIsReadOnly():array {

		$thread_map  = $this->post("?s", "thread_map");
		$is_readonly = $this->post("?i", "is_readonly");

		// проверяем is_readonly
		if ($is_readonly != 0 && $is_readonly != 1) {
			throw new ParamException(__METHOD__ . ": is_readonly has wrong value");
		}
		$is_readonly = $is_readonly == 1;

		// обновляем запись
		Type_Thread_Meta::setIsReadOnly($thread_map, $is_readonly);

		return $this->ok();
	}

	// устанавливаем родительское сообщение удаленным
	public function setParentMessageIsDeleted():array {

		$thread_map = $this->post("?s", "thread_map");

		// помечаем родительское сообщение удаленный
		Type_Thread_Meta::setParentRelIsDeleted($thread_map);

		return $this->ok();
	}

	// устанавливаем родительское сообщение удаленным
	public function setParentMessageIsDeletedByThreadMapList():array {

		$thread_map_list = $this->post("?a", "thread_map_list");

		// помечаем родительское сообщение удаленный
		foreach ($thread_map_list as $v) {
			Type_Thread_Meta::setParentRelIsDeleted($v);
		}

		return $this->ok();
	}

	// устанавливаем родительское сообщение удаленным
	public function setParentMessageIsHiddenOrUnhiddenByThreadMapList():array {

		$thread_map_list = $this->post("?a", "thread_map_list");
		$hide_or_show    = $this->post("?i", "need_to_hide_parent_thread");

		// проверяем, что пришло корректное значение need_to_hide_parent_thread
		if ($hide_or_show !== 0 && $hide_or_show !== 1) {
			throw new ParamException(__METHOD__ . ": need_to_hide_parent_thread has wrong value");
		}

		// помечаем родительское сообщение скрытым
		foreach ($thread_map_list as $v) {
			Type_Thread_Meta::setParentRelIsHiddenOrUnhiddenForUser($v, $hide_or_show, $this->user_id);
		}

		return $this->ok();
	}

	// чистим в треде meta cache
	public function doClearMetaThreadCache():array {

		$source_parent_map = $this->post("?s", "source_parent_map");

		// очищаем кэш
		Type_Thread_Rel_Meta::clearCache($source_parent_map);
		return $this->ok();
	}

	// чистим кэш родительского сообщения треда
	public function doClearParentMessageCache():array {

		$parent_message_map = $this->post("?s", "parent_message_map");

		// очищаем кэш
		Type_Thread_Rel_Cache::clear($parent_message_map);
		return $this->ok();
	}

	// чистим кэш родительского сообщения тредов
	public function doClearParentMessageListCache():array {

		$parent_message_map_list = $this->post("?a", "parent_message_map_list");

		// очищаем кэш
		foreach ($parent_message_map_list as $v) {
			Type_Thread_Rel_Cache::clear($v);
		}
		return $this->ok();
	}

	/**
	 * отписываем от всех тредов при смене роли пользователя на обычного сотрудника
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doUnfollowThreadListIfRoleChangeToEmployee():array {

		$source_parent_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");

		// отписываем пользователя от тредов в чате Найма и увольнения
		Helper_Threads::unfollowThreadListIfRoleChangeToEmployee($this->user_id, $source_parent_map);

		return $this->ok();
	}

	/**
	 * добавление системного сообщения
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	public function addSystemMessageOnHireRequestStatusChanged():array {

		$request_type      = $this->post(\Formatter::TYPE_STRING, "request_type");
		$thread_map        = $this->post(\Formatter::TYPE_STRING, "thread_map");
		$new_status        = $this->post(\Formatter::TYPE_STRING, "new_status");
		$candidate_user_id = $this->post(\Formatter::TYPE_INT, "candidate_user_id");
		$candidate_info    = $this->post(\Formatter::TYPE_ARRAY, "candidate_info", []);

		try {
			$this->_addSystemMessageOnHireRequestStatusChanged($request_type, $thread_map, $new_status, $candidate_user_id, $candidate_info);
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParseFatalException("empty system message for add");
		}

		return $this->ok();
	}

	/**
	 * добавляем системное сообщение при смене статуса заявки
	 *
	 * @throws Domain_Thread_Exception_Message_ListIsEmpty
	 * @throws ParamException
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	protected function _addSystemMessageOnHireRequestStatusChanged(string $request_type, string $thread_map, string $new_status, int $candidate_user_id, array $candidate_info):void {

		switch ($request_type) {

			case "hiring_request":
				Domain_Thread_Action_OnHiringRequestStatusChanged::do($thread_map, $new_status, $this->user_id, $candidate_user_id, $candidate_info);
				break;

			case "dismissal_request":
				Domain_Thread_Action_OnDismissalRequestStatusChanged::do($thread_map, $new_status, $this->user_id, $candidate_user_id);
				break;
		}
	}

	/**
	 * Метод для отписки пользователя от тредов
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function clearThreadsForUser():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$limit   = $this->post(\Formatter::TYPE_INT, "limit", 500);
		$offset  = $this->post(\Formatter::TYPE_INT, "offset", 0);

		$is_complete = Domain_Thread_Scenario_Socket::clearThreadsForUser($user_id, $limit, $offset);
		return $this->ok([
			"is_complete" => (int) ($is_complete ? 1 : 0),
		]);
	}

	/**
	 * Проверяем что пользователь не подписан на треды
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function checkClearThreadsForUser():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$limit   = $this->post(\Formatter::TYPE_INT, "limit", 500);
		$offset  = $this->post(\Formatter::TYPE_INT, "offset", 0);

		$is_cleared = Domain_Thread_Scenario_Socket::checkClearThreadsForUser($user_id, $limit, $offset);
		return $this->ok([
			"is_cleared" => (int) $is_cleared,
		]);
	}

	/**
	 * создаем системные сообщения в треде заявки на увольнение
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	public function addSystemMessageToDismissalRequestThread():array {

		$creator_user_id   = $this->post(\Formatter::TYPE_INT, "creator_user_id");
		$dismissal_user_id = $this->post(\Formatter::TYPE_INT, "dismissal_user_id");
		$thread_map        = $this->post(\Formatter::TYPE_STRING, "thread_map");

		// выполняем действия при создании заявки
		try {
			Domain_Thread_Scenario_Socket::addSystemMessageToDismissalRequestThread($creator_user_id, $dismissal_user_id, $thread_map);
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParseFatalException("passed empty message list");
		}

		return $this->ok();
	}

	/**
	 * получаем треды для фида
	 * @return array
	 * @throws \blockException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Message_DuplicateClientMessageId
	 * @throws cs_ThreadIsReadOnly
	 */
	public function getThreadListForFeed():array {

		$thread_map_list = $this->post(\Formatter::TYPE_ARRAY, "thread_map_list");

		[$thread_meta_list, $menu_list] = Domain_Thread_Scenario_Socket::getThreadListForFeed($this->user_id, $thread_map_list);

		// формируем ответ
		$frontend_thread_meta_list = [];
		foreach ($thread_meta_list as $item) {
			$frontend_thread_meta_list[] = Apiv1_Format::threadMeta($item);
		}

		$frontend_thread_menu_list = [];
		foreach ($menu_list as $item) {
			$frontend_thread_menu_list[] = Apiv1_Format::threadMenu($item);
		}

		return $this->ok([
			"thread_meta_list" => (array) $frontend_thread_meta_list,
			"thread_menu_list" => (array) $frontend_thread_menu_list,
		]);
	}

	/**
	 * получаем треды для фида батчингом
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public function getThreadListForBatchingFeed():array {

		// -------------------------------------------------------
		// !!! ВНИМАНИЕ
		// сокет используется только для метода feed.getBatchingThreads
		// -------------------------------------------------------

		$thread_map_list                          = $this->post(\Formatter::TYPE_ARRAY, "thread_map_list");
		$conversation_dynamic_by_conversation_map = $this->post(\Formatter::TYPE_ARRAY, "conversation_dynamic_by_conversation_map");
		$conversation_meta_by_conversation_map    = $this->post(\Formatter::TYPE_ARRAY, "conversation_meta_by_conversation_map");

		[$thread_meta_list, $menu_list] = Domain_Thread_Scenario_Socket::getThreadListForBatchingFeed(
			$this->user_id, $thread_map_list, $conversation_dynamic_by_conversation_map, $conversation_meta_by_conversation_map
		);

		// формируем ответ
		$frontend_thread_meta_list = [];
		foreach ($thread_meta_list as $item) {
			$frontend_thread_meta_list[] = Apiv1_Format::threadMeta($item);
		}

		$frontend_thread_menu_list = [];
		foreach ($menu_list as $item) {
			$frontend_thread_menu_list[] = Apiv1_Format::threadMenu($item);
		}

		return $this->ok([
			"thread_meta_list" => (array) $frontend_thread_meta_list,
			"thread_menu_list" => (array) $frontend_thread_menu_list,
		]);
	}

	// отписаться от тредов
	public function doUnfollowThreadList():array {

		$thread_map_list = $this->post("?a", "thread_map_list");

		foreach ($thread_map_list as $v) {

			// отписываемся от треда
			Domain_Thread_Action_Follower_Unfollow::do($this->user_id, $v, true);
		}

		return $this->ok();
	}

	// отписаться от всех тредов по его родителю
	public function doUnfollowThreadListByConversationMap():array {

		$source_parent_map = $this->post("?s", "conversation_map");

		Helper_Threads::unfollowThreadListByMetaMap($this->user_id, $source_parent_map);

		return $this->ok();
	}

	/**
	 * создаем тред для заявки на наем
	 *
	 * @throws ParamException
	 * @throws cs_HiringRequestIsNotAllowedForAddThread
	 */
	public function addThreadForHiringRequest():array {

		$creator_user_id       = $this->post(\Formatter::TYPE_INT, "creator_user_id");
		$request_id            = $this->post(\Formatter::TYPE_INT, "request_id");
		$is_company_creator    = $this->post(\Formatter::TYPE_INT, "is_company_creator");
		$is_need_thread_attach = $this->post(\Formatter::TYPE_INT, "is_need_thread_attach", 0);
		$is_need_thread_attach = $is_need_thread_attach === 1;

		// для заявки создателя компании тред не нужен
		if ($is_company_creator) {
			return $this->ok();
		}

		$thread_meta_row = Domain_Thread_Action_AddToHiringRequest::do($creator_user_id, $request_id, $is_need_thread_attach);

		return $this->ok([
			"thread_map" => (string) $thread_meta_row["thread_map"],
		]);
	}

	/**
	 * создаем тред для заявки на увольнение
	 *
	 * @throws ParamException
	 */
	public function addThreadForDismissalRequest():array {

		$creator_user_id       = $this->post(\Formatter::TYPE_INT, "creator_user_id");
		$request_id            = $this->post(\Formatter::TYPE_INT, "request_id");
		$is_need_thread_attach = $this->post(\Formatter::TYPE_INT, "is_need_thread_attach", 0);
		$is_need_thread_attach = $is_need_thread_attach === 1;

		$thread_meta_row = Domain_Thread_Action_AddToDismissalRequest::do($creator_user_id, $request_id, $is_need_thread_attach);

		return $this->ok([
			"thread_map" => (string) $thread_meta_row["thread_map"],
		]);
	}

	/**
	 * отправляем сообщение-Напоминание в чат
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \busException
	 * @throws \parseException
	 */
	public function sendRemindMessage():array {

		$message_map     = $this->post(\Formatter::TYPE_STRING, "message_map");
		$comment         = $this->post(\Formatter::TYPE_STRING, "comment");
		$remind_type     = $this->post(\Formatter::TYPE_INT, "remind_type");
		$creator_user_id = $this->post(\Formatter::TYPE_INT, "creator_user_id");

		try {
			Domain_Thread_Scenario_Socket::sendRemindMessage($message_map, $creator_user_id, $comment, $remind_type);
		} catch (cs_ConversationIsLocked|cs_Conversation_IsBlockedOrDisabled|cs_Message_HaveNotAccess|
		cs_Message_IsDeleted|cs_ParentMessage_IsDeleted|cs_ThreadIsReadOnly|cs_Thread_ParentEntityNotFound) {
			// обработчик события не смог отправить сообщение - ничего не делаем в случае ошибки
		} catch (cs_Message_DuplicateClientMessageId) {

			// а вот это странно - кидаем ошибку
			return $this->error(10511, "passed duplicate client message id");
		} catch (cs_Message_IsTooLong) {

			// а вот это странно - кидаем ошибку
			return $this->error(2418005, "Comment text is too long");
		} catch (Domain_Thread_Exception_Message_ListIsEmpty) {
			throw new ParseFatalException("passed empty message list");
		}

		return $this->ok();
	}

	/**
	 * актуализируем данные Напоминания для сообщения-оригинала
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws ParamException
	 */
	public function actualizeTestRemindForMessage():array {

		$message_map = $this->post(\Formatter::TYPE_STRING, "message_map");

		assertTestServer();

		Domain_Thread_Scenario_Socket::actualizeTestRemindForMessage($message_map);

		return $this->ok();
	}
}