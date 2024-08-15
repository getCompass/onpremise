<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;

/**
 * API-сценарии домена «напоминаний».
 */
class Domain_Remind_Scenario_Api {

	/**
	 * создаём Напоминание
	 *
	 * @throws Domain_Conversation_Exception_Guest_AttemptInitialConversation
	 * @throws Domain_Conversation_Exception_Message_NotAllowForUser
	 * @throws Domain_Conversation_Exception_User_IsAccountDeleted
	 * @throws Domain_Remind_Exception_AlreadyExist
	 * @throws Domain_Remind_Exception_RemindAtBeforeCurrentTime
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Conversation_MemberIsDisabled
	 * @throws cs_Conversation_UserbotIsDeleted
	 * @throws cs_Conversation_UserbotIsDisabled
	 * @throws cs_Message_IsTooLong
	 * @throws cs_UserIsNotMember
	 */
	public static function create(int $user_id, string $message_map, int $remind_at, string $comment):array {

		// проверяем, что не вылезли за 2038 год (фатально для mysql)
		if (!isValidTimestamp($remind_at)) {
			throw new ParamException("incorrect remind_at");
		}

		// если время меньше текущего - возвращаем об этом ошибку
		if ($remind_at <= time()) {
			throw new Domain_Remind_Exception_RemindAtBeforeCurrentTime("remind before current time");
		}

		// если сообщение не из диалога
		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParamException("the message is not from conversation");
		}

		// фильтруем коммент
		$comment = Domain_Remind_Action_FilteredComment::do($comment);

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
		$remind = Domain_Conversation_Action_Message_AddRemind::do($message_map, $conversation_map, $meta_row, $comment, $remind_at, $user_id);

		return [$remind->remind_id, $comment];
	}

	/**
	 * удаляем Напоминание
	 *
	 * @throws Domain_Conversation_Exception_Message_NotAllowForUser
	 * @throws Domain_Remind_Exception_AlreadyDone
	 * @throws Domain_Remind_Exception_AlreadyRemoved
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws cs_UserIsNotAdmin
	 * @throws cs_UserIsNotMember
	 */
	public static function remove(int $user_id, string $message_map, int $role, int $permissions):void {

		// если сообщение не из диалога
		if (!\CompassApp\Pack\Message::isFromConversation($message_map)) {
			throw new ParamException("the message is not from conversation");
		}

		// получаем мету диалога
		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);

		// если наш пользователь не является участником диалога, то ругаемся
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			throw new cs_UserIsNotMember("not member of conversation");
		}

		// проверяем, что разрешено действие для данного типа диалога
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::REMIND_REMOVE_FROM_CONVERSATION);

		// удаляем Напоминание
		Domain_Conversation_Action_Message_RemoveRemind::do($message_map, $conversation_map, $meta_row, $user_id, $role, $permissions);
	}
}