<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * хелпер для всего, что связано с приглашениями
 */
class Helper_Invites {

	// создание инвайта
	public static function create(int $sender_user_id, int $user_id, string $single_conversation_map, array $group_meta_row, bool $is_need_increment_count = true):array {

		return Type_Invite_Single::create($sender_user_id, $user_id, $single_conversation_map, $group_meta_row, $is_need_increment_count);
	}

	// отправка приглашения в групповой диалог пользователю
	public static function inviteUserFromSingle(int $sender_user_id, int $user_id, array $group_meta_row, array $single_meta_row, bool $is_need_increment_count = true, bool $is_need_async = true, string $platform = Type_Conversation_Message_Handler_Default::WITHOUT_PLATFORM):array {

		// получаем инвайт
		$invite_row = Type_Conversation_Invite::getByConversationMapAndUserId($sender_user_id, $user_id, $group_meta_row["conversation_map"]);

		// получаем флаг нужно ли отправлять системное сообщение об инвайте в группу
		$is_need_send_system_message = self::_isNeedSendSystemMessageAboutInvite($invite_row);

		// проверяем существует ли запись, если нет - создаем
		if (!isset($invite_row["invite_map"])) {
			$invite_row = self::create($sender_user_id, $user_id, $single_meta_row["conversation_map"], $group_meta_row, $is_need_increment_count);
		}

		// если пользователь не состоит в группе
		if (!Type_Conversation_Meta_Users::isMember($user_id, $group_meta_row["users"])) {

			// если приглашение не имеет статус active
			if ($invite_row["status"] != Type_Invite_Handler::STATUS_ACTIVE) {
				Type_Invite_Single::setActive($invite_row, $user_id, $group_meta_row);
			}

			// отправляем системное сообщение о приглашении в группу, если необходимо
			if ($is_need_send_system_message) {
				self::_sendSystemMessageForInvite($group_meta_row["conversation_map"], $group_meta_row, $user_id);
			}
		}

		// пользователи, для которых не отправляем ws-ивент
		$not_send_ws_event_user_list = [];

		// если у приглашающего отсутствует single-диалог с приглашенным в левом меню или он спрятан
		$left_menu_row = Type_Conversation_LeftMenu::get($sender_user_id, $single_meta_row["conversation_map"]);
		if (!isset($left_menu_row["user_id"]) || $left_menu_row["is_hidden"] == 1) {
			$not_send_ws_event_user_list = [$sender_user_id];
		}

		// отправляем сообщение с инвайтом
		return self::_sendInviteMessage(
			$sender_user_id,
			$invite_row["invite_map"],
			$single_meta_row["conversation_map"],
			$single_meta_row,
			$platform,
			$not_send_ws_event_user_list
		);
	}

	// отправляем сообщение с инвайтом
	protected static function _sendInviteMessage(int $sender_user_id, string $invite_map, string $single_conversation_map, array $single_meta_row, string $platform, array $not_send_ws_event_user_list = []):array {

		// добавляем в single диалог сообщение с приглашением
		$message = self::_addMessageWithInvite(
			$single_conversation_map, $sender_user_id, $invite_map, $single_meta_row, $platform, $not_send_ws_event_user_list
		);

		// готовим сообщения под формат
		return Type_Conversation_Message_Main::getLastVersionHandler()::prepareForFormatLegacy($message);
	}

	// добавляем сообщение с приглашением в single-диалог
	protected static function _addMessageWithInvite(string $single_conversation_map, int $sender_user_id, string $invite_map, array $single_meta_row, string $platform, array $not_send_ws_event_user_list):array {

		$invite_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeInvite($sender_user_id, $invite_map, $platform);
		return self::_addMessage($single_conversation_map, $invite_message, $single_meta_row, $not_send_ws_event_user_list);
	}

	// функция для отправки сообщения
	protected static function _addMessage(string $single_conversation_map, array $invite_message, array $single_meta_row, array $not_send_ws_event_user_list):array {

		return Helper_Conversations::addMessage(
			$single_conversation_map,
			$invite_message,
			$single_meta_row["users"],
			$single_meta_row["type"],
			$single_meta_row["conversation_name"],
			$single_meta_row["extra"],
			not_send_ws_event_user_list: $not_send_ws_event_user_list
		);
	}

