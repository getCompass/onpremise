<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * крон для исполнения задач
 */
class Type_Phphooker_Worker {

	protected int $memory_limit = 50;

	// выполнить задачу
	// @long switch
	public function doTask(int $task_type, array $params):bool {

		// развилка по типу задачи
		switch ($task_type) {

			// обновление conversation_name группового диалога
			case Type_Phphooker_Main::TASK_TYPE_CHANGE_CONVERSATION_NAME:

				return $this->_doChangeConversationName(
					$params["conversation_map"],
					$params["conversation_name"],
					$params["users"]
				);

			// обновление avatar_file_map группового диалога
			case Type_Phphooker_Main::TASK_TYPE_CHANGE_CONVERSATION_AVATAR:

				return $this->_doChangeConversationAvatar($params["conversation_map"], $params["avatar_file_map"], $params["users"]);

			case Type_Phphooker_Main::TASK_TYPE_UPDATE_USER_DATA_ON_MESSAGE_ADD:

				$result = true;

				foreach ($params["users"] as $k => $v) {

					$temp   = $this->_updateUserDataOnMessageAdd($k, $params["conversation_map"], $params["message"], $params["messages_count"]);
					$result = $result && $temp;
				}

				return $result;

			// добавляем и отправляем сообщение в диалог
			case Type_Phphooker_Main::TASK_TYPE_ADD_MESSAGE_LIST:

				return $this->_addMessageList(
					$params["conversation_map"],
					$params["message_list"],
					$params["users"],
					$params["conversation_type"],
					$params["conversation_name"],
					$params["conversation_extra"],
					is_silent: $params["is_silent"]
				);

			// обновляем last_message пользователей при обновлении/удалении последнего сообщения
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_LAST_MESSAGE_ON_MESSAGE_UPDATE:

				$result = true;

				foreach ($params["users"] as $k => $_) {

					$temp   = $this->_updateLeftMenuOnMessageUpdate($k, $params["conversation_map"], $params["message"]);
					$result = $result && $temp;
				}

				return $result;

			// обновляем last_message пользователей при редактировании
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_LEFT_MENU_FOR_USER_ON_MESSAGE_EDIT:

				$result = true;

				foreach ($params["users"] as $k => $_) {

					$temp   = $this->_updateLeftMenuForUserOnMessageEdit(
						$k,
						$params["conversation_map"],
						$params["message"],
						$params["new_mentioned_user_id_list"]
					);
					$result = $result && $temp;
				}

				return $result;

			// обновляем last_message пользователя при установке сообщения последним
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_LAST_MESSAGE_ON_SET_MESSAGE_AS_LAST:

				return $this->_updateLeftMenuOnSetMessageAsLast($params["user_id"], $params["conversation_map"], $params["message"]);

			// обновляем last_message пользователей при обновлении/удалении последнего сообщения
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_LAST_MESSAGE_ON_MESSAGE_UPDATE_FOR_SYSTEM_DELETE:

				$result = true;

				foreach ($params["users"] as $k => $_) {

					$temp   = $this->_updateLeftMenuOnMessageUpdateForSystemDelete($k, $params["conversation_map"], $params["message"]);
					$result = $result && $temp;
				}

				return $result;

			// обновляем member_count для left_menu при изменении количества юзеров в группе
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_MEMBER_COUNT:

				// получаем мету диалога
				$meta_row = Type_Conversation_Meta::get($params["conversation_map"]);

				// считаем member_count
				$member_count = count($meta_row["users"]);
				$user_id_list = array_keys($meta_row["users"]);
				return $this->_updateMembersCountOnGroupMembersChange($user_id_list, $params["conversation_map"], $member_count);

			// помечаем удаленной запись с историей о репосте
			case Type_Phphooker_Main::TASK_TYPE_SET_DELETED_REPOST_REL:

				return $this->_doSetRepostRelMessageDeleted($params["conversation_map"], $params["message_map"]);

			// помечаем удаленной запись с историей о репосте из треда
			case Type_Phphooker_Main::TASK_TYPE_SET_DELETED_THREAD_REPOST_REL:

				return $this->_doSetThreadRepostRelMessageDeleted($params["thread_map"], $params["message_map"]);

			// помечаем что родительское сообщение треда удалено
			case Type_Phphooker_Main::TASK_TYPE_SET_PARENT_MESSAGE_IS_DELETED:

				return $this->_doSetParentMessageIsDeleted($params["thread_map"]);

			// отправляем сообщение с приглашением пользователю
			case Type_Phphooker_Main::TASK_TYPE_SEND_INVITE_TO_USER:

				return $this->_sendInviteToUser(
					$params["inviter_user_id"],
					$params["invited_user_id"],
					$params["invite_map"],
					$params["single_conversation_map"],
					$params["group_meta_row"],
					$params["is_need_send_system_message"],
					$params["platform"]);

			// обновляем allow_status_alias в левом меню
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_ALLOW_STATUS_ALIAS_IN_LEFT_MENU:

				return $this->_updateAllowStatusAliasInLeftMenu($params["allow_status"], $params["extra"], $params["conversation_map"], $params["user_id"],
					$params["opponent_user_id"]);

			// помечаем инвайты неактивными
			case Type_Phphooker_Main::TASK_TYPE_SET_INACTIVE_INVITE:

				return $this->_setInactiveAllUserInviteToConversation(
					$params["user_id"],
					$params["conversation_map"],
					$params["inactive_reason"],
					$params["is_remove_user"]
				);

			// помечаем инвайты отклоненными
			case Type_Phphooker_Main::TASK_TYPE_SET_DECLINE_INVITE:

				return $this->_setDeclinedAllUserInviteToConversation($params["user_id"], $params["conversation_map"]);

			// помечаем пользователя в sphinx удаленным при покидании того группы
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_PROFILE_DATA_TO_SPHINX_GROUP_MEMBER:
			case Type_Phphooker_Main::TASK_TYPE_SET_SPHINX_DELETED_USER_ON_LEAVE_GROUP:

				return true;

			// меняем роль пользователя в группе
			case Type_Phphooker_Main::TASK_TYPE_CHANGE_USER_ROLE_IN_GROUP:

				return $this->_changeUserRoleInGroup($params["conversation_map"], $params["user_id"], $params["role"]);

			// отписываемся от тредов
			case Type_Phphooker_Main::TASK_TYPE_DO_UNFOLLOW_THREAD_LIST:

				return $this->_doUnfollowThreadList(
					$params["user_id"],
					$params["thread_map_list"],
				);

			// помечаем что родительское сообщение тредов удалено
			case Type_Phphooker_Main::TASK_TYPE_SET_PARENT_MESSAGE_LIST_IS_DELETED:

				return $this->_doSetParentMessageListIsDeleted($params["thread_map_list"]);

			// пробуем отписать пользователя от всех тредов после покидания группы
			case Type_Phphooker_Main::TASK_TYPE_TRY_UNFOLLOW_THREAD_BY_CONVERSATION_MAP:

				return $this->_tryUnfollowThreadsByConversationMap($params["user_id"], $params["conversation_map"]);

			// обновление основной информации группового диалога
			case Type_Phphooker_Main::TASK_TYPE_CHANGE_GROUP_BASE_INFO:

				return $this->_doChangeGroupBaseInfo(
					$params["conversation_map"],
					$params["group_name"],
					$params["avatar_file_map"],
					$params["users"]
				);

			// обновление основной информации группового диалога
			case Type_Phphooker_Main::TASK_TYPE_CHANGE_HIDDEN_THREAD_ON_HIDDEN_CONVERSATION:

				return $this->_doHideOrShowThreadList(
					$params["user_id"],
					$params["thread_map_list"],
					$params["need_to_hide_parent_thread"]
				);

			// очистка кэша thread_meta
			case Type_Phphooker_Main::TASK_TYPE_CLEAR_THREAD_META_CACHE:

				$this->_doClearMetaThreadCache($params["source_parent_map"]);
				return true;

			// очистка кэша родительского сообщения треда
			case Type_Phphooker_Main::TASK_TYPE_CLEAR_PARENT_MESSAGE_CACHE:

				$this->_doClearParentMessageCache($params["message_map"]);
				return true;

			// очистка кэша родительского сообщения тредов
			case Type_Phphooker_Main::TASK_TYPE_CLEAR_PARENT_MESSAGE_LIST_CACHE:

				$this->_doClearParentMessageListCache($params["message_map_list"]);
				return true;

			// обновляем last_message пользователей при обновлении/удалении последнего сообщения
			case Type_Phphooker_Main::TASK_TYPE_UPDATE_LAST_MESSAGE_ON_DELETE_IF_DISABLED_SHOW_MESSAGE:

				$result = true;

				foreach ($params["users"] as $k => $_) {

					$temp   = $this->_updateLastMessageOnDeleteIfDisabledShowDeleteMessage($k, $params["conversation_map"], $params["message"]);
					$result = $result && $temp;
				}

				return $result;

			// обновляем last_message пользователей при отключении показа удаленных сообщений в диалоге
			case Type_Phphooker_Main::TASK_TYPE_DISABLE_SYSTEM_DELETED_MESSAGE_CONVERSATION:

				$result = true;

				foreach ($params["users"] as $k => $_) {

					$temp   = $this->_updateLastMessageIfDisabledShowDeleteMessage($k, $params["conversation_map"]);
					$result = $result && $temp;
				}

				return $result;

			default:
				throw new ParseFatalException("Unhandled task_type [$task_type] in " . __METHOD__);
		}
	}

