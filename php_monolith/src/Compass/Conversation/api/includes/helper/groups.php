<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Struct\Short;

/**
 * хелпер для групп
 */
class Helper_Groups {

	/**
	 * Создаем группу
	 *
	 * @param int    $user_id
	 * @param string $group_name
	 * @param string $file_map
	 * @param string $description
	 * @param int    $group_type
	 * @param bool   $is_favorite
	 * @param bool   $is_mentioned
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 */
	public static function create(int $user_id, string $group_name, string $file_map = "", string $description = "",
						int $group_type = CONVERSATION_TYPE_GROUP_DEFAULT, bool $is_favorite = false, bool $is_mentioned = false):array {

		// если пришел неадекватный тип диалога для группы
		if (!Type_Conversation_Meta::isSubtypeOfGroup($group_type)) {
			throw new ParseFatalException("Incorrect type of group");
		}

		// создаем групповой диалог
		$meta_row = Type_Conversation_Group::add($user_id, $group_name, $group_type, $is_favorite, $is_mentioned, $file_map, $description);

		// отправляем событие пользователю, что добавлен диалог в левом меню
		Gateway_Bus_Sender::conversationAdded($user_id, $meta_row["conversation_map"]);

		// инкрементим количество действий
		Domain_User_Action_IncActionCount::incGroupCreated($user_id, $meta_row["conversation_map"]);

		return $meta_row;
	}

	/**
	 * Вступаем в групповой диалог
	 *
	 * @throws ParseFatalException
	 */
	public static function doJoin(string $conversation_map, int $user_id, int|false $member_role = false, int|false $member_permissions = false,
						int    $inviter_user_id = 0, int $role = Type_Conversation_Meta_Users::ROLE_DEFAULT, bool $is_favorite = false,
						bool   $is_mentioned = false, string $userbot_id = "", bool $is_need_silent = false):array {

		// если пользователь администратор всех групп - добавляем в группу как администратора
		if (Permission::isGroupAdministrator($member_role, $member_permissions)) {
			$role = Type_Conversation_Meta_Users::ROLE_OWNER;
		}

		// добавляем пользователя в группу
		[$conversation_data] = Type_Conversation_Group::addUserToGroup($conversation_map, $user_id, $role, $is_favorite, $is_mentioned, $userbot_id);
		$meta_row      = Type_Conversation_Utils::getMetaRowFromConversationData($conversation_data);
		$left_menu_row = Type_Conversation_Utils::getLeftMenuRowFromConversationData($conversation_data);

		self::setClearMessagesConversationForJoinGroup($user_id, $member_role, $conversation_map, $left_menu_row, $meta_row, $is_need_silent);

		$need_send_system = true;

		// если создатель группы тот же человек которого приглашаем и это только создание группы - то не отправляем системное сообщение
		if (isset($meta_row["creator_user_id"]) && $meta_row["creator_user_id"] == $user_id) {
			$need_send_system = false;
		}

		// если пользователю не надо отправлять сообщение, но его перепригласили так как пользователей несколько - нужно отправить
		if (!$need_send_system && count($meta_row["users"]) > 1) {
			$need_send_system = true;
		}

		if ($need_send_system && !$is_need_silent) {

			// добавляем системное сообщение о вступлении пользователя в группу
			$system_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemUserJoinedToGroup($user_id);
			$is_silent      = Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnInviteAndJoin($meta_row["extra"]);
			self::_addSystemMessage($conversation_map, $system_message, $meta_row, $is_silent);
			Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::JOIN_GROUP);
		}

		// действия после вступления пользователя в групповой диалог
		self::_onJoinUserToGroup($conversation_map, $user_id, $meta_row["users"], $inviter_user_id, $role, $is_need_silent);

		// ставим диалог на индексацию
		Domain_Search_Entity_Conversation_Task_Reindex::queueForUser($conversation_map, [$user_id]);