	// отправка приглашения в групповой диалог пользователю с асинхронной отправкой сообщений через хукер
	public static function inviteUserFromSingleWithAsyncMessages(int $sender_user_id, int $user_id, array $group_meta_row):void {

		// получаем запись с инвайтом
		$invite_row = Type_Conversation_Invite::getByConversationMapAndUserId($sender_user_id, $user_id, $group_meta_row["conversation_map"]);

		// получаем флаг нужно ли отправлять системное сообщение об инвайте в группу
		$is_need_send_system_message = self::_isNeedSendSystemMessageAboutInvite($invite_row);

		// если нет диалога, создаем
		$single_meta_row         = Helper_Single::createIfNotExist($sender_user_id, $user_id, true, false);
		$single_conversation_map = $single_meta_row["conversation_map"];

		// проверяем существует ли запись, если нет - создаем
		if (!isset($invite_row["invite_map"])) {
			$invite_row = self::create($sender_user_id, $user_id, $single_meta_row["conversation_map"], $group_meta_row);
		}

		if (!Type_Conversation_Meta_Users::isMember($user_id, $group_meta_row["users"])) {

			// если приглашение не имеет статус active, то устанавливаем инвайту статус active
			if ($invite_row["status"] != Type_Invite_Handler::STATUS_ACTIVE) {
				Type_Invite_Single::setActive($invite_row, $user_id, $group_meta_row);
			}
		}

		// отправляем сообщения об инвайте
		Type_Phphooker_Main::sendInviteToUser($sender_user_id, $user_id, $invite_row["invite_map"], $single_conversation_map,
			$group_meta_row, $is_need_send_system_message);
	}

	// принимаем инвайт
	public static function setAccepted(string $invite_map, int $user_id):array {

		$invite_row = self::_tryGetInviteRowIfExist($invite_map);

		// проверяем, что диалог не заблокирован инвайт принадлежит юзеру
		self::_throwIfConversationIsLocked($invite_row["group_conversation_map"]);
		self::_throwIfInviteNotMine($invite_row, $user_id);

		// если инвайт уже принят
		self::_throwIfInviteIsAccepted($invite_row["status"]);

		// если инвайт уже отклонен
		self::_throwIfInviteIsDeclined($invite_row["status"]);

		// если инвайт был отозван
		self::_throwIfInviteIsRevoked($invite_row["status"]);

		// если инвайт не активный
		self::_throwIfInviteIsNotActive($invite_row["status"]);

		$meta_row = Type_Conversation_Meta::get($invite_row["group_conversation_map"]);

		// принимаем приглашение
		Type_Invite_Handler::setAccepted($invite_map, $invite_row, $user_id, $meta_row);

		return $invite_row;
	}

	// отклоняем приглашение в группу
	public static function setDeclined(string $invite_map, int $user_id):void {

		$invite_row = self::_tryGetInviteRowIfExist($invite_map);

		// проверяем, что диалог не заблокирован; что инвайт принадлежит юзеру
		self::_throwIfConversationIsLocked($invite_row["group_conversation_map"]);
		self::_throwIfInviteNotMine($invite_row, $user_id);

		// если инвайт уже принят
		self::_throwIfInviteIsAccepted($invite_row["status"]);

		// если инвайт уже отклонен
		self::_throwIfInviteIsDeclined($invite_row["status"]);

		// если инвайт был отозван
		self::_throwIfInviteIsRevoked($invite_row["status"]);

		// если инвайт не активный
		self::_throwIfInviteIsNotActive($invite_row["status"]);

		$meta_row = Type_Conversation_Meta::get($invite_row["group_conversation_map"]);

		// отклоняем приглашение
		Type_Invite_Handler::setDeclinedByDpcInvite($invite_row["invite_map"], $invite_row, $user_id, $meta_row);

		// отправляем системное сообщение с WS событием
		self::_onInviteDeclined($invite_row["group_conversation_map"], $invite_row["invite_map"], $user_id, $meta_row);

		// почемечаем отклоненными остальные инвайты в данный диалог
		Type_Phphooker_Main::setDeclineAllUserInviteToConversation($user_id, $invite_row["group_conversation_map"]);
	}

	// отправляем системное сообщение с WS событием
	protected static function _onInviteDeclined(string $conversation_map, string $invite_map, int $user_id, array $meta_row):void {

		// получаем talking_user_list для изменения статуса инвайта в событие
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);

