<?php

namespace Compass\Conversation;

/**
 * контроллер для сокет методов класса invite
 */
class Socket_Invites extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"createInviteForUserDpc",
		"updateInviteForUserDpc",
		"trySendToGroupBatching",
		"revokeByDestination",
		"getInvitedUserListByDestination",
		"clearInvitesForUser",
		"checkClearInvitesForUser",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	// создаем запись с инвайтом
	public function createInviteForUserDpc():array {

		$user_id                = $this->post("?i", "user_id");
		$invite_map             = $this->post("?s", "invite_map");
		$status                 = $this->post("?i", "status");
		$created_at             = $this->post("?i", "created_at");
		$sender_user_id         = $this->post("?i", "sender_user_id");
		$group_conversation_map = $this->post("?s", "group_conversation_map");

		// формируем запись с инвайтом
		$invite_row = [
			"user_id"                => $user_id,
			"invite_map"             => $invite_map,
			"status"                 => $status,
			"created_at"             => $created_at,
			"updated_at"             => null,
			"sender_user_id"         => $sender_user_id,
			"group_conversation_map" => $group_conversation_map,
		];

		Type_Invite_Single::insertInviteDataForUserInviteRel($invite_row);

		return $this->ok();
	}

	// обновляем запись с инвайтом
	public function updateInviteForUserDpc():array {

		$user_id         = $this->post("?i", "user_id");
		$invite_map      = $this->post("?s", "invite_map");
		$expected_status = $this->post("?i", "expected_status");
		$status          = $this->post("?i", "status");
		$updated_at      = $this->post("?i", "updated_at");

		Type_Invite_Single::updateInviteDataForUserInvite($user_id, $invite_map, $expected_status, $status, $updated_at);

		return $this->ok();
	}

	// отправляем инвайты в множество диалогов для одного юзера
	public function trySendToGroupBatching():array {

		$user_id                = $this->post("?i", "user_id");
		$sender_user_id         = $this->post("?i", "sender_user_id");
		$conversation_map_list  = $this->post("?a", "conversation_map_list");
		$conversation_meta_list = $this->post("?a", "conversation_meta_list");

		// получаем count_sender_active_invite_list
		$count_sender_active_invite_list = Type_Invite_Single::getAllCountSenderActiveInviteListForGroupList($sender_user_id, $conversation_map_list);

		// отправляем приглашение в каждую групп, если возможно
		$output = $this->_sendInviteToEveryGroupIfIsPossible($conversation_meta_list, $user_id, $sender_user_id, $count_sender_active_invite_list);

		return $this->ok($output);
	}

	// отправляем приглашение в каждую группу
	protected function _sendInviteToEveryGroupIfIsPossible(array $conversation_meta_list, int $sender_user_id, int $user_id, array $count_sender_active_invite_list):array {

		$output = $this->_makeOutputForTrySendBatchingForGroups();
		foreach ($conversation_meta_list as $v) {

			// если нельзя отправить инвайт - записываем ошибку
			$error = $this->_getErrorIfNotSendInviteToGroup($v["type"], $sender_user_id, $v["users"], $v["conversation_map"], $count_sender_active_invite_list);
			if (count($error) > 0) {

				$output["list_error"][] = $error;
				continue;
			}

			$output["list_ok"][] = $this->_makeListOkItemForTrySendBathingGroups($v["conversation_map"]);
		}

		// если невозможно отправить хоть один инвайт - не отправляем все инвайты
		if (count($output["list_error"]) > 0) {

			$output["is_sent"] = (int) 0;
			return $output;
		}

		$this->_sendInviteToEveryGroup($conversation_meta_list, $sender_user_id, $user_id);
		return $output;
	}

	// создаем output для метода trySendBatchingForGroups
	protected function _makeOutputForTrySendBatchingForGroups():array {

		return [
			"is_sent"    => (int) 1,
			"list_ok"    => [],
			"list_error" => [],
		];
	}

	// получаем ошибку, если не удалось отправить инвайт
	protected function _getErrorIfNotSendInviteToGroup(int $type, int $sender_user_id, array $users, string $conversation_map, array $count_sender_active_invite_list):array {

		$member = Gateway_Bus_CompanyCache::getMember($sender_user_id);

		// если диалог не является групповым
		if (!Type_Conversation_Meta::isSubtypeOfGroup($type)) {
			return $this->_makeErrorForTrySendBathingForGroups($conversation_map, 400, "Conversation is not a group");
		}

		// если юзер не участник диалога
		if (!Type_Conversation_Meta_Users::isMember($sender_user_id, $users)) {
			return $this->_makeErrorForTrySendBathingForGroups($conversation_map, 501, "User is not conversation member");
		}

		// если юзер не может отправлять инвайт (не позволяет роль)
		if (!Type_Conversation_Meta_Users::isGroupAdmin($member->user_id, $users)) {
			return $this->_makeErrorForTrySendBathingForGroups($conversation_map, 902, "You are not allowed to do this action");
		}

		// если запись счечика для данного диалога существует и достигнут лимит отправки активных инвайтов в данную группу для отправителя инвайта
		if (isset($count_sender_active_invite_list[$conversation_map])
			&& $count_sender_active_invite_list[$conversation_map]["count_sender_active_invite"] == Type_Invite_Handler::getSendActiveInviteLimit()) {
			return $this->_makeErrorForTrySendBathingForGroups($conversation_map, 915, "Active invite send limit exceeded");
		}

		return [];
	}

	// формируем ответ с error_code для trySendBatchingForGroups
	protected function _makeErrorForTrySendBathingForGroups(string $conversation_map, int $error_code, string $error_message):array {

		return [
			"conversation_map" => (string) $conversation_map,
			"error_code"       => (int) $error_code,
			"message"          => (string) $error_message,
		];
	}

	// формируем ответ для list_ok для trySendBatchingForGroups
	protected function _makeListOkItemForTrySendBathingGroups(string $group_conversation_map):array {

		return [
			"conversation_map" => (string) $group_conversation_map,
		];
	}

	// отправляем инвайты
	protected function _sendInviteToEveryGroup(array $conversation_meta_list, int $sender_user_id, int $user_id):void {

		// отправляем инвайт в каждую группу ассинхронно
		foreach ($conversation_meta_list as $v) {

			try {
				Helper_Invites::inviteUserFromSingleWithAsyncMessages($sender_user_id, $user_id, $v);
			} catch (cs_InviteIsDuplicated) {
				continue;
			}
		}
	}

	# region invitation

	/**
	 * Метод для деактивации приглашений
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function clearInvitesForUser():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$limit   = $this->post(\Formatter::TYPE_INT, "limit", 500);
		$offset  = $this->post(\Formatter::TYPE_INT, "offset", 0);

		$is_complete = Domain_Conversation_Scenario_Socket::clearInvitesForUser($user_id, $limit, $offset);
		return $this->ok([
			"is_complete" => (bool) $is_complete,
		]);
	}

	/**
	 * Проверяем что у пользователя нет активных приглашений
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function checkClearInvitesForUser():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");
		$limit   = $this->post(\Formatter::TYPE_INT, "limit", 500);
		$offset  = $this->post(\Formatter::TYPE_INT, "offset", 0);

		$is_cleared = Domain_Conversation_Scenario_Socket::checkClearInvitesForUser($user_id, $limit, $offset);
		return $this->ok([
			"is_cleared" => (bool) $is_cleared,
		]);
	}

	# endregion invitation
}