	// -------------------------------------------------------
	// ЛОГИКА ВЫПОЛНЕНИЯ ЗАДАЧ
	// -------------------------------------------------------

	// обновление conversation_name группового диалога
	protected function _doChangeConversationName(string $conversation_map, string $conversation_name, array $users):bool {

		// проходимся по всем участникам диалога
		foreach ($users as $k => $_) {

			// обновляем название группового диалога в записях пользователя
			Type_Conversation_LeftMenu::onChangeName(
				$k,
				$conversation_map,
				$conversation_name
			);
		}

		return true;
	}

	/**
	 * обновление основной информации группового диалога (название, описание, аватарка)
	 *
	 * @throws \parseException
	 */
	protected function _doChangeGroupBaseInfo(string $conversation_map, string|false $group_name, string|false $avatar_file_map, array $users):bool {

		// проходимся по всем участникам диалога
		foreach ($users as $k => $_) {

			// обновляем описание группового диалога в записях пользователя
			Type_Conversation_LeftMenu::onChangeGroupBaseInfo(
				$k,
				$conversation_map,
				$group_name,
				$avatar_file_map
			);
		}

		return true;
	}

	// обновление avatar_file_map группового диалога
	protected function _doChangeConversationAvatar(string $conversation_map, string $avatar_file_map, array $users):bool {

		// проходимся по всем участникам диалога
		foreach ($users as $k => $_) {

			// обновляем avatar_file_map группового диалога в записях пользователя
			Type_Conversation_LeftMenu::onChangeAvatar(
				$k,
				$conversation_map,
				$avatar_file_map
			);
		}

		return true;
	}

	// обновить записи пользователя при добавлении сообщения в диалог
	// @long
	protected function _updateUserDataOnMessageAdd(int $user_id, string $conversation_map, array $message, int $messages_count):bool {

		Gateway_Db_CompanyConversation_Main::beginTransaction();
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return false;
		}