		// добавляем в массив юзера, который отклонил инвайт
		$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);

		// отправляем событие об отклонении приглашение
		Gateway_Bus_Sender::conversationInviteStatusChanged($talking_user_list, $invite_map, $user_id, Type_Invite_Handler::STATUS_DECLINED, $conversation_map);
	}

	// отзываем приглашение в группу
	public static function setRevoked(int $user_id, array $meta_row):void {

		// проверяем вступил ли уже пользователь в группу (cs_InviteIsAccepted)
		self::_throwIfAlreadyMemberOfGroup($user_id, $meta_row["users"]);

		$invite_list = Type_Conversation_Invite::getAllByStatusAndConversationMapAndUserId(
			$user_id, $meta_row["conversation_map"], Type_Invite_Handler::STATUS_ACTIVE, count($meta_row["users"])
		);

		// если нет активных инвайтов, бросаем исключение (cs_InviteIsNotActive)
		self::_throwIfNotActiveInvites($invite_list);

		// помечаем все инвайты юзеру отозванными
		self::_setRevokedAllUserInviteToConversation($user_id, $meta_row, $invite_list);
	}

	// выдаем исключение, если пользователь уже участник группы
	protected static function _throwIfAlreadyMemberOfGroup(int $user_id, array $users):void {

		if (Type_Conversation_Meta_Users::isMember($user_id, $users)) {
			throw new cs_InviteIsAccepted();
		}
	}

	// выдаем исключение, если нет активных инвайтов, которые можно было бы отозвать
	protected static function _throwIfNotActiveInvites(array $invite_list):void {

		if (count($invite_list) == 0) {
			throw new cs_InviteIsNotActive();
		}
	}

	// помечаем инвайт отозванным
	protected static function _setRevokedAllUserInviteToConversation(int $user_id, array $meta_row, array $invite_list):void {

		// ws-событие получают все участники группы
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);

		// проходимся по всем инвайтам - помечаем каждый инвайт отозванным
		foreach ($invite_list as $invite_row) {

			// помечаем revoked
			Type_Invite_Handler::setRevoked($invite_row["invite_map"], $invite_row, $user_id, $meta_row);

			// отправляем событие
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($invite_row["sender_user_id"], false);
			Gateway_Bus_Sender::conversationInviteStatusChanged(
				$talking_user_list, $invite_row["invite_map"], $invite_row["user_id"], Type_Invite_Handler::STATUS_REVOKED, $meta_row["conversation_map"]
			);
		}
	}

	// помечаем инвайт неактиным
	public static function setInactiveAllUserInviteToConversation(int $user_id, string $conversation_map, int $inactive_reason, bool $is_remove_user = false):void {

		$meta_row     = Type_Conversation_Meta::get($conversation_map);
		$member_count = count($meta_row["users"]);

		// помечаем отправленные инвайты неактиным
		$invite_list = Type_Conversation_Invite::getAllByStatusAndConversationMapAndSenderUserId($user_id, $conversation_map, Type_Invite_Handler::STATUS_ACTIVE);
		self::_setInactiveForInviteList($invite_list, $user_id, $meta_row, $inactive_reason, $is_remove_user, $meta_row["users"]);

		// помечаем полученные инвайты неактиным
		$invite_list = Type_Conversation_Invite::getAllByStatusAndConversationMapAndUserId(
			$user_id,
			$conversation_map,
			Type_Invite_Handler::STATUS_ACTIVE, $member_count
		);
		self::_setInactiveForInviteList($invite_list, $user_id, $meta_row, $inactive_reason, $is_remove_user);
	}

	// помечаем каждый инвайт inactive
	protected static function _setInactiveForInviteList(array $invite_list, int $user_id, array $meta_row, int $inactive_reason, bool $is_remove_user, array $users = []):void {

		// проходимся по всем инвайтам - помечаем inactive и отправляем события
		foreach ($invite_list as $invite_row) {

			// помечаем инвайт неактивным
			Type_Invite_Handler::setInactive($invite_row["invite_map"], $invite_row, $user_id, $meta_row, $inactive_reason, $is_remove_user);

			$talking_user_list = self::_getTalkingUserListForSetInactiveInvite($invite_row, $inactive_reason, $users);

			// отправляем ws-событие
			$status = self::_getStatusInviteForWsEvent();
			Gateway_Bus_Sender::conversationInviteStatusChanged(
				$talking_user_list, $invite_row["invite_map"], $invite_row["user_id"], $status, $meta_row["conversation_map"]
			);
		}
	}

	// формируем talking_user_list для того чтобы сообщить о том что инвайт стал неактивным
	protected static function _getTalkingUserListForSetInactiveInvite(array $invite_row, int $inactive_reason, array $users):array {

		$talking_user_list = [];

		if (count($users) > 0) {
			$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($users);
		}

		$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($invite_row["user_id"], false);

		// взависимости от причины пометки inactive формируем talking_user_list
		if ($inactive_reason == Type_Invite_Handler::INACTIVE_REASON_ACCEPTED) {
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($invite_row["sender_user_id"], false);
		}

		return $talking_user_list;
	}

	// получаем статус инвайта для ws инвента
	protected static function _getStatusInviteForWsEvent():int {

		return Type_Invite_Handler::STATUS_INACTIVE;
	}

	// помечаем инвайт declined
	public static function setDeclinedAllUserInviteToConversation(int $user_id, string $conversation_map):void {

		$meta_row      = Type_Conversation_Meta::get($conversation_map);
		$members_count = count($meta_row["users"]);

		// для полученных инвайтов
		$invite_list = Type_Conversation_Invite::getAllByStatusAndConversationMapAndUserId(
			$user_id,
			$conversation_map,
			Type_Invite_Handler::STATUS_ACTIVE,
			$members_count
		);

		// проходимся по всем инвайтам - помечаем каждый инвайт declined
		foreach ($invite_list as $invite_row) {

			// помечаем declined
			Type_Invite_Handler::setDeclinedByDpcConversation($invite_row["invite_map"], $invite_row, $user_id, $meta_row);

			// получаем talking_user_item
			$talking_user_item = Gateway_Bus_Sender::makeTalkingUserItem($invite_row["sender_user_id"], false);

			// отправляем событие
			Gateway_Bus_Sender::conversationInviteStatusChanged(
				[$talking_user_item],
				$invite_row["invite_map"],
				$invite_row["user_id"],
				Type_Invite_Handler::STATUS_DECLINED,
				$conversation_map
			);
		}
	}

	/**
	 * получаем ошибку для апи из invites/doDecline
	 * cs_InviteIsAccepted, cs_InviteIsRevoked, cs_InviteIsNotActive
	 *
	 * @param      $e
	 *
	 * @throws \parseException
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 */
	public static function getDoDeclinedError($e, bool $is_new_error = false):array {

		if ($is_new_error) {
			return self::_returnNewDoDeclinedError($e);
		} else {
			return self::_returnOldDoDeclinedError($e);
		}
	}

	/**
	 * возвращаем новые ошибки
	 *
	 * @param $e
	 *
	 * @throws \parseException
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 */
	protected static function _returnNewDoDeclinedError($e):array {

		if ($e instanceof cs_InviteIsAccepted) {
			return self::_getError(914, "invite was accepted");
		}
		if ($e instanceof cs_InviteIsRevoked) {
			return self::_getError(913, "invite was revoked");
		}
		if ($e instanceof cs_InviteIsNotActive) {
			return self::_getError(911, "invite is not active");
		}
		throw new ParseFatalException("Received unknown error in e");
	}

	/**
	 * возвращаем старые ошибки
	 *
	 * @param $e
	 *
	 * @throws \parseException
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 */
	protected static function _returnOldDoDeclinedError($e):array {

		if ($e instanceof cs_InviteIsAccepted) {
			return self::_getError(911, "invite was accepted");
		}
		if ($e instanceof cs_InviteIsRevoked) {
			return self::_getError(911, "invite was revoked");
		}
		if ($e instanceof cs_InviteIsNotActive) {
			return self::_getError(911, "invite is not active");
		}
		throw new ParseFatalException("Received unknown error in e");
	}

	/**
	 * получаем ошибку для апи из invites/tryAccept
	 * cs_InviteIsDeclined, cs_InviteIsAccepted, cs_InviteIsRevoked, cs_InviteIsNotActive
	 *
	 * @param      $e
	 *
	 * @throws \parseException
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 */
	public static function getTryAcceptError($e, bool $is_new_error = false):array {

		if ($is_new_error) {
			return self::_returnNewTryAcceptError($e);
		} else {
			return self::_returnOldTryAcceptError($e);
		}
	}

	/**
	 * возвращаем новые ошибки
	 *
	 * @param $e
	 *
	 * @throws \parseException
	 * @mixed - $e имееет не определенный тип одной из ошибок
	 */
	protected static function _returnNewTryAcceptError($e):array {

		if ($e instanceof cs_InviteIsDeclined) {
			return self::_getError(916, "invite was declined");
		}
		if ($e instanceof cs_InviteIsAccepted) {
			return self::_getError(914, "invite was accepted");
		}
		if ($e instanceof cs_InviteIsRevoked) {
			return self::_getError(913, "invite was revoked");
		}
		if ($e instanceof cs_InviteIsNotActive) {
			return self::_getError(911, "invite is not active");
		}
		if ($e instanceof cs_InviteStatusIsNotExpected) {
			return self::_getError(911, "invite status is not expected");
		}
		throw new ParseFatalException("Received unknown error in e");
	}

	/**
	 * возвращаем старые ошибки
	 *
	 * @param $e
	 *
	 * @throws \parseException
	 * @mixed  - $e имееет не определенный тип одной из ошибок
	 */
	protected static function _returnOldTryAcceptError($e):array {

		if ($e instanceof cs_InviteIsDeclined) {
			return self::_getError(911, "invite was declined");
		}
		if ($e instanceof cs_InviteIsAccepted) {
			return self::_getError(911, "invite was accepted");
		}
		if ($e instanceof cs_InviteIsRevoked) {
			return self::_getError(911, "invite was revoked");
		}
		if ($e instanceof cs_InviteIsNotActive) {
			return self::_getError(911, "invite is not active");
		}
		if ($e instanceof cs_InviteStatusIsNotExpected) {
			return self::_getError(911, "invite status is not expected");
		}
		throw new ParseFatalException("Received unknown error in e");
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получаем флаг необходимо ли отправлять системное сообщение о приглашении пользователя
	protected static function _isNeedSendSystemMessageAboutInvite(array $invite_row):bool {

		// отправляем, если пользователя еще не приглашали
		if (!isset($invite_row["invite_map"])) {
			return true;
		}

		// НЕ отправляем, если приглашение еще активно (пользователь может принять или отклонить)
		if ($invite_row["status"] == Type_Invite_Handler::STATUS_ACTIVE) {
			return false;
		}

		return true;
	}

	// отправляем системное сообщение о приглашении
	protected static function _sendSystemMessageForInvite(string $group_conversation_map, array $group_meta_row, int $user_id):void {

		// формируем системное сообщение о приглашении
		$system_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemUserInvitedToGroup($user_id);
		$is_silent      = Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnInviteAndJoin($group_meta_row["extra"]);

		// отправляем системное сообщение
		try {

			Helper_Conversations::addMessage(
				$group_conversation_map,
				$system_message,
				$group_meta_row["users"],
				$group_meta_row["type"],
				$group_meta_row["conversation_name"],
				$group_meta_row["extra"],
				is_silent: $is_silent
			);
		} catch (cs_ConversationIsLocked) {
			// nothing
		}
	}

	// если инвайт уже отклонен
	protected static function _throwIfInviteIsDeclined(string $status):void {

		if ($status == Type_Invite_Handler::STATUS_DECLINED) {
			throw new cs_InviteIsDeclined();
		}
	}

	// если инвайт уже принят
	protected static function _throwIfInviteIsAccepted(string $status):void {

		if ($status == Type_Invite_Handler::STATUS_ACCEPTED) {
			throw new cs_InviteIsAccepted();
		}

		if ($status == Type_Invite_Handler::STATUS_AUTO_ACCEPTED) {
			throw new cs_InviteIsAccepted();
		}
	}

	// если инвайт уже отклонен
	protected static function _throwIfInviteIsRevoked(string $status):void {

		if ($status == Type_Invite_Handler::STATUS_REVOKED) {
			throw new cs_InviteIsRevoked();
		}
	}

	// получаем приглашение если оно существует
	protected static function _tryGetInviteRowIfExist(string $invite_map):array {

		$invite_row = Type_Invite_Handler::get($invite_map);

		if (!isset($invite_row["invite_map"])) {
			throw new cs_InviteIsNotExist();
		}

		return $invite_row;
	}

	// если инвайт не активный
	protected static function _throwIfInviteIsNotActive(string $status):void {

		if ($status == Type_Invite_Handler::STATUS_INACTIVE) {
			throw new cs_InviteIsNotActive();
		}
	}

	// бросаем если диалог уже закрыт
	protected static function _throwIfConversationIsLocked(string $conversation_map):void {

		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		if ($dynamic_row["is_locked"] == 1) {
			throw new cs_ConversationIsLocked();
		}
	}

	// проверяем, что инвайт принадлежит юзеру
	protected static function _throwIfInviteNotMine(array $invite_row, int $user_id):void {

		if ($invite_row["user_id"] != $user_id) {
			throw new cs_InviteIsNotMine();
		}
	}

	// получаем ошибку
	protected static function _getError(int $error_code, string $error_message):array {

		return [
			"error_code" => $error_code,
			"message"    => $error_message,
		];
	}
}