		// подготавливаем сущность conversation
		return Type_Conversation_Utils::prepareConversationForFormat($meta_row, $left_menu_row);
	}

	// устанавливаем очистку диалога при вступлении в группу
	public static function setClearMessagesConversationForJoinGroup(int $user_id, int $member_role, string $conversation_map, array $left_menu_row, array $meta_row, bool $is_need_silent = false):void {

		// если для диалога убрана опция — "Показывать историю сообщений" — очищаем
		if (!Type_Conversation_Meta_Extra::isShowHistoryForNewMembers($meta_row["extra"]) || Member::ROLE_GUEST === $member_role) {

			Helper_Conversations::clearMessages($user_id, $conversation_map, $left_menu_row, false, time(), $is_need_silent);
			return;
		}

		// получаем dynamic диалога
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($conversation_map);

		// диалог не чистился вообще - ничего не делаем (не чистился пользователем у себя, не чистился при прошлом вступлении со скрытой историей сообщений, не чистился у всех участников диалога)
		if (Type_Conversation_Meta_Extra::getConversationClearUntilForAll($meta_row["extra"]) === 0
			&& !isset($dynamic_row["user_clear_info"][$user_id])
			&& !isset($dynamic_row["conversation_clear_info"][$user_id])) {
			return;
		}

		// если диалог не чистился для всех, но чистился при вступлении в прошлый раз - разклириваем (чистка диалога только для себя учтена в unclearMessages)
		if (Type_Conversation_Meta_Extra::getConversationClearUntilForAll($meta_row["extra"]) === 0 && isset($dynamic_row["conversation_clear_info"][$user_id])) {

			Helper_Conversations::unclearMessages($user_id, $conversation_map, $left_menu_row, $dynamic_row);
			return;
		}

		// если личная очистка была, и она была позже очистки для всех (чистка диалога только для себя учтена в unclearMessages)
		if (isset($dynamic_row["user_clear_info"][$user_id])
			&& $dynamic_row["user_clear_info"][$user_id]["clear_until"] > Type_Conversation_Meta_Extra::getConversationClearUntilForAll($meta_row["extra"])) {

			Helper_Conversations::unclearMessages($user_id, $conversation_map, $left_menu_row, $dynamic_row);
			return;
		}

		// получаем время очистки диалога для всех и если оно есть - очищаем по нему
		$clear_until = Type_Conversation_Meta_Extra::getConversationClearUntilForAll($meta_row["extra"]);
		if ($clear_until > 0) {
			Helper_Conversations::clearMessages($user_id, $conversation_map, $left_menu_row, false, $clear_until, $is_need_silent);
		}
	}

	// действия после вступления пользователя в группе
	// @long
	protected static function _onJoinUserToGroup(string $conversation_map, int $user_id, array $users, int $inviter_user_id, int $role, bool $is_need_silent = false):void {

		// обновляем количество пользователей в left_menu, очищаем meta кэш и отправляем событие пользователю, что добавлен диалог в левом меню
		Type_Phphooker_Main::updateMembersCount($conversation_map, $users);
		Type_Phphooker_Main::sendClearThreadMetaCache($conversation_map);

		if (!$is_need_silent) {
			Gateway_Bus_Sender::conversationAdded($user_id, $conversation_map);
		}

		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($users);

		if (!$is_need_silent) {
			Gateway_Bus_Sender::conversationGroupUserJoined($talking_user_list, $conversation_map, $user_id, $role, $users);
		}

		// помечаем остальные инвайты inactive
		Type_Phphooker_Main::setInactiveAllUserInviteToConversation(
			$user_id,
			$conversation_map,
			Type_Invite_Handler::INACTIVE_REASON_ACCEPTED
		);

		// если приглащающих нет
		if ($inviter_user_id < 1) {
			return;
		}

		if (!$is_need_silent) {

			$invite_row = Type_Conversation_Invite::getByConversationMapAndUserId($inviter_user_id, $user_id, $conversation_map);
			self::_throwIfInviteIsNotAccepted($invite_row);

			$talking_user_list   = [];
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($user_id, false);
			$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($invite_row["sender_user_id"], false);
			Gateway_Bus_Sender::conversationInviteStatusChanged(
				$talking_user_list, $invite_row["invite_map"], $user_id, Type_Invite_Handler::STATUS_ACCEPTED, $conversation_map
			);
		}
	}

	// бросаем экзепшен если статус не accepted
	protected static function _throwIfInviteIsNotAccepted(array $invite_row):void {

		if ($invite_row["status"] != Type_Invite_Handler::STATUS_ACCEPTED) {
			throw new ParseFatalException(__METHOD__ . " invite is not accepted");
		}
	}

	// кикаем юзера $user_id из группы
	public static function doUserKick(array $meta_row, int $user_id, bool $is_need_unfollow_threads = false, string $userbot_id = ""):void {

		// пользователь является участником диалога?
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			return;
		}

		// убираем юзера из группы (локально или socket запросом)
		$conversation_map = $meta_row["conversation_map"];
		try {
			$meta_row = self::_removeUserFromGroup($conversation_map, $user_id, Type_Conversation_LeftMenu::LEAVE_REASON_KICKED, $userbot_id);
		} catch (cs_UserIsNotMember) {
			return;
		}

		// обновляем информацию в инвайте
		Type_Phphooker_Main::setInactiveAllUserInviteToConversation($user_id, $conversation_map, Type_Invite_Handler::INACTIVE_REASON_SENDER_LEAVED);

		// если нужно отписывать от тредов
		if ($is_need_unfollow_threads) {
			Type_Phphooker_Main::doUnfollowThreadListByConversationMap($user_id, $conversation_map);
		}

		// обновляем бадж с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$conversation_map], true);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);

		// отправляем системное сообщение в диалог
		$system_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemUserKickedFromGroup($user_id);
		$is_silent      = Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnLeaveAndKicked($meta_row["extra"]);
		self::_addSystemMessage($conversation_map, $system_message, $meta_row, $is_silent);

		// делаем все что необходимо когда юзер покидает группу
		self::_onUserLeftGroupForAnyReason($conversation_map, $meta_row, $user_id, Type_Conversation_LeftMenu::LEAVE_REASON_KICKED);

		// удаляем данные диалога для пользователя из поиска
		Domain_Search_Entity_Conversation_Task_Clear::queue($conversation_map, [$user_id]);

		Gateway_Bus_Statholder::inc("groups", "row109");
	}

	// переименовывает группу
	public static function doChangeName(int $user_id, string $conversation_map, string $group_name, array $meta_row):void {

		// переименовываем группу
		Type_Conversation_Group::setName($conversation_map, $group_name);

		// актуализируем информацию о диалоге в пользовательских таблицах на member DPC
		Type_Phphooker_Main::onChangeConversationName($conversation_map, $meta_row["users"], $group_name);

		// отправляем системное сообщение
		$system_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemAdminRenamedGroup(
			$user_id,
			$group_name,
			$meta_row["conversation_name"]
		);
		Helper_Conversations::addMessage($conversation_map, $system_message, $meta_row["users"], $meta_row["type"], $group_name, $meta_row["extra"]);

		// подготавливаем talking_user_list
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);

		// отправляем событие участникам
		Gateway_Bus_Sender::conversationGroupRenamed($talking_user_list, $conversation_map, $group_name);
	}

	/**
	 * обновляет аватар группы
	 *
	 * @throws \parseException
	 */
	public static function doChangeAvatar(string $conversation_map, string $file_map, array $meta_row):void {

		// обновляем аватар группы
		Type_Conversation_Group::setAvatar($conversation_map, $file_map);

		// актуализируем информацию о диалоге в пользовательских таблицах
		Type_Phphooker_Main::onChangeConversationAvatar($conversation_map, $meta_row["users"], $file_map);

		// подготавливаем talking_user_list
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);

		if (isEmptyString($file_map)) {

			// отправляем событие участникам
			Gateway_Bus_Sender::conversationGroupAvatarCleared($talking_user_list, $conversation_map);

			Gateway_Bus_Sender::conversationGroupChangedBaseInfo($talking_user_list, $conversation_map, false, $file_map, false, $meta_row);
			return;
		}

		// отправляем событие участникам
		Gateway_Bus_Sender::conversationGroupAvatarChanged($talking_user_list, $conversation_map, $file_map);
	}

	/**
	 * изменяем основную информацию о группе (название группы, описание, аватарка)
	 *
	 * @param int          $user_id
	 * @param string       $conversation_map
	 * @param array        $meta_row
	 * @param string|false $name
	 * @param string|false $file_map
	 * @param string|false $description
	 *
	 * @return array
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public static function doChangeBaseInfo(int $user_id, string $conversation_map, array $meta_row, string|false $name, string|false $file_map, string|false $description):array {

		// выбрасываем ошибку, если чату не доступно действие
		if ($name !== false) {
			Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::CHANGE_NAME_FROM_CONVERSATION);
		}

		if ($file_map !== false) {
			Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::CHANGE_AVATAR_FROM_CONVERSATION);
		}

		if ($description !== false) {
			Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::CHANGE_DESCRIPTION_FROM_CONVERSATION);
		}

		// выбрасываем ошибку, если пользователь не может изменить информацию о группе
		if (!Type_Conversation_Meta_Users::isGroupAdmin($user_id, $meta_row["users"])) {
			throw new Domain_Member_Exception_ActionNotAllowed("Action not allowed");
		}

		// обновляем основную информацию о группе (название, описание, аватарку)
		$changed_meta_row = Type_Conversation_Group::setBaseInfo($conversation_map, $meta_row, $name, $file_map, $description);

		// актуализируем информацию о диалоге в пользовательских таблицах
		// описание там не хранится, менять его у пользователей не надо
		Type_Phphooker_Main::onChangeGroupBasicInfo($conversation_map, $meta_row["users"], $name, $file_map);

		// отправляем системные сообщения
		self::_sendSystemMessagesAfterChangedGroupInfo($user_id, $conversation_map, $name, $meta_row);

		// подготавливаем talking_user_list
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);

		// отправляем событие участникам
		self::_sendEventsAfterChangedGroupInfo($talking_user_list, $conversation_map, $name, $file_map, $description, $meta_row);

		return $changed_meta_row;
	}

	/**
	 * отправляем системные сообщения после изменения информации группового диалога
	 *
	 * @param int          $user_id
	 * @param string       $conversation_map
	 * @param string|false $group_name
	 * @param array        $meta_row
	 *
	 * @mixed - $group_name, $file_map могут быть false
	 */
	protected static function _sendSystemMessagesAfterChangedGroupInfo(int $user_id, string $conversation_map, string|false $group_name, array $meta_row):void {

		if ($group_name !== false) {

			$system_message_list[] = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemAdminRenamedGroup(
				$user_id,
				$group_name,
				$meta_row["conversation_name"]
			);

			Helper_Conversations::addMessageList($conversation_map, $system_message_list, $meta_row["users"], $meta_row["type"], $group_name, $meta_row["extra"]);
		}
	}

	/**
	 * отправляем участникам диалога уведомления об изменения информации группового диалога
	 *
	 * @param array        $talking_user_list
	 * @param string       $conversation_map
	 * @param string|false $group_name
	 * @param string|false $file_map
	 * @param string|false $description
	 * @param array        $meta_row
	 *
	 * @throws ParseFatalException
	 * @mixed - $group_name, $file_map могут быть false
	 */
	protected static function _sendEventsAfterChangedGroupInfo(array $talking_user_list, string $conversation_map, string|false $group_name, string|false $file_map, string|false $description, array $meta_row):void {

		Gateway_Bus_Sender::conversationGroupChangedBaseInfo($talking_user_list, $conversation_map, $group_name, $file_map, $description, $meta_row);

		// также отправляем события для старых клиентов
		if ($group_name !== false) {
			Gateway_Bus_Sender::conversationGroupRenamed($talking_user_list, $conversation_map, $group_name);
		}
		if ($file_map !== false) {

			if ($file_map === "") {

				Gateway_Bus_Sender::conversationGroupAvatarCleared($talking_user_list, $conversation_map);
				return;
			}

			Gateway_Bus_Sender::conversationGroupAvatarChanged($talking_user_list, $conversation_map, $file_map);
		}
	}

	// позволяет пользователю выйти из группы
	public static function doLeave(string $conversation_map, int $user_id, array $meta_row, bool $is_need_notify_about_group_left = true, bool $is_need_unfollow_threads = false, bool $is_need_notify_about_company_left = false):void {

		// получаем сокращенную информацию по пользователю
		$user_info = self::_getUserInfo($user_id);

		// если это "Главный чат" и овнер компании пытается выйти, роняем исключение
		if (self::_isOwnerTryToLeaveGeneralConversation($user_info, $conversation_map)) {
			throw new cs_OwnerTryToLeaveGeneralConversation();
		}

		// если это чат Спасибо и овнер компании пытается выйти, роняем исключение
		if (self::_isOwnerTryToLeaveRespectConversation($user_info, $conversation_map)) {
			throw new cs_OwnerTryToLeaveRespectConversation();
		}

		// если уже не являемся участником диалога
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			return;
		}

		// устанавливаем нового овнера, если необходимо
		if (self::_isNeedAddOwner($meta_row["users"], $user_id) && !Type_System_Legacy::isDisabledAutoAssignmentOfAdministrator()) {
			self::_setNewOwnerFromMembers($user_id, $meta_row["users"], $conversation_map);
		}

		// покидаем групповой диалог (локально или socket запросом)
		try {
			$meta_row = self::_removeUserFromGroup($conversation_map, $user_id, Type_Conversation_LeftMenu::LEAVE_REASON_LEAVED);
		} catch (cs_UserIsNotMember) {
			return;
		}

		// обновляем информацию в инвайте; помечаем пользователя удаленным в sphinx
		Type_Phphooker_Main::setInactiveAllUserInviteToConversation($user_id, $conversation_map, Type_Invite_Handler::INACTIVE_REASON_SENDER_LEAVED);

		// если нужно отписывать от тредов
		if ($is_need_unfollow_threads) {
			Type_Phphooker_Main::doUnfollowThreadListByConversationMap($user_id, $conversation_map);
		}

		// обновляем бадж с непрочитанными для пользователя
		$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id, [$conversation_map], true);
		Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);

		// если необходимо отправить системное сообщение о покидании группы (для покидания компании также идёт это же системное сообщение)
		if ($is_need_notify_about_group_left || $is_need_notify_about_company_left) {

			$system_message = Type_Conversation_Message_Main::getLastVersionHandler()::makeSystemUserLeftGroup($user_id);
			$is_silent      = Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnLeaveAndKicked($meta_row["extra"]);
			self::_addSystemMessage($conversation_map, $system_message, $meta_row, $is_silent);
		}

		// делаем все что необходимо когда юзер покидает группу
		self::_onUserLeftGroupForAnyReason($conversation_map, $meta_row, $user_id, Type_Conversation_LeftMenu::LEAVE_REASON_LEAVED);

		// удаляем данные диалога для пользователя из поиска
		Domain_Search_Entity_Conversation_Task_Clear::queue($conversation_map, [$user_id]);
	}

	/**
	 * Проверяем кто пытается ливнуть с чата
	 *
	 * @param Short  $user_info
	 * @param string $conversation
	 *
	 * @return bool
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _isOwnerTryToLeaveGeneralConversation(Short $user_info, string $conversation):bool {

		if ($user_info->role != Member::ROLE_ADMINISTRATOR
			|| !Permission::hasOwnerPermissions($user_info->permissions)) {
			return false;
		}

		// получаем ключ чата general
		$general_conversation_map = Type_Company_Default::getCompanyGroupConversationMapByKey(Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME);
		if ($conversation == $general_conversation_map) {
			return true;
		}

		return false;
	}

	/**
	 * Проверяем кто пытается ливнуть с чата спасибо
	 *
	 * @param Short  $user_info
	 * @param string $conversation
	 *
	 * @return bool
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _isOwnerTryToLeaveRespectConversation(Short $user_info, string $conversation):bool {

		if ($user_info->role != Member::ROLE_ADMINISTRATOR
			|| !Permission::hasOwnerPermissions($user_info->permissions)) {
			return false;
		}

		// получаем ключ чата Спасибо
		try {
			$respect_conversation_map = Type_Company_Default::getCompanyGroupConversationMapByKey(Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME);
		} catch (\cs_RowIsEmpty) {
			return false; // если чат пока не создан
		}
		return $conversation == $respect_conversation_map;
	}

	/**
	 * Получаем информацию о пользователе
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \returnException
	 */
	protected static function _getUserInfo(int $user_id):Short {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		if (!isset($user_info_list[$user_id])) {
			throw new ParamException(__METHOD__ . ": user not found");
		}
		return $user_info_list[$user_id];
	}

	// проверяем нужно ли добавить нового овнера
	protected static function _isNeedAddOwner(array $users, int $leaved_user_id):bool {

		// если единственный участник или роль простого участника, то ничего не трогаем
		if (count($users) <= 1 || Type_Conversation_Meta_Users::isDefaultMember($leaved_user_id, $users)) {
			return false;
		}

		// убираем пользователя, который ливает
		unset($users[$leaved_user_id]);

		// если овнеры имеются, то ничего не трогаем
		$owner_list = Type_Conversation_Meta_Users::getOwners($users);
		if (count($owner_list) > 0) {
			return false;
		}

		return true;
	}

	// добавляем нового овнера из пользователей группы
	protected static function _setNewOwnerFromMembers(int $old_owner_user_id, array $users, string $conversation_map):void {

		// убираем пользователя из списка пользователей, чтобы не назначить его же овнером
		unset($users[$old_owner_user_id]);

		// получаем пользователя, который станет новым овнером
		// (если участник, то раньше всех вступившего в группу; если админ - раньше всех получивший роль)
		$user_id_list      = Type_Conversation_Meta_Users::getUserIdListSortedByJoinTime($users);
		$new_owner_user_id = array_shift($user_id_list);

		// отправляем задачу для установки роли овнера пользователю
		Type_Phphooker_Main::changeUserRoleInGroup($conversation_map, $new_owner_user_id, Type_Conversation_Meta_Users::ROLE_OWNER);
	}

	// устанавливаем роль пользователю в диалоге
	public static function setRole(string $conversation_map, int $user_id, int $role):void {

		// устанавливаем роль пользователю в диалоге
		Type_Conversation_Group::setRole($conversation_map, $user_id, $role);

		// пушим событие, что пользователь сменил роль
		Gateway_Event_Dispatcher::dispatch(Type_Event_UserConversation_UserRoleChanged::create(
			$user_id, $conversation_map, $role, time()
		));

		// очищаем кэш-мета для всех тредов текущего диалога
		Type_Phphooker_Main::sendClearThreadMetaCache($conversation_map);
	}

	// устанавливаем опции диалога
	public static function doChangeOptions(string $conversation_map, array $meta_row, array $modifiable_options):void {

		// обновляем extra
		$extra = self::_updateExtraOnChangeOptions($meta_row["extra"], $modifiable_options);

		// обновляем мету
		Type_Conversation_Group::setOptions($conversation_map, $extra);

		// получаем список участников группы - talking_user_list
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);

		// получаем актуальный список опций
		$actual_option_list = [
			"is_show_history_for_new_members"                 => Type_Conversation_Meta_Extra::isShowHistoryForNewMembers($extra) ? 1 : 0,
			"is_can_commit_worked_hours"                      => Type_Conversation_Meta_Extra::isCanCommitWorkedHours($extra) ? 1 : 0,
			"need_system_message_on_dismissal"                => Type_Conversation_Meta_Extra::isNeedSystemMessageOnDismissal($extra) ? 1 : 0,
			"is_need_show_system_message_on_invite_and_join"  => Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnInviteAndJoin($extra) ? 1 : 0,
			"is_need_show_system_message_on_leave_and_kicked" => Type_Conversation_Meta_Extra::isNeedShowSystemMessageOnLeaveAndKicked($extra) ? 1 : 0,
			"is_need_show_system_deleted_message"             => Type_Conversation_Meta_Extra::isNeedShowSystemDeletedMessage($extra) ? 1 : 0,
		];

		// отправляем событие участникам
		Gateway_Bus_Sender::conversationGroupChangedOptions($talking_user_list, $conversation_map, $actual_option_list);

		// если была выключена опцию is_need_show_system_deleted_message, актуализируем левое меню если необходимо
		if (Type_Conversation_Meta_Extra::isNeedShowSystemDeletedMessage($meta_row["extra"]) && !Type_Conversation_Meta_Extra::isNeedShowSystemDeletedMessage($extra)) {
			Type_Phphooker_Main::doDisableSystemDeletedMessageConversation($conversation_map, $meta_row["users"]);
		}
	}

	// актуализируем поле extra с учетом изменяемых опций
	protected static function _updateExtraOnChangeOptions(array $extra, array $modifiable_options):array {

		// пробегаемся по изменениям
		foreach ($modifiable_options as $option => $new_value) {

			$extra = match ($option) {

				"is_show_history_for_new_members"                 => Type_Conversation_Meta_Extra::setFlagShowHistoryForNewMembers($extra, $new_value),
				"is_can_commit_worked_hours"                      => Type_Conversation_Meta_Extra::setFlagCanCommitWorkedHours($extra, $new_value),
				"need_system_message_on_dismissal"                => Type_Conversation_Meta_Extra::setFlagNeedSystemMessageOnDismissal($extra, $new_value),
				"is_need_show_system_message_on_invite_and_join"  => Type_Conversation_Meta_Extra::setFlagNeedShowSystemMessageOnInviteAndJoin($extra, $new_value),
				"is_need_show_system_message_on_leave_and_kicked" => Type_Conversation_Meta_Extra::setFlagNeedShowSystemMessageOnLeaveAndKicked($extra, $new_value),
				"is_need_show_system_deleted_message"             => Type_Conversation_Meta_Extra::setFlagNeedShowSystemDeletedMessage($extra, $new_value),
				default                                           => throw new ParseFatalException(__METHOD__ . ": unhandled option"),
			};
		}

		return $extra;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// убирает юзера из группы
	protected static function _removeUserFromGroup(string $conversation_map, int $user_id, int $leave_reason, string $userbot_id = ""):array {

		[$after_meta_row] = Type_Conversation_Group::removeUserFromGroup($conversation_map, $user_id, $leave_reason, $userbot_id);

		return $after_meta_row;
	}

	// выполняем все что необходимо когда юзер покидает группу (независимо от причины)
	protected static function _onUserLeftGroupForAnyReason(string $conversation_map, array $meta_row, int $left_user_id, int $leave_reason):void {

		// обновляем количество пользователей в left_menu
		Type_Phphooker_Main::updateMembersCount($conversation_map, $meta_row["users"]);

		// очищаем meta кэш для php_thread по conversation_map
		Type_Phphooker_Main::sendClearThreadMetaCache($conversation_map);

		// подготавливаем talking_user_list
		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);

		// добавляем пользователя который покидает группу
		$talking_user_list[] = Gateway_Bus_Sender::makeTalkingUserItem($left_user_id, false);

		// отправляем событие участникам
		Gateway_Bus_Sender::conversationGroupUserLeaved($talking_user_list, $conversation_map, $left_user_id, $meta_row["users"], $leave_reason);
	}

	// добавляем системное сообщение
	protected static function _addSystemMessage(string $conversation_map, array $system_message, array $meta_row, bool $is_silent):void {

		Type_Phphooker_Main::addMessage(
			$conversation_map,
			$system_message,
			$meta_row["users"],
			$meta_row["type"],
			$meta_row["conversation_name"],
			$meta_row["extra"],
			$is_silent,
		);
	}
}
