<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Контроллер, отвечающий за Напоминания в треде
 */
class Apiv2_Threads_Remind extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"create",
		"remove",
	];

	/**
	 * Создать Напоминание для сообщения треда
	 * версия метода 2
	 *
	 * @throws ParamException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 * @throws CaseException
	 * @long try..catch разросся
	 */
	public function create():array {

		$message_key = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map = \CompassApp\Pack\Message::tryDecrypt($message_key);
		$remind_at   = $this->post(\Formatter::TYPE_INT, "remind_at");
		$comment     = $this->post(\Formatter::TYPE_STRING, "comment", "");
		$is_parent   = $this->post(\Formatter::TYPE_INT, "is_parent", 0) == 1;

		// проверяем блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::REMIND_CREATE);

		// создаём Напоминание
		try {
			[$remind_id, $comment] = Domain_Remind_Scenario_Api::create($this->user_id, $message_map, $remind_at, $comment, $is_parent, $this->method_version);
		} catch (cs_Message_IsTooLong|Gateway_Socket_Exception_Conversation_MessageTextIsTooLong) {
			throw new CaseException(2218005, "Comment text is too long");
		} catch (cs_Message_IsDeleted|Gateway_Socket_Exception_Conversation_MessageIsDeleted) {
			return $this->error(549, "Message is deleted");
		} catch (cs_Thread_UserNotMember|cs_Message_HaveNotAccess|Gateway_Socket_Exception_Conversation_UserIsNotMember) {
			return $this->error(530, "You are not allow to do this action");
		} catch (cs_Conversation_IsBlockedOrDisabled $e) {
			return $this->_returnErrorOnOpponentIsBlockedOrDisabled($e->getAllowStatus());
		} catch (Gateway_Socket_Exception_Conversation_IsBlockedOrDisabled $e) {

			$extra = $e->getExtra();
			return $this->_returnErrorOnOpponentIsBlockedOrDisabled($extra["allow_status"]);
		} catch (Domain_Thread_Exception_Message_NotAllowForRemind|Gateway_Socket_Exception_Conversation_MessageNotAllowForRemind) {
			throw new CaseException(2218004, "You are not allowed to do this action");
		} catch (Domain_Remind_Exception_AlreadyExist|Gateway_Socket_Exception_Conversation_RemindAlreadyExist) {
			throw new CaseException(2235004, "Remind already set in message");
		} catch (Gateway_Socket_Exception_Conversation_MessageIsNotExist) {
			throw new CaseException(2218007, "Message is not exist");
		} catch (Domain_Remind_Exception_RemindAtBeforeCurrentTime) {
			throw new CaseException(2229001, "Time before current");
		} catch (Domain_Group_Exception_NotEnoughRights) {
			return $this->error(2129003, "not enough right");
		}

		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_REMIND);

		return $this->ok([
			"remind" => (object) Apiv2_Format::remind($remind_id, $remind_at, $this->user_id, $comment),
		]);
	}

	// возвращаем ошибку в зависимости от полученного allow_status
	// @long - switch..case
	protected function _returnErrorOnOpponentIsBlockedOrDisabled(int $allow_status):array {

		// в зависимости от полученного allow_status — подготавливаем код и сообщение об ошибке
		switch ($allow_status) {

			case 11: // в диалог нельзя писать, собеседник заблокирован нами

				$error_code    = 905;
				$error_message = "opponent has blocked";
				break;

			case 12: // в диалог нельзя писать, мы заблокированы собеседником

				$error_code    = 904;
				$error_message = "opponent blocked us";
				break;

			// в диалог нельзя писать, один из участников заблокирован в системе
			case Type_Thread_Utils::CONVERSATION_ALLOW_STATUS_MEMBER_IS_DISABLED:

				$error_code    = 532;
				$error_message = "opponent has blocked in system";
				break;

			// в диалог нельзя писать, пользователь удалил аккаунт
			case Type_Thread_Utils::CONVERSATION_ALLOW_STATUS_MEMBER_IS_DELETED:

				$error_code    = 2129001;
				$error_message = "opponent has delete account";
				break;

			// в диалог нельзя писать, бот выключен
			case Type_Thread_Utils::CONVERSATION_ALLOW_STATUS_USERBOT_IS_DISABLED:

				$error_code    = 2134001;
				$error_message = "userbot has disabled";
				break;

			// в диалог нельзя писать, бот удалён
			case Type_Thread_Utils::CONVERSATION_ALLOW_STATUS_USERBOT_IS_DELETED:

				$error_code    = 2134002;
				$error_message = "userbot has deleted";
				break;

			default:
				throw new ReturnFatalException(__METHOD__ . ": passed unhandled error");
		}

		return $this->error($error_code, $error_message);
	}

	/**
	 * удалить Напоминание с сообщения треда
	 *
	 * @throws ParamException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 * @throws CaseException
	 */
	public function remove():array {

		$message_key = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map = \CompassApp\Pack\Message::tryDecrypt($message_key);

		// проверяем блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::REMIND_REMOVE);

		// удаляем Напоминание с сообщения
		try {
			Domain_Remind_Scenario_Api::remove($this->user_id, $message_map);
		} catch (cs_Message_HaveNotAccess|cs_Thread_UserNotMember) {
			return $this->error(530, "You are not allow to do this action");
		} catch (cs_Message_IsDeleted) {
			return $this->error(549, "Message is deleted");
		} catch (Domain_Remind_Exception_UserIsNotCreator) {
			throw new CaseException(2218006, "Member has no access to remind management");
		} catch (Domain_Thread_Exception_Message_NotAllowForRemind) {
			throw new CaseException(2218004, "You are not allowed to do this action");
		} catch (Domain_Remind_Exception_AlreadyRemoved) {
			throw new CaseException(2237001, "Remind already remove");
		} catch (Domain_Remind_Exception_AlreadyDone) {
			throw new CaseException(2237002, "Remind already done");
		}

		return $this->ok();
	}
}