		// получаем conversation_message_index и message_map переданного сообщения
		$message_map                                = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);
		$current_message_conversation_message_index = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message_map);
		if (!$this->_isNeedDoTask($left_menu_row, $message)) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		// обновляем левое меню и таблицу диалогов сфинкса
		$set = $this->_makeSet($message, $user_id, $left_menu_row, $current_message_conversation_message_index);
		$set = $this->_incrementOrDecrementUnreadCount($user_id, $set, $message, $message_map, $left_menu_row, $current_message_conversation_message_index, $messages_count);

		// если мы ничего не обновляем, то откатываем
		if (count($set) == 0) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
		Gateway_Db_CompanyConversation_Main::commitTransaction();

		// отправляем ws об обновлении левого меню
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getOne($user_id, $conversation_map);
		$ws_users      = Type_Conversation_Message_Main::getHandler($message)::getUsers($message);
		$this->_sendWsLeftMenuUpdated($user_id, $left_menu_row, $ws_users);

		if (Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message) == $user_id) {
			return true;
		}

		// отправляем запрос для обновления баджа для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$conversation_map], false);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);

		return true;
	}

	// откатываем транзакцию если пользователь покинул группу
	protected function _isNeedDoTask(array $left_menu_row, array $message):bool {

		// проверяем может пользователь покинул диалог или заблокирован в системе
		if ($left_menu_row["is_leaved"] == 1 || $left_menu_row["allow_status_alias"] == Type_Conversation_Utils::ALLOW_STATUS_MEMBER_IS_DISABLED) {
			return false;
		}

		if (Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message) < $left_menu_row["clear_until"]) {
			return false;
		}
		return true;
	}

	// формируем массив для обновления
	protected function _makeSet(array $message, int $user_id, array $left_menu_row, int $current_message_conversation_message_index):array {

		// получаем conversation_message_index последнего сообщения из left_menu
		$last_message_conversation_message_index = Gateway_Db_CompanyConversation_UserLeftMenu::getConversationMessageIndex($left_menu_row["last_message"]);

		// если в last_message находится сообщение, отправленное после обрабатываемого
		if ($current_message_conversation_message_index <= $last_message_conversation_message_index) {

			// не обновляем последнее сообщение
			return [];
		}

		// формируем сообщение
		$last_message = $this->_makeLastMessage($message, $user_id);

		$output = ["last_message" => $last_message];

		$conversation_message_handler = Type_Conversation_Message_Main::getHandler($message);

		// если пользователь был упомянут - указываем это
		if ($conversation_message_handler::isUserMention($message, $user_id) && !Type_Conversation_Meta::isHiringConversation($left_menu_row["type"])) {

			$output["is_mentioned"]  = 1;
			$output["mention_count"] = $left_menu_row["mention_count"] + 1;
		}

		if ($left_menu_row["is_hidden"] == 1) {
			$output["is_hidden"] = 0;
		}

		// если пользователь отправитель инвайта то updated_at не обновляем
		if ($conversation_message_handler::getSenderUserId($message)
			&& $conversation_message_handler::getType($message) == CONVERSATION_MESSAGE_TYPE_INVITE) {
			return $output;
		}

		$output["updated_at"] = time();
		$output["version"]    = Domain_User_Entity_Conversation_LeftMenu::generateVersion($left_menu_row["version"]);

		return $output;
	}

	// инкрементим или декрементим количество непрочитанных
	protected function _incrementOrDecrementUnreadCount(int $user_id, array $set, array $message, string $message_map, array $left_menu_row, int $current_message_conversation_message_index, int $messages_count):array {

		// получаем message_index последнего прочитанного сообщения
		$last_read_conversation_message_index = $this->_getLastReadConversationMessageIndex($left_menu_row["last_read_message_map"]);

		// если сообщение уже было прочитано (либо мы его отправитель) - не стоит инкрементитить unread_count
		// это происходит потому что ws-ивент отправляется быстрее, чем задача в phphooker
		// и диалог не в муте
		if ($last_read_conversation_message_index >= $current_message_conversation_message_index) {

			$set["is_mentioned"] = 0;
			return $set;
		}

		$is_user_mentioned = Type_Conversation_Message_Main::getHandler($message)::isUserMention($message, $user_id);

		// если пользователь не был упомянут и чат в муте, то не инкрементим
		if (!$is_user_mentioned && ($left_menu_row["is_muted"] != 0 || $left_menu_row["muted_until"] >= $message["created_at"])) {
			return $set;
		}

		// для сообщений с конференцией ничего не делаем
		if (Type_Conversation_Message_Main::getHandler($message)::getType($message) == CONVERSATION_MESSAGE_TYPE_MEDIA_CONFERENCE
			&& in_array(Type_Conversation_Message_Main::getHandler($message)::getConferenceAcceptStatus($message), ["accepted"])) {

			return $set;
		}

		// если пользователь не является отправителем и это не чат найма(там получать инкремент должны все)
		if ($user_id != Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message)
			|| Type_Conversation_Message_Main::getHandler($message)::getType($message) == CONVERSATION_MESSAGE_TYPE_HIRING_REQUEST) {

			// если чат найма, то не инкрементим количество непрочитанных
			if (Type_Conversation_Meta::isHiringConversation($left_menu_row["type"])) {
				return $set;
			}

			// инкрементим
			return $this->_incrementUnreadCount($set, $user_id, $left_menu_row["unread_count"], $messages_count);
		} else {

			// если это отправитель и сообщение типа инвайт
			if ($user_id == Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message)
				&& Type_Conversation_Message_Main::getHandler($message)::getType($message) == CONVERSATION_MESSAGE_TYPE_INVITE) {

				return $set;
			}

			// иначе декрементим total_unread_count
			return $this->_decrementUnreadCount($set, $message_map, $left_menu_row["unread_count"], $user_id);
		}
	}

	// получаем индекс последнего прочитанного сообщения
	protected function _getLastReadConversationMessageIndex(string $last_read_message_map):int {

		if (mb_strlen($last_read_message_map) > 0) {
			$last_read_conversation_message_index = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($last_read_message_map);
		} else {
			$last_read_conversation_message_index = 0;
		}

		return $last_read_conversation_message_index;
	}

	// инкрементим количество непрочитанных
	protected function _incrementUnreadCount(array $set, int $user_id, int $unread_count, int $new_messages_count = 1):array {

		// инкрементим
		$set["unread_count"]   = "unread_count + " . $new_messages_count;
		$set["is_have_notice"] = 1;

		// инкрементим total_unread_conversations_count, если в чате не было прочитанных
		if ($unread_count == 0) {

			Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
				"message_unread_count"      => "message_unread_count + " . $new_messages_count,
				"conversation_unread_count" => "conversation_unread_count + 1",
				"updated_at"                => time(),
			]);

			return $set;
		}

		Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
			"message_unread_count" => "message_unread_count + " . $new_messages_count,
			"updated_at"           => time(),
		]);

		return $set;
	}

	// декрементим количество непрочитанных
	protected function _decrementUnreadCount(array $set, string $message_map, int $unread_count, int $user_id):array {

		$set["last_read_message_map"] = $message_map;

		// если есть что декрементить
		if ($unread_count > 0) {

			// декрементим total_unread_count и чистим нотисы/меншены
			$set["unread_count"]   = 0;
			$set["is_have_notice"] = 0;
			$set["is_mentioned"]   = 0;

			Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
				"message_unread_count"      => "message_unread_count - " . $unread_count,
				"conversation_unread_count" => "conversation_unread_count - 1",
				"updated_at"                => time(),
			]);
		}

		return $set;
	}

	// обновляем last_message в left_menu при установке сообщения последним
	protected function _updateLeftMenuOnSetMessageAsLast(int $user_id, string $conversation_map, array $message):bool {

		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getOne($user_id, $conversation_map);

		// если пользователь не состоит в группе
		if (!isset($left_menu_row["user_id"])) {
			return false;
		}

		// если после отправки сообщения диалог был почищен или юзер покинул диалог
		if (!$this->_isNeedUpdateLastMessage($message, $left_menu_row)) {
			return true;
		}

		// обновляем запись левого меню
		$left_menu_row["last_message"] = $this->_makeLastMessage($message, $user_id);

		$set = ["last_message" => $left_menu_row["last_message"]];

		$left_menu_row["version"] = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);

		$this->_sendWsLeftMenuUpdated($user_id, $left_menu_row);
		return true;
	}

	// проверяет может диалог уже был почищен или пользователь покинул диалог
	protected function _isNeedUpdateLastMessage(array $message, array $left_menu_row):bool {

		// если сообщение было отправлено до последней очистки диалога
		$created_at = Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message);
		if ($created_at < $left_menu_row["clear_until"]) {
			return false;
		}

		// проверяем, быть может пользователь покинул диалог
		if ($left_menu_row["is_leaved"] == 1) {
			return false;
		}

		return true;
	}

	// обновляем last_message в left_menu при обновлении сообщения
	// @long
	protected function _updateLeftMenuOnMessageUpdate(int $user_id, string $conversation_map, array $message):bool {

		// получаем запись из левого меню
		Gateway_Db_CompanyConversation_Main::beginTransaction();
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return false;
		}

		// проверяем, быть может пользователь покинул диалог
		if ($left_menu_row["is_leaved"] == 1) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		// получаем индексы сообщений, и обновляем запись левого меню если нужно
		$current_message_conversation_message_index = $this->_getCurrentMessageConversationIndex($message);
		$last_message_conversation_message_index    = $this->_getLastMessageConversationIndex($left_menu_row["last_message"]);

		$set = [];
		if (Type_Conversation_Message_Main::getHandler($message)::isUserMention($message, $user_id)) {

			$mention_count        = $left_menu_row["mention_count"] - 1;
			$set["mention_count"] = $mention_count;
			$set["updated_at"]    = time();

			$message_type = Type_Conversation_Message_Main::getHandler($message)::getType($message);
			if ($message_type === CONVERSATION_MESSAGE_TYPE_DELETED) {

				if ($mention_count < 1) {
					$set["is_mentioned"] = 0;
				}
			}
		}

		// если пользователь сейчас упомянут - но в отредактированном сообщении его измениили, снимем
		if ($left_menu_row["is_mentioned"] == 1 && !Type_Conversation_Message_Main::getHandler($message)::isUserMention($message, $user_id)) {

			$mention_count        = $left_menu_row["mention_count"] - 1;
			$set["mention_count"] = $mention_count;
			$set["updated_at"]    = time();

			if ($mention_count < 1) {
				$set["is_mentioned"] = 0;
			}
		}

		// если менялось не последнее сообщение в чате
		if (!Type_Conversation_Utils::isExistLastMessage($left_menu_row) || !$this->_isNeedUpdateLeftMenuOnMessageUpdate($current_message_conversation_message_index, $last_message_conversation_message_index)) {

			// если меняли меншен, обновленяем его
			if (isset($set["mention_count"]) && ($set["mention_count"] != $left_menu_row["mention_count"])) {

				$left_menu_row            = array_merge($left_menu_row, $set);
				$left_menu_row["version"] = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
				Gateway_Db_CompanyConversation_Main::commitTransaction();

				$this->_sendWsLeftMenuUpdated($user_id, $left_menu_row);
				return true;
			}

			// иначе просто выходим
			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		$last_message        = $this->_makeLastMessage($message, $user_id);
		$set["last_message"] = $last_message;

		$left_menu_row            = array_merge($left_menu_row, $set);
		$left_menu_row["version"] = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
		Gateway_Db_CompanyConversation_Main::commitTransaction();

		$this->_sendWsLeftMenuUpdated($user_id, $left_menu_row);

		return true;
	}

	// обновляем last_message в left_menu при изменении текста сообщения
	protected function _updateLeftMenuForUserOnMessageEdit(int $user_id, string $conversation_map, array $message, array $new_mentioned_user_id_list):bool {

		// если пользователь не упомянут обрабатываем как обычное обновление левого меню
		if (!in_array($user_id, $new_mentioned_user_id_list)) {
			return $this->_updateLeftMenuOnMessageUpdate($user_id, $conversation_map, $message);
		}

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		// получаем запись из левого меню
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return false;
		}

		// проверяем, быть может пользователь покинул диалог
		if ($left_menu_row["is_leaved"] == 1 || Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message) < $left_menu_row["clear_until"]) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		// получаем индекс текущего сообщения
		$current_message_conversation_message_index = $this->_getCurrentMessageConversationIndex($message);

		// формируем set если пользователь упомянут при редактировании
		$set = $this->_makeSetForUserWhichMentionedInEditedMessage($user_id, $message, $left_menu_row, $current_message_conversation_message_index);

		if (count($set) < 1) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
		Gateway_Db_CompanyConversation_Main::commitTransaction();

		// отправляем ws об обновлении левого меню
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getOne($user_id, $conversation_map);
		$this->_sendWsLeftMenuUpdated($user_id, $left_menu_row);

		return true;
	}

	// формируем set если пользователь упомянут при редактировании
	// update the left menu for user which mentioned in edited message
	protected function _makeSetForUserWhichMentionedInEditedMessage(int $user_id, array $message, array $left_menu_row, int $current_message_conversation_message_index):array {

		$set = [
			"is_mentioned"  => 1,
			"mention_count" => $left_menu_row["mention_count"] + 1,
			"updated_at"    => time(),
		];

		$last_message_conversation_message_index = $this->_getLastMessageConversationIndex($left_menu_row["last_message"]);
		if ($this->_isNeedUpdateLeftMenuOnMessageUpdate($current_message_conversation_message_index, $last_message_conversation_message_index)) {
			$set["last_message"] = $this->_makeLastMessage($message, $user_id);
		}

		if ($user_id == Type_Conversation_Message_Main::getHandler($message)::getSenderUserId($message)) {
			return $set;
		}

		if ($left_menu_row["unread_count"] != 0) {
			return $set;
		}

		// это кейс если сообщения были прочитаны
		return $this->_incrementUnreadCount($set, $user_id, $left_menu_row["unread_count"]);
	}

	// обновляем last_message в left_menu при системном удалении сообщения
	// @long
	protected function _updateLeftMenuOnMessageUpdateForSystemDelete(int $user_id, string $conversation_map, array $message):bool {

		// получаем запись из левого меню
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getOne($user_id, $conversation_map);
		if (!isset($left_menu_row["user_id"])) {
			return false;
		}

		// проверяем, быть может пользователь покинул диалог
		if ($left_menu_row["is_leaved"] == 1) {
			return true;
		}

		// получаем индексы сообщений, и обновляем запись левого меню если нужно
		$current_message_conversation_message_index = $this->_getCurrentMessageConversationIndex($message);
		$last_message_conversation_message_index    = $this->_getLastMessageConversationIndex($left_menu_row["last_message"]);
		if ($this->_isNeedUpdateLeftMenuOnMessageUpdate($current_message_conversation_message_index, $last_message_conversation_message_index)) {

			$last_message = $this->_makeLastMessage($message, $user_id);
			$set          = ["last_message" => $last_message];

			// обновляем левое меню
			$version                       = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
			$left_menu_row["last_message"] = $last_message;
			$left_menu_row["version"]      = $version;
		}

		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getOne($conversation_map);

		// форматируем запись левого меню
		$prepared_left_menu_row  = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);
		$formatted_left_menu_row = Apiv1_Format::leftMenu($prepared_left_menu_row);

		// отправляем ws-ивент
		$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);
		Gateway_Bus_Sender::conversationMessageHiddenList(
			$talking_user_list, [$message["message_map"]], $conversation_map, $formatted_left_menu_row, $dynamic->messages_updated_version
		);

		// отправляем ws об обновлении левого меню
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getOne($user_id, $conversation_map);
		$this->_sendWsLeftMenuUpdated($user_id, $left_menu_row);

		return true;
	}

	// обновляем members_count для left_menu при изменении количества юзеров в групповом диалоге
	protected function _updateMembersCountOnGroupMembersChange(array $user_id_list, string $conversation_map, int $members_count):bool {

		// обновляем эту запись
		Domain_User_Action_Conversation_UpdateLeftMenu::doUserIdList($user_id_list, $conversation_map, [
			"member_count" => $members_count,
		]);
		return true;
	}

	// помечаем удаленной запись с историей о репосте
	protected function _doSetRepostRelMessageDeleted(string $conversation_map, string $message_map):bool {

		// помечаем удаленной запись
		Type_Conversation_RepostRel::setMessageDeleted($conversation_map, $message_map);

		return true;
	}

	// помечаем удаленной запись с историей о репосте из треда
	protected function _doSetThreadRepostRelMessageDeleted(string $thread_map, string $message_map):bool {

		// отправляем сокет запрос на php_thread
		[$status] = Gateway_Socket_Thread::doCall("threads.setRepostRelDeleted", [
			"thread_map"  => $thread_map,
			"message_map" => $message_map,
		]);

		return true;
	}

	// помечаем что родительское сообщение треда удалено
	protected function _doSetParentMessageIsDeleted(string $thread_map):bool {

		// отправляем сокет запрос на php_thread
		[$status] = Gateway_Socket_Thread::doCall("threads.setParentMessageIsDeleted", [
			"thread_map" => $thread_map,
		]);

		return true;
	}

	// помечаем что родительское сообщение треда удалено
	protected function _doSetParentMessageListIsDeleted(array $thread_map_list):bool {

		// отправляем сокет запрос на php_thread
		[$status] = Gateway_Socket_Thread::doCall("threads.setParentMessageIsDeletedByThreadMapList", [
			"thread_map_list" => $thread_map_list,
		]);

		return true;
	}

	// отправляем сообщение с приглашением пользователю
	protected function _sendInviteToUser(int $inviter_user_id, int $invited_user_id, string $invite_map, string $single_conversation_map, array $group_meta_row, bool $is_need_send_system_message, string $platform):bool {

		$single_meta_row = Type_Conversation_Meta::get($single_conversation_map);

		// пользователи, для которых не отправляем ws-ивент
		$not_send_ws_event_user_list = [];

		// если у приглашающего отсутствует single-диалог с приглашенным в левом меню или он спрятан
		$left_menu_row = Type_Conversation_LeftMenu::get($inviter_user_id, $single_conversation_map);
		if (!isset($left_menu_row["user_id"]) || $left_menu_row["is_hidden"] == 1) {
			$not_send_ws_event_user_list = [$inviter_user_id];
		}

		try {
			Helper_Conversations::checkIsAllowed($single_conversation_map, $single_meta_row, $inviter_user_id);
		} catch (cs_Conversation_MemberIsDisabled | Domain_Conversation_Exception_User_IsAccountDeleted | cs_Conversation_UserbotIsDisabled | cs_Conversation_UserbotIsDeleted | Domain_Conversation_Exception_Guest_AttemptInitialConversation) {
			return false;
		}

		$is_success = $this->_addMessageWithInvite($single_conversation_map, $inviter_user_id, $invite_map, $single_meta_row, $platform, $not_send_ws_event_user_list);
		if (!$is_success) {
			return false;
		}

		if ($is_need_send_system_message && !Type_Conversation_Meta_Users::isMember($invited_user_id, $group_meta_row["users"])) {

			$is_success = $this->_sendSystemMessageForInvite($group_meta_row["conversation_map"], $group_meta_row, $invited_user_id);
			if (!$is_success) {
				return false;
			}
		}
		return true;
	}

	// пометить приглашение неактивным
	protected function _setInactiveAllUserInviteToConversation(int $user_id, string $conversation_map, int $inactive_reason, bool $is_remove_user):bool {

		try {
			Helper_Invites::setInactiveAllUserInviteToConversation($user_id, $conversation_map, $inactive_reason, $is_remove_user);
		} catch (cs_InviteStatusIsNotExpected | ReturnFatalException) {
			return false;
		}

		return true;
	}

	// пометить приглашение отклоненным
	protected function _setDeclinedAllUserInviteToConversation(int $user_id, string $conversation_map):bool {

		try {
			Helper_Invites::setDeclinedAllUserInviteToConversation($user_id, $conversation_map);
		} catch (cs_InviteStatusIsNotExpected | ReturnFatalException) {
			return false;
		}

		return true;
	}

	// добавляем сообщение с приглашением в single-диалог
	protected function _addMessageWithInvite(string $single_conversation_map, int $sender_user_id, string $invite_map, array $single_meta_row, string $platform, array $not_send_ws_event_user_list):bool {

		$invite_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeInvite($sender_user_id, $invite_map, $platform);

		return $this->_addMessageList(
			$single_conversation_map,
			[$invite_message],
			$single_meta_row["users"],
			$single_meta_row["type"],
			$single_meta_row["conversation_name"],
			$single_meta_row["extra"],
			$not_send_ws_event_user_list
		);
	}

	// отправляем системное сообщение о приглашении
	protected function _sendSystemMessageForInvite(string $group_conversation_map, array $group_meta_row, int $user_id):bool {

		// формируем системное сообщение о приглашении
		$system_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemUserInvitedToGroup($user_id);
		$is_silent      = Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnInviteAndJoin($group_meta_row["extra"]);

		// отправляем системное сообщение
		return $this->_addMessageList(
			$group_conversation_map,
			[$system_message],
			$group_meta_row["users"],
			$group_meta_row["type"],
			$group_meta_row["conversation_name"],
			$group_meta_row["extra"],
			is_silent: $is_silent
		);
	}

	// обновляем allow_status_alias в левом меню
	protected function _updateAllowStatusAliasInLeftMenu(int $allow_status, array $extra, string $conversation_map, int $user_id, int $opponent_user_id):bool {

		// обновляем поле allow_status_alias для самого пользователя
		$allow_status_alias = Type_Conversation_Utils::getAllowStatus($allow_status, $extra, $opponent_user_id);
		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, ["allow_status_alias" => $allow_status_alias]);

		return true;
	}

	// меняем роль пользователя в группе
	protected function _changeUserRoleInGroup(string $conversation_map, int $user_id, int $role):bool {

		Helper_Groups::setRole($conversation_map, $user_id, $role);

		return true;
	}

	// отписываем пользователя от тредов асинхронно
	protected function _doUnfollowThreadList(int $user_id, array $thread_map_list):bool {

		// выполняем сокет-запрос на dpc треда, чтобы отписаться от треда
		[$status] = Gateway_Socket_Thread::doCall("threads.doUnfollowThreadList", [
			"thread_map_list" => $thread_map_list,
		], $user_id);

		return true;
	}

	// скрываем/открываем у пользователя треды асинхронно
	protected function _doHideOrShowThreadList(int $user_id, array $thread_map_list, bool $need_to_hide_parent_thread):bool {

		// выполняем сокет-запрос на dpc треда, чтобы отписаться от треда
		[$status] = Gateway_Socket_Thread::doCall("threads.setParentMessageIsHiddenOrUnhiddenByThreadMapList", [
			"thread_map_list"            => $thread_map_list,
			"need_to_hide_parent_thread" => $need_to_hide_parent_thread,
		], $user_id);

		return $status === "ok";
	}

	// пробуем отписать пользователя от всех тредов в группе после выхода из нее
	protected function _tryUnfollowThreadsByConversationMap(int $user_id, string $conversation_map):bool {

		// выполняем сокет-запрос на dpc пользователя, чтобы отписаться от тредов по родителю
		[$status] = Gateway_Socket_Thread::doCall("threads.doUnfollowThreadListByConversationMap", [
			"conversation_map" => $conversation_map,
		], $user_id);

		return true;
	}

	/**
	 * обновляем last_message в left_menu при обновлении сообщения
	 *
	 * @return bool
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @long много проверок
	 */
	protected function _updateLastMessageOnDeleteIfDisabledShowDeleteMessage(int $user_id, string $conversation_map, array $message):bool {

		$message_type = Type_Conversation_Message_Main::getHandler($message)::getType($message);
		if ($message_type !== CONVERSATION_MESSAGE_TYPE_DELETED) {
			return true;
		}

		// получаем запись из левого меню
		Gateway_Db_CompanyConversation_Main::beginTransaction();
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return false;
		}

		// проверяем, быть может пользователь покинул диалог
		if ($left_menu_row["is_leaved"] == 1) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		// проверяем не пустой ли last_message
		if (!isset($left_menu_row["last_message"]["message_map"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		// если пользователь не может просматривать сообщение (например скрыл его)
		if (Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		$conversation_dynamic = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		if ($conversation_dynamic["last_block_id"] < 1) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		$clear_until = Domain_Conversation_Entity_Dynamic::getClearUntil($conversation_dynamic["user_clear_info"], $conversation_dynamic["conversation_clear_info"], $user_id);

		// если дата создания сообщения раньше, чем отметка до которой пользователь очистил диалог
		if (Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message) < $clear_until) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		// получаем индексы сообщений, и обновляем запись левого меню если нужно
		$current_message_conversation_message_index = $this->_getCurrentMessageConversationIndex($message);
		$last_message_conversation_message_index    = $this->_getLastMessageConversationIndex($left_menu_row["last_message"]);

		$set                 = [];
		$set["last_message"] = [];

		if (Type_Conversation_Message_Main::getHandler($message)::isUserMention($message, $user_id)) {

			$mention_count        = $left_menu_row["mention_count"] - 1;
			$set["mention_count"] = $mention_count;
			$set["updated_at"]    = time();

			if ($mention_count < 1) {
				$set["is_mentioned"] = 0;
			}
		}

		// если пользователь сейчас упомянут - но в отредактированном сообщении его измениили, снимем
		if ($left_menu_row["is_mentioned"] == 1 && !Type_Conversation_Message_Main::getHandler($message)::isUserMention($message, $user_id)) {

			$mention_count        = $left_menu_row["mention_count"] - 1;
			$set["mention_count"] = $mention_count;
			$set["updated_at"]    = time();

			if ($mention_count < 1) {
				$set["is_mentioned"] = 0;
			}
		}

		// если менялось не последнее сообщение в чате
		if (!Type_Conversation_Utils::isExistLastMessage($left_menu_row) || !$this->_isNeedUpdateLeftMenuOnMessageUpdate($current_message_conversation_message_index, $last_message_conversation_message_index)) {

			// если меняли меншен, обновленяем его
			if (isset($set["mention_count"]) && ($set["mention_count"] != $left_menu_row["mention_count"])) {

				$left_menu_row            = array_merge($left_menu_row, $set);
				$left_menu_row["version"] = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
				Gateway_Db_CompanyConversation_Main::commitTransaction();

				$this->_sendWsLeftMenuUpdated($user_id, $left_menu_row);
				return true;
			}

			// иначе просто выходим
			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		$left_menu_row            = array_merge($left_menu_row, $set);
		$left_menu_row["version"] = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
		Gateway_Db_CompanyConversation_Main::commitTransaction();

		$this->_sendWsLeftMenuUpdated($user_id, $left_menu_row);

		return true;
	}

	/**
	 * обновляем last_message в left_menu при отключении показа удаленных сообщений
	 *
	 * @return bool
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_Message_IsNotExist
	 * @long много проверок
	 */
	protected function _updateLastMessageIfDisabledShowDeleteMessage(int $user_id, string $conversation_map):bool {

		// получаем запись из левого меню
		Gateway_Db_CompanyConversation_Main::beginTransaction();
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return false;
		}

		// проверяем, быть может пользователь покинул диалог
		if ($left_menu_row["is_leaved"] == 1) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		// проверяем не пустой ли last_message
		if (!isset($left_menu_row["last_message"]["message_map"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);
		$block_row   = Domain_Conversation_Entity_Message_Block_Get::getBlockRow($conversation_map, $left_menu_row["last_message"]["message_map"], $dynamic_row, true);
		$message     = Domain_Conversation_Entity_Message_Block_Message::get($left_menu_row["last_message"]["message_map"], $block_row);

		$message_type = Type_Conversation_Message_Main::getHandler($message)::getType($message);
		if ($message_type !== CONVERSATION_MESSAGE_TYPE_DELETED) {
			return true;
		}

		$set["last_message"] = [];

		if (Type_Conversation_Message_Main::getHandler($message)::isUserMention($message, $user_id)) {

			$mention_count        = $left_menu_row["mention_count"] - 1;
			$set["mention_count"] = $mention_count;
			$set["updated_at"]    = time();

			if ($mention_count < 1) {
				$set["is_mentioned"] = 0;
			}
		}

		// если пользователь сейчас упомянут - но в отредактированном сообщении его измениили, снимем
		if ($left_menu_row["is_mentioned"] == 1 && !Type_Conversation_Message_Main::getHandler($message)::isUserMention($message, $user_id)) {

			$mention_count        = $left_menu_row["mention_count"] - 1;
			$set["mention_count"] = $mention_count;
			$set["updated_at"]    = time();

			if ($mention_count < 1) {
				$set["is_mentioned"] = 0;
			}
		}

		// если нету last_message в левом меню
		if (!Type_Conversation_Utils::isExistLastMessage($left_menu_row)) {

			// если меняли меншен, обновленяем его
			if (isset($set["mention_count"]) && ($set["mention_count"] != $left_menu_row["mention_count"])) {

				$left_menu_row            = array_merge($left_menu_row, $set);
				$left_menu_row["version"] = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
				Gateway_Db_CompanyConversation_Main::commitTransaction();

				$this->_sendWsLeftMenuUpdated($user_id, $left_menu_row);
				return true;
			}

			// иначе просто выходим
			Gateway_Db_CompanyConversation_Main::rollback();
			return true;
		}

		$left_menu_row            = array_merge($left_menu_row, $set);
		$left_menu_row["version"] = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $conversation_map, $set);
		Gateway_Db_CompanyConversation_Main::commitTransaction();

		$this->_sendWsLeftMenuUpdated($user_id, $left_menu_row);

		return true;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// добавляем и отправляем список сообщений в диалог
	protected function _addMessageList(string $conversation_map, array $message_list, array $users, int $conversation_type, string $conversation_name, array $conversation_extra, array $not_send_ws_event_user_list = [], bool $is_silent = true):bool {

		try {

			Helper_Conversations::addMessageList(
				$conversation_map,
				$message_list,
				$users,
				$conversation_type,
				$conversation_name,
				$conversation_extra,
				true,
				$is_silent,
				$not_send_ws_event_user_list
			);
		} catch (cs_ConversationIsLocked) {
			return false;
		}

		return true;
	}

	// формируем объект last_message
	// @long - большая структура для формирования сообщения
	protected function _makeLastMessage(array $message, int $user_id):array {

		$last_message = Type_Conversation_LeftMenu::makeLastMessage($message);

		// если сообщение скрыто
		if (Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
			$last_message = [];
		}

		return $last_message;
	}

	// нужно ли обновлять левое меню при обновлении сообщения (изменении текста сообщения, удалении/скрытии сообщения)
	protected function _isNeedUpdateLeftMenuOnMessageUpdate(int $current_message_conversation_message_index, int $last_message_conversation_message_index):bool {

		// если conversation_message_index равен нулю, значит last_message пуст
		if ($last_message_conversation_message_index == 0) {
			return false;
		}

		// если conversation_message_index обновленного сообщения меньше того, что записан в last_message
		if ($current_message_conversation_message_index < $last_message_conversation_message_index) {
			return false;
		}

		return true;
	}

	// получаем conversation_message_index текущего сообщения
	protected function _getCurrentMessageConversationIndex(array $message):int {

		$message_map = Type_Conversation_Message_Main::getHandler($message)::getMessageMap($message);

		return \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message_map);
	}

	// получаем conversation_message_index последнего сообщения
	protected function _getLastMessageConversationIndex(array $last_message):int {

		return Gateway_Db_CompanyConversation_UserLeftMenu::getConversationMessageIndex($last_message);
	}

	// подготавливаем и отправляем ws об обновлении левого меню
	protected function _sendWsLeftMenuUpdated(int $user_id, array $left_menu_row, array $ws_users = []):void {

		if (Type_Conversation_Utils::isExistLastMessage($left_menu_row)) {
			$ws_users = array_unique(array_merge($ws_users, Type_Conversation_Utils::getLastMessageUsers($left_menu_row["last_message"])));
		}

		$prepared_left_menu  = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);
		$formatted_left_menu = Apiv1_Format::leftMenu($prepared_left_menu);

		$last_message_type   = isset($left_menu_row["last_message"]["type"]) ? $left_menu_row["last_message"]["type"] : null;
		$formatted_left_menu = Apiv1_Format::prepareFormattedLeftMenuForNewClient($formatted_left_menu, $left_menu_row["type"], $last_message_type);

		Gateway_Bus_Sender::conversationLeftMenuUpdated($user_id, $formatted_left_menu, $ws_users);
	}

	// чистим в треде meta cache
	protected function _doClearMetaThreadCache(string $source_parent_map):void {

		// очищаем кэш
		Gateway_Socket_Thread::doClearMetaThreadCache($source_parent_map);
	}

	// чистим кэш родительского сообщения треда
	protected function _doClearParentMessageCache(string $parent_message_map):void {

		Gateway_Socket_Thread::doClearParentMessageCache($parent_message_map);
	}

	// чистим кэш родительского сообщения тредов
	protected function _doClearParentMessageListCache(array $parent_message_map_list):void {

		Gateway_Socket_Thread::doClearParentMessageListCache($parent_message_map_list);
	}
}