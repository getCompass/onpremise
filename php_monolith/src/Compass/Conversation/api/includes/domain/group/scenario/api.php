<?php declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\BusFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * API-сценарии домена «группового диалога».
 */
class Domain_Group_Scenario_Api {

	/**
	 * самоназначаем администратором в группе
	 *
	 * @param string $conversation_map
	 * @param int    $user_id
	 * @param int    $user_role
	 * @param int    $user_permissions
	 *
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws cs_Conversation_IsGroupIfOwnerExist
	 * @throws cs_UserIsNotMember
	 */
	public static function trySelfAssignAdminRole(string $conversation_map, int $user_id, int $user_role, int $user_permissions):void {

		Member::assertUserNotGuest($user_role);

		$meta_row = Gateway_Db_CompanyConversation_ConversationMetaLegacy::getOne($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::CHANGE_ROLE_USER_FROM_CONVERSATION);

		// выполним все проверки для доступа к установке администратора
		$meta_row = Type_Conversation_Group_SelfAdmin_Action::do($conversation_map, $user_id, $user_role, $user_permissions);

		self::_sendWsEventConversationGroupRoleChanged($user_id, $conversation_map, Type_Conversation_Meta_Users::ROLE_OWNER, $meta_row["users"]);
	}

	/**
	 *  очистка сообщений в диалоге для всех участников группы
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 *
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_UserIsNotAdmin
	 */
	public static function clearMessagesForAll(int $user_id, string $conversation_map):void {

		// проверяем валидность действия и что пользователь администратор группы
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::CLEAR_CONVERSATION_FOR_ALL);

		// если пользователь не админ в группе и не имеет прав администратора всех групп
		if (!Type_Conversation_Meta_Users::isOwnerMember($user_id, $meta_row["users"])) {
			throw new cs_UserIsNotAdmin();
		}

		// обновляем время очистки диалога
		$clear_until = time();
		Type_Conversation_Meta::setConversationClearUntilForAll($conversation_map, $clear_until);

		// получаем пользователей состоящих в диалоге
		$user_id_list = [];
		foreach ($meta_row["users"] as $member_user_id => $_) {

			if (Type_Conversation_Meta_Users::isMember($member_user_id, $meta_row["users"])) {
				$user_id_list[] = $member_user_id;
			}
		}

		// обнуляем unread_count, last_message и устанавливаем clear_until в left_menu
		Type_Conversation_LeftMenu::setClearedForUserIdList($user_id_list, $conversation_map, $clear_until);

		// обновляем время очистки диалога для всех пользователей
		$dynamic = Domain_Conversation_Entity_Dynamic::setClearUntilConversationForUserIdList($conversation_map, $user_id_list, $clear_until, true);

		// делаем сокет запрос в модуль php_thread для обновление времени очистки диалога
		Gateway_Socket_Thread::clearConversationForUserIdList($conversation_map, $clear_until, $user_id_list);

		// делим на чанки по 100 пользователей и пушим событие через go_event
		$chunk_user_id_list = array_chunk($user_id_list, 100);
		foreach ($chunk_user_id_list as $user_id_list) {

			Gateway_Event_Dispatcher::dispatch(
				Type_Event_Conversation_ClearConversationForUsers::create($conversation_map, $user_id_list, $dynamic->messages_updated_version)
			);
		}

		// удаляем все данные диалога в поисковом индексе
		Domain_Search_Entity_Conversation_Task_Purge::queue($conversation_map);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * отправляем инвент о смнеи роли в группе
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param int    $role
	 * @param array  $users
	 *
	 * @throws \parseException
	 */
	protected static function _sendWsEventConversationGroupRoleChanged(int $user_id, string $conversation_map, int $role, array $users):void {

		$previous_user_role = Type_Conversation_Meta_Users::getRole($user_id, $users);
		$talking_user_list  = Type_Conversation_Meta_Users::getTalkingUserList($users);
		Gateway_Bus_Sender::conversationGroupRoleChanged($talking_user_list, $conversation_map, $user_id, $role, $previous_user_role);
	}

	/**
	 * Создать группу
	 *
	 * @param int    $user_id
	 * @param string $name
	 * @param string $avatar_file_map
	 * @param string $description
	 *
	 * @return array
	 * @throws Domain_Group_Exception_InvalidFileForAvatar
	 * @throws Domain_Group_Exception_InvalidName
	 * @throws Domain_Group_Exception_NameContainsEmoji
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParseFatalException
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws \cs_RowIsEmpty
	 */
	public static function add(int $user_id, string $name, string $avatar_file_map, string $description):array {

		// проверяем ограничение на создание группы
		Domain_Member_Entity_Permission::check($user_id, Permission::IS_ADD_GROUP_ENABLED);

		// форматируем название группового диалога
		[$name, $description] = self::_validateGroupFields($name, $avatar_file_map, $description);

		$meta_row = Helper_Groups::create($user_id, $name, $avatar_file_map, $description);

		// получаем запись из левого меню
		$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $meta_row["conversation_map"]);

