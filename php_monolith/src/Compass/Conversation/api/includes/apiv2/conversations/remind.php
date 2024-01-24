<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * контроллер, отвечающий за Напоминания сообщений чата
 */
class Apiv2_Conversations_Remind extends \BaseFrame\Controller\Api {

	public const ALLOW_METHODS = [
		"create",
		"remove",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"create",
		"remove",
	];

	/**
	 * Метод для создания Напоминания
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @long try..catch разросся
	 */
	public function create():array {

		$message_key = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);
		$remind_at   = $this->post(\Formatter::TYPE_INT, "remind_at");
		$comment     = $this->post(\Formatter::TYPE_STRING, "comment", "");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::REMIND_CREATE);

		try {
			[$remind_id, $comment] = Domain_Remind_Scenario_Api::create($this->user_id, $message_map, $remind_at, $comment);
		} catch (cs_Message_IsDeleted) {
			throw new CaseException(2218003, "Message is deleted");
		} catch (Domain_Conversation_Exception_Message_NotAllowForRemind|Domain_Conversation_Exception_Message_NotAllowForUser) {
			throw new CaseException(2218004, "You are not allowed to do this action");
		} catch (cs_Message_IsTooLong) {
			throw new CaseException(2218005, "Comment text is too long");
		} catch (cs_Conversation_MemberIsDisabled|Domain_Conversation_Exception_User_IsAccountDeleted|cs_Conversation_UserbotIsDisabled|cs_Conversation_UserbotIsDeleted $e) {

			$error = Helper_Conversations::getCheckIsAllowedError($e, true);
			throw new CaseException($error["error_code"], $error["message"]);
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2218001, "User is not member of conversation");
		} catch (Domain_Remind_Exception_AlreadyExist) {
			throw new CaseException(2235004, "Remind already set in message");
		} catch (cs_Message_IsNotExist) {
			throw new CaseException(2218007, "Message is not exist");
		} catch (Domain_Remind_Exception_RemindAtBeforeCurrentTime) {
			throw new CaseException(2218008, "Time before current");
		} catch (cs_ActionIsNotAllowedInSupportConversation) {
			throw new ParamException("Trying to add remind in support conversation");
		} catch (Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_REMIND);

		return $this->ok([
			"remind" => (object) Apiv2_Format::remind($remind_id, $remind_at, $this->user_id, $comment),
		]);
	}

	/**
	 * Метод для удаления Напоминания
	 *
	 * @throws CaseException
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function remove():array {

		$message_key = $this->post(\Formatter::TYPE_STRING, "message_key");
		$message_map = \CompassApp\Pack\Message\Conversation::tryDecrypt($message_key);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::REMIND_REMOVE);

		try {
			Domain_Remind_Scenario_Api::remove($this->user_id, $message_map, $this->role, $this->permissions);
		} catch (Domain_Conversation_Exception_Message_NotAllowForRemind|Domain_Conversation_Exception_Message_NotAllowForUser) {
			throw new CaseException(2218004, "You are not allowed to do this action");
		} catch (cs_Message_IsDeleted) {
			throw new CaseException(2218003, "Message is deleted");
		} catch (cs_UserIsNotAdmin) {
			throw new CaseException(2218006, "Member has no access to remind management");
		} catch (cs_UserIsNotMember) {
			throw new CaseException(2218001, "User is not member of conversation");
		} catch (cs_Message_IsNotExist) {
			throw new CaseException(2218007, "Message is not exist");
		} catch (Domain_Remind_Exception_AlreadyRemoved) {
			throw new CaseException(2235005, "Remind already remove");
		} catch (Domain_Remind_Exception_AlreadyDone) {
			throw new CaseException(2235006, "Remind already done");
		}

		return $this->ok();
	}
}
