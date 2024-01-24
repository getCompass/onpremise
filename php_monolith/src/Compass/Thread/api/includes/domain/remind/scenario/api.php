<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Сценарии Напоминаний для API
 */
class Domain_Remind_Scenario_Api {

	/**
	 * Сценарий создания Напоминания
	 *
	 * @param int    $user_id
	 * @param string $message_map
	 * @param int    $remind_at
	 * @param string $comment
	 * @param bool   $is_parent
	 *
	 * @return array
	 * @throws Domain_Remind_Exception_AlreadyExist
	 * @throws Domain_Remind_Exception_RemindAtBeforeCurrentTime
	 * @throws Domain_Thread_Exception_Message_NotAllowForRemind
	 * @throws Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled
	 * @throws Gateway_Socket_Exception_Conversation_MessageIsDeleted
	 * @throws Gateway_Socket_Exception_Conversation_MessageIsNotExist
	 * @throws Gateway_Socket_Exception_Conversation_MessageNotAllowForRemind
	 * @throws Gateway_Socket_Exception_Conversation_MessageTextIsTooLong
	 * @throws Gateway_Socket_Exception_Conversation_RemindAlreadyExist
	 * @throws Gateway_Socket_Exception_Conversation_UserIsNotMember
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws cs_Conversation_IsBlockedOrDisabled
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Message_IsTooLong
	 * @throws cs_Thread_UserNotMember
	 */
	public static function create(int $user_id, string $message_map, int $remind_at, string $comment, bool $is_parent):array {

		// проверяем, что не вылезли за 2038 год (фатально для mysql)
		if (!isValidTimestamp($remind_at)) {
			throw new ParamException("incorrect remind_at");
		}

		// если время меньше текущего - возвращаем об этом ошибку
		if ($remind_at <= time()) {
			throw new Domain_Remind_Exception_RemindAtBeforeCurrentTime("remind before current time");
		}

		// фильтруем коммент для Напоминания
		$comment = Domain_Remind_Action_FilteredComment::do($comment);

		// если передано родительское сообщение, то закрепляем за сообщением диалога (!!! с будущей отправкой Напоминания в тред)
		if ($is_parent) {

			try {
				$remind_id = Gateway_Socket_Conversation::createRemindOnConversationMessage($user_id, $message_map, $remind_at, $comment);
			} catch (Domain_Thread_Exception_Guest_AttemptInitialThread) {
				throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "guest attempt to initial thread");
			}

			return [$remind_id, $comment];
		}

		// если сообщение не из треда, то ругаемся
		if (!\CompassApp\Pack\Message::isFromThread($message_map)) {
			throw new ParamException("the message is not from thread");
		}

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		$meta_row   = Helper_Threads::getMetaIfUserMember($thread_map, $user_id, false);
		$remind     = Domain_Thread_Action_Message_AddRemind::do($message_map, $thread_map, $meta_row, $comment, $remind_at, $user_id);

		return [$remind->remind_id, $comment];
	}

	/**
	 * удаляем Напоминание с сообщения
	 *
	 * @throws Domain_Remind_Exception_AlreadyDone
	 * @throws Domain_Remind_Exception_AlreadyRemoved
	 * @throws Domain_Remind_Exception_UserIsNotCreator
	 * @throws Domain_Thread_Exception_Message_NotAllowForRemind
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws cs_Message_HaveNotAccess
	 * @throws cs_Message_IsDeleted
	 * @throws cs_Thread_UserNotMember
	 */
	public static function remove(int $user_id, string $message_map):void {

		// если сообщение не треда, то ругаемся
		if (!\CompassApp\Pack\Message::isFromThread($message_map)) {
			throw new ParamException("the message is not from thread");
		}

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);

		try {
			$meta_row = Helper_Threads::getMetaIfUserMember($thread_map, $user_id, false);
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			$meta_row = $e->getMetaRow(); // если пользователь заблочен, мы всё равно позволяем удалить Напоминание
		}

		Domain_Thread_Action_Message_RemoveRemind::do($message_map, $thread_map, $meta_row, $user_id);
	}
}