		// отдаем ответ
		$prepared_conversation = Type_Conversation_Utils::prepareConversationForFormat($meta_row, $left_menu_row);
		Type_User_ActionAnalytics::send($user_id, Type_User_ActionAnalytics::ADD_GROUP);

		return $prepared_conversation;
	}

	/**
	 * Изменить группу
	 *
	 * @param int          $user_id
	 * @param string       $conversation_map
	 * @param string|false $name
	 * @param string|false $avatar_file_map
	 * @param string|false $description
	 *
	 * @return array
	 * @throws Domain_Group_Exception_InvalidFileForAvatar
	 * @throws Domain_Group_Exception_InvalidName
	 * @throws Domain_Group_Exception_NameContainsEmoji
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public static function edit(int $user_id, string $conversation_map, string|false $name, string|false $avatar_file_map, string|false $description):array {

		// форматируем название группового диалога
		[$name, $description] = self::_validateGroupFields($name, $avatar_file_map, $description);

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		$meta_row = Helper_Groups::doChangeBaseInfo($user_id, $conversation_map, $meta_row, $name, $avatar_file_map, $description);

		// получаем запись из левого меню
		$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $meta_row["conversation_map"]);

		// отдаем ответ
		return Type_Conversation_Utils::prepareConversationForFormat($meta_row, $left_menu_row);
	}

	/**
	 * Продублировать группу
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param string $name
	 * @param string $avatar_file_map
	 * @param string $description
	 * @param array  $excluded_user_id_list
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws Domain_Group_Exception_InvalidFileForAvatar
	 * @throws Domain_Group_Exception_InvalidName
	 * @throws Domain_Group_Exception_NameContainsEmoji
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function copy(int $user_id, string $conversation_map, string $name, string $avatar_file_map, string $description, array $excluded_user_id_list):array {

		// проверяем ограничение на создание группы
		Domain_Member_Entity_Permission::check($user_id, Permission::IS_ADD_GROUP_ENABLED);

		// форматируем название группового диалога
		[$name, $description] = self::_validateGroupFields($name, $avatar_file_map, $description);

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// выбрасываем ошибку, если пользователь не может изменить информацию о группе
		if (!Type_Conversation_Meta_Users::isGroupAdmin($user_id, $meta_row["users"])) {
			throw new Domain_Member_Exception_ActionNotAllowed("Action not allowed");
		}

		// получаем пользователей состоящих в диалоге (кроме себя)
		$user_list = [];
		foreach ($meta_row["users"] as $invited_user_id => $_) {

			if ($user_id !== $invited_user_id && Type_Conversation_Meta_Users::isMember($invited_user_id, $meta_row["users"])) {

				$user_list[$invited_user_id] = new Struct_Conversation_User(
					$invited_user_id,
					Type_Conversation_Meta_Users::getRole($invited_user_id, $meta_row["users"])
				);
			}
		}

		// удаляем исключенных пользователей
		if (count($excluded_user_id_list) > 0) {

			$user_list = array_filter($user_list, fn(int $invited_user_id) => !in_array($invited_user_id, $excluded_user_id_list),
				ARRAY_FILTER_USE_KEY);
		}

		// фильтруем массив пользователей
		$user_list = self::_filterInvitedUserList($user_list);

		// создаем групповой диалог
		$meta_row = Helper_Groups::create($user_id, $name, $avatar_file_map, $description);

		// получаем запись из левого меню
		$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $meta_row["conversation_map"]);

		if (count($user_list) > 0) {

			// пушим событие на создание и отправку инвайта списку пользователей
			Gateway_Event_Dispatcher::dispatch(Type_Event_Invite_CreateAndSendInvite::create($user_id, array_keys($user_list), $meta_row));
		}

		return Type_Conversation_Utils::prepareConversationForFormat($meta_row, $left_menu_row);
	}

	/**
	 * Продублировать группу c добавлением пользователей в неё
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param string $name
	 * @param string $avatar_file_map
	 * @param string $description
	 * @param array  $excluded_user_id_list
	 *
	 * @return array
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 * @throws Domain_Group_Exception_InvalidFileForAvatar
	 * @throws Domain_Group_Exception_InvalidName
	 * @throws Domain_Group_Exception_NameContainsEmoji
	 * @throws Domain_Member_Exception_ActionNotAllowed
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty|cs_PlatformNotFound
	 */
	public static function copyWithUsers(int $user_id, string $conversation_map, string $name, string $avatar_file_map, string $description, array $excluded_user_id_list):array {

		// проверяем ограничение на создание группы
		Domain_Member_Entity_Permission::check($user_id, Permission::IS_ADD_GROUP_ENABLED);

		// форматируем название группового диалога
		[$name, $description] = self::_validateGroupFields($name, $avatar_file_map, $description);

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// выбрасываем ошибку, если пользователь не может изменить информацию о группе
		if (!Type_Conversation_Meta_Users::isGroupAdmin($user_id, $meta_row["users"])) {
			throw new Domain_Member_Exception_ActionNotAllowed("Action not allowed");
		}

		// получаем пользователей состоящих в диалоге (кроме себя)
		$user_list = [];
		foreach ($meta_row["users"] as $invited_user_id => $_) {

			if ($user_id !== $invited_user_id && Type_Conversation_Meta_Users::isMember($invited_user_id, $meta_row["users"])) {

				$user_list[$invited_user_id] = new Struct_Conversation_User(
					$invited_user_id,
					Type_Conversation_Meta_Users::getRole($invited_user_id, $meta_row["users"]),
				);
			}
		}

		// удаляем исключенных пользователей
		if (count($excluded_user_id_list) > 0) {

			$user_list = array_filter($user_list, fn(int $invited_user_id) => !in_array($invited_user_id, $excluded_user_id_list),
				ARRAY_FILTER_USE_KEY);
		}

		// фильтруем массив пользователей
		$user_list = self::_filterInvitedUserList($user_list);

		// создаем групповой диалог
		$meta_row = Helper_Groups::create($user_id, $name, $avatar_file_map, $description);

		// получаем запись из левого меню
		$left_menu_row = Type_Conversation_LeftMenu::get($user_id, $meta_row["conversation_map"]);

		if (count($user_list) > 0) {

			// пушим событие на создание и отправку инвайта списку пользователей
			$platform = Type_Api_Platform::getPlatform();
			Gateway_Event_Dispatcher::dispatch(Type_Event_Invite_CreateAndSendAutoAcceptInvite::create($user_id, $user_list, $meta_row, $platform));
		}

		return Type_Conversation_Utils::prepareConversationForFormat($meta_row, $left_menu_row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Провалидировать поля группы
	 *
	 * @param string|false $name
	 * @param string|false $avatar_file_map
	 * @param string|false $description
	 *
	 * @return array
	 * @throws Domain_Group_Exception_InvalidFileForAvatar
	 * @throws Domain_Group_Exception_InvalidName
	 * @throws Domain_Group_Exception_NameContainsEmoji
	 */
	protected static function _validateGroupFields(string|false $name, string|false $avatar_file_map, string|false $description):array {

		if ($name !== false) {

			// если в названии есть эмодзи
			if (Type_Api_Validator::isStringContainEmoji($name)) {
				throw new Domain_Group_Exception_NameContainsEmoji("name contains emoji");
			}

			// форматируем название группового диалога
			$name = Type_Api_Filter::sanitizeGroupName($name);

			if (!Type_Api_Validator::isGroupName($name)) {
				throw new Domain_Group_Exception_InvalidName("invalid name for group");
			}
		}

		// форматируем описание группы
		if ($description !== false) {
			$description = Type_Api_Filter::sanitizeGroupDescription($description);
		}

		// если это не изображение
		if ($avatar_file_map !== false && $avatar_file_map !== "" && \CompassApp\Pack\File::getFileType($avatar_file_map) !== FILE_TYPE_IMAGE) {
			throw new Domain_Group_Exception_InvalidFileForAvatar("cant use file as avatar");
		}

		return [$name, $description];
	}

	/**
	 * фильтруем массив пользователей
	 *
	 * @param Struct_Conversation_User[] $user_list
	 *
	 * @return Struct_Conversation_User[]
	 * @throws BusFatalException
	 * @throws ControllerMethodNotFoundException
	 */
	protected static function _filterInvitedUserList(array $user_list):array {

		// получаем нужных пользователей
		$user_info_list = Gateway_Bus_CompanyCache::getMemberList(array_keys($user_list));

		$deleted_user_id_list = [];
		foreach ($user_info_list as $member) {

			if (\CompassApp\Domain\Member\Entity\Extra::getIsDeleted($member->extra)) {
				$deleted_user_id_list[] = $member->user_id;
			}
		}

		// не шлем удаленным пользователям
		if (count($deleted_user_id_list) > 0) {

			$user_list = array_filter($user_list, fn(int $invited_user_id) => !in_array($invited_user_id, $deleted_user_id_list),
				ARRAY_FILTER_USE_KEY);
		}

		return $user_list;
	}
}