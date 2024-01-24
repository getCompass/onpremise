<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\UserIsGuest;

/**
 * контроллер, отвечающий за взаимодействие пользователя с групповыми диалогами
 * @property Type_Api_Action action
 */
class Apiv1_Groups extends \BaseFrame\Controller\Api {

	protected const _GET_MANAGED_COUNT      = 1000; // количество возвращаемых диалогов в getManaged

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"add",
		"doLeave",
		"tryKick",
		"getManaged",
		"getInvited",
		"getShared",
		"doRevokeInvite",
		"changeRole",
		"setOptions",
		"trySelfAssignAdminRole",
		"clearMessagesForAll",
	];

	public const MEMBER_ACTIVITY_METHOD_LIST = [
		"add",
		"doLeave",
		"tryKick",
		"doRevokeInvite",
		"changeRole",
		"setOptions",
		"trySelfAssignAdminRole",
		"clearMessagesForAll",
	];

	// список запрещенных методов по ролям
	public const RESTRICTED_METHOD_LIST_BY_ROLE = [
		Member::ROLE_GUEST => [
			"add",
			"tryKick",
			"getInvited",
			"doRevokeInvite",
			"changeRole",
			"setOptions",
			"setBaseInfo",
			"trySelfAssignAdminRole",
			"clearMessagesForAll",
		],
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод для создания группового диалога
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function add():array {

		$group_name = $this->post("?s", "name");

		$file_map = "";
		$file_key = $this->post("?s", "file_key", "");
		if (mb_strlen($file_key) > 0) {
			$file_map = \CompassApp\Pack\File::tryDecrypt($file_key);
		}

		Gateway_Bus_Statholder::inc("groups", "row4");

		// если в названии есть эмоджи
		if (Type_Api_Validator::isStringContainEmoji($group_name)) {
			return $this->error(546, "Group name or description contains emoji");
		}

		// удаляем лишнее из названия группы и выбрасываем исключение если название некорретное
		$group_name = $this->_tryFilterGroupName($group_name, "groups", "row0");

		// если это не изображение
		if ($file_map !== "" && \CompassApp\Pack\File::getFileType($file_map) !== FILE_TYPE_IMAGE) {
			return $this->error(705, "File is not image");
		}

		// инкрементим блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_ADD, "groups", "row2");

		// создаем групповой диалог
		try {

			if ($this->method_version >= 2) {
				Domain_Member_Entity_Permission::check($this->user_id, Permission::IS_ADD_GROUP_ENABLED);
			}

			$meta_row = Helper_Groups::create($this->user_id, $group_name, $file_map);
		} catch (Domain_Member_Exception_ActionNotAllowed) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		// получаем запись из левого меню
		$left_menu_row = Type_Conversation_LeftMenu::get($this->user_id, $meta_row["conversation_map"]);

		// отдаем ответ
		$prepared_conversation = Type_Conversation_Utils::prepareConversationForFormat($meta_row, $left_menu_row);

		Gateway_Bus_Statholder::inc("groups", "row3");
		Type_User_ActionAnalytics::send($this->user_id, Type_User_ActionAnalytics::ADD_GROUP);

		return $this->ok([
			"conversation" => (object) Apiv1_Format::conversation($prepared_conversation),
		]);
	}

	/**
	 * метод для выхода из диалога
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws ParseFatalException
	 */
	public function doLeave():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		// инкрементим блокировку
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_DOLEAVE, "groups", "row80");

		// получаем мета информацию о диалоге
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// выбрасываем ошибку, если диалог не является групповым
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::LEAVE_FROM_CONVERSATION);

		// возвращаем ок если юзера уже нет в группе
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("groups", "row83");
			return $this->ok();
		}

		// отправляем событие в хелпер чтобы покинуть групповой диалог и оповестить остальных
		try {
			Helper_Groups::doLeave($conversation_map, $this->user_id, $meta_row, true, true);
		} catch (cs_OwnerTryToLeaveGeneralConversation) {
			return $this->error(517, "Owner trying to leave general conversation");
		} catch (cs_OwnerTryToLeaveRespectConversation) {
			return $this->error(2104001, "Owner trying to leave respect conversation");
		}

		return $this->ok();
	}

	/**
	 * метод для изгнания пользователя из диалога
	 *
	 * @throws \blockException
	 * @throws cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @long
	 */
	public function tryKick():array {

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
		$user_id          = $this->post(\Formatter::TYPE_INT, "user_id");

		// проверяем user_id на корректность
		$this->_throwIfUserIdIsMalformed($user_id);
		$this->_throwIfUserIdIsEqualWithYourself($user_id);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_TRYKICK);

		// получаем информацию о пользователе которого хотим кикнуть (чтобы отдать 400 если такого нет)
		$user_info = $this->_getUserInfo($user_id);

		// получаем мету диалога; проверяем что диалог является группой
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::KICK_FROM_CONVERSATION);

		// проверяем что мы и тот кого пытаемся кикнуть - участники группы
		$this->_throwIfUserIsNotConversationMember($meta_row, $this->user_id);
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {
			return $this->error(501, "User is not conversation member");
		}

		// наш пользователь не имеет права кикнуть участника
		if (!Type_Conversation_Meta_Users::isGroupAdmin($this->user_id, $meta_row["users"])) {
			return $this->_return514ErrorWithUserAndOpponentRoles($user_id, $meta_row["users"]);
		}

		// если кикают из группы бота
		$userbot_id = "";
		if (Type_User_Main::isUserbot($user_info->npc_type)) {
			$userbot_id = Gateway_Socket_Company::kickUserbotFromGroup($user_id, $conversation_map);
		}

		Helper_Groups::doUserKick($meta_row, $user_id, true, $userbot_id);

		return $this->ok();
	}

	/**
	 * метод для получения диалогов, в которых пользователь имеет права администратора
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getManaged():array {

		$offset = $this->post("?i", "offset", 0);

		Gateway_Bus_Statholder::inc("groups", "row180");

		// валидируем параметры
		if ($offset < 0) {

			Gateway_Bus_Statholder::inc("groups", "row183");
			return $this->_returnLeftMenuMethodOk();
		}

		// получаем список диалогов, в которых пользователь admin или owner
		$left_menu_list          = Type_Conversation_LeftMenu::getManaged($this->user_id, self::_GET_MANAGED_COUNT, $offset);
		$prepared_left_menu_list = self::_prepareLeftMenuListForGetManaged($left_menu_list);

		Gateway_Bus_Statholder::inc("groups", "row181");
		return $this->_returnLeftMenuMethodOk($prepared_left_menu_list, self::_GET_MANAGED_COUNT);
	}

	/**
	 * Форматируем список для метода getManaged
	 *
	 */
	protected static function _prepareLeftMenuListForGetManaged(array $left_menu_list):array {

		$prepared_left_menu_list = [];
		foreach ($left_menu_list as $v) {

			// пропускаем чат найма
			if (Type_Conversation_Meta::isHiringConversation($v["type"])) {
				continue;
			}

			// пропускаем чат заметки
			if (Type_Conversation_Meta::isNotesConversationType($v["type"])) {
				continue;
			}

			// пропускаем чат службы поддержки
			if (Type_Conversation_Meta::isGroupSupportConversationType($v["type"])) {
				continue;
			}

			$prepared_left_menu_list[] = $v;
		}

		return $prepared_left_menu_list;
	}

	/**
	 * Метод для получения списка приглашенных в диалог пользователей
	 *
	 * @throws ParamException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 */
	public function getInvited():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		try {
			$member_id_list = Domain_Conversation_Scenario_Api::getInvited($this->user_id, $this->role, $this->method_version, $conversation_map);
		} catch (Domain_Member_Exception_ActionNotAllowed|UserIsGuest) {
			return $this->error(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action not allowed");
		}

		$this->action->users($member_id_list);
		return $this->ok([
			"member_list" => (array) $member_id_list,
		]);
	}

	/**
	 * метод получения общих с пользователем групповых диалогов
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \paramException
	 */
	public function getShared():array {

		$opponent_user_id = $this->post("?i", "user_id");

		if ($opponent_user_id < 1) {

			throw new ParamException("invalid user_id");
		}

		// получаем список общих с пользователем групповых диалогов
		try {
			$shared_group_list = Domain_Conversation_Scenario_Api::getShared($this->user_id, $opponent_user_id);
		} catch (cs_IncorrectUserId) {
			throw new ParamException("Incorrect user_id");
		}

		// форматируем сущности и собираем ответ
		$output = [];
		foreach ($shared_group_list as $left_menu_row) {

			// подготавливаем и форматируем сущность left_menu
			$temp     = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);
			$output[] = Apiv1_Format::leftMenu($temp);
		}

		return $this->ok([
			"left_menu_list" => (array) $output,
		]);
	}

	/**
	 * метод для отзыва приглашения в групповой диалог
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function doRevokeInvite():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		$user_id = $this->post("?i", "user_id");

		$this->_throwIfParamsIsNotCorrect($user_id);
		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_DOREVOKEINVITE, "groups", "row430");

		$meta_row = Type_Conversation_Meta::get($conversation_map);
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::REVOKE_INVITE_FROM_CONVERSATION);
		$this->_throwIfUserIsNotConversationMember($meta_row, $this->user_id, "groups", "row425");

		if (!Type_Conversation_Meta_Users::isGroupAdmin($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("groups", "row426");
			return $this->error(514, "You are not allowed to do this action");
		}

		// отзываем инвайт
		try {
			Helper_Invites::setRevoked($user_id, $meta_row);
		} catch (cs_InviteIsAccepted) {
			return $this->error(535, "User accepted invitation");
		} catch (cs_InviteIsNotActive) {

			Gateway_Bus_Statholder::inc("groups", "row428");
			return $this->ok();
		} catch (cs_InviteStatusIsNotExpected) {
			return $this->_returnForRevokeIfInviteStatusIsNotExpected($conversation_map, $this->user_id, $user_id);
		}

		Gateway_Bus_Statholder::inc("groups", "row429");
		return $this->ok();
	}

	// проверяем параметры
	protected function _throwIfParamsIsNotCorrect(int $user_id):void {

		// проверяет что присланный user_id - корректный и не равен user_id совершающему запрос
		$this->_throwIfUserIdIsMalformed($user_id, "groups", "row422");
		$this->_throwIfUserIdIsEqualWithYourself($user_id, "groups", "row423");
	}

	// отдаем ответ при некорректном статусе
	protected function _returnForRevokeIfInviteStatusIsNotExpected(string $conversation_map, int $sender_user_id, int $user_id):array {

		Gateway_Bus_Statholder::inc("groups", "row431");
		$invite_row = Type_Conversation_Invite::getByConversationMapAndUserId($sender_user_id, $user_id, $conversation_map);

		// взависимости от статуса
		if ($invite_row["status"] == Type_Invite_Handler::STATUS_ACCEPTED) {
			return $this->error(535, "User accepted invitation");
		}

		return $this->ok();
	}

	/**
	 * меняем роль пользователя в группе
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function changeRole():array {

		$conversation_key = $this->post("?s", "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		$user_id = $this->post("?i", "user_id");
		$role    = $this->post("?s", "role");

		Gateway_Bus_Statholder::inc("groups", "row450");

		// проверяем user_id и новую роль на корректность
		$this->_throwIfUserIdIsMalformed($user_id, "groups", "row440");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_CHANGEROLE, "groups", "row443");

		$user_info = $this->_getUserInfo($user_id);
		$this->_throwIfUserGuest($user_info->role);
		if (!Type_User_Main::isHuman($user_info->npc_type)) {
			throw new ParamException("user is not human");
		}

		// проверяем, что собеседник является участником диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("groups", "row448");
			return $this->error(501, "User is not conversation member");
		}

		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::CHANGE_ROLE_USER_FROM_CONVERSATION);
		$this->_throwIfUserIsNotConversationMember($meta_row, $this->user_id, "groups", "row446");

		$new_role = $this->_tryGetNewRole($role, Type_Conversation_Meta_Users::getRole($user_id, $meta_row["users"]));

		// проверяем, что имеем право изменить роль участнику
		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])
			|| !Type_Conversation_Meta_Users::isGroupAdmin($this->user_id, $meta_row["users"])) {

			Gateway_Bus_Statholder::inc("groups", "row447");
			return $this->_return514ErrorWithUserAndOpponentRoles($user_id, $meta_row["users"]);
		}

		return $this->_tryChangeRole($conversation_map, $meta_row["users"], $user_id, $new_role);
	}

	/**
	 * выбрасываем исключение, если пользователь гость
	 *
	 * @param int $role
	 *
	 * @throws CaseException
	 */
	protected function _throwIfUserGuest(int $role):void {

		try {
			Member::assertUserNotGuest($role);
		} catch (UserIsGuest) {
			throw new CaseException(Permission::ACTION_NOT_ALLOWED_ERROR_CODE, "action is not allowed");
		}
	}

	/**
	 * пробуем получить роль
	 *
	 * @throws \paramException
	 *
	 * @long большой switch
	 */
	protected function _tryGetNewRole(string $new_role, int $old_role):int {

		switch ($new_role) {

			case "owner":
				$new_role = Type_Conversation_Meta_Users::ROLE_OWNER;
				break;

			case "admin":

				if ($old_role === Type_Conversation_Meta_Users::ROLE_OWNER) {

					$new_role = Type_Conversation_Meta_Users::ROLE_DEFAULT;
					break;
				}

				if ($old_role === Type_Conversation_Meta_Users::ROLE_DEFAULT || $old_role === Type_Conversation_Meta_Users::ROLE_ADMIN) {

					$new_role = Type_Conversation_Meta_Users::ROLE_OWNER;
					break;
				}
				throw new ParamException("incorrect param role");

			case "member":
				$new_role = Type_Conversation_Meta_Users::ROLE_DEFAULT;
				break;

			default:

				Gateway_Bus_Statholder::inc("groups", "row442");
				throw new ParamException("incorrect param role");
		}

		return $new_role;
	}

	// пробуем поменять роль пользователю
	protected function _tryChangeRole(string $conversation_map, array $users, int $user_id, int $new_role):array {

		// проверяем, быть может собеседник уже имеет нужную роль
		if (Type_Conversation_Meta_Users::getRole($user_id, $users) == $new_role) {

			Gateway_Bus_Statholder::inc("groups", "row451");
			return $this->ok();
		}

		Helper_Groups::setRole($conversation_map, $user_id, $new_role);

		// отправляем участникам группы событие об изменении роли в групповом диалоге
		$this->_sendWsNewAndOldClientAfterChangeRole($conversation_map, $user_id, $users, $new_role);

		Gateway_Bus_Statholder::inc("groups", "row449");
		return $this->ok();
	}

	// отправляем участникам группы событие об изменении роли в групповом диалоге
	protected function _sendWsNewAndOldClientAfterChangeRole(string $conversation_map, int $user_id, array $users, int $new_role):void {

		$previous_user_role = Type_Conversation_Meta_Users::getRole($user_id, $users);
		$talking_user_list  = Type_Conversation_Meta_Users::getTalkingUserList($users);
		Gateway_Bus_Sender::conversationGroupRoleChanged($talking_user_list, $conversation_map, $user_id, $new_role, $previous_user_role);
	}

	/**
	 * метод для самоназначения администратором в группе
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function trySelfAssignAdminRole():array {

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_TRYSELFASSIGNADMIN);

		try {
			Domain_Group_Scenario_Api::trySelfAssignAdminRole($conversation_map, $this->user_id, $this->role, $this->permissions);
		} catch (cs_UserIsNotMember) {
			return $this->error(501, "User is not conversation member");
		} catch (cs_Conversation_IsGroupIfOwnerExist) {
			return $this->error(516, "Administrator has already been assigned to the group");
		} catch (cs_RequestedActionIsNotAble) {
			throw new ParamException("You can not perform this action with this conversation");
		} catch (UserIsGuest) {
			return $this->error(2238001, "action is not allowed");
		}

		return $this->ok();
	}

	/**
	 * установить опции для группового диалога
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \blockException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function setOptions():array {

		$conversation_key                                = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$conversation_map                                = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
		$is_show_history_for_new_members                 = $this->post(\Formatter::TYPE_INT, "is_show_history_for_new_members", false);
		$is_can_commit_worked_hours                      = $this->post(\Formatter::TYPE_INT, "is_can_commit_worked_hours", false);
		$need_system_message_on_dismissal                = $this->post(\Formatter::TYPE_INT, "need_system_message_on_dismissal", false);
		$is_need_show_system_message_on_invite_and_join  = $this->post(\Formatter::TYPE_INT, "is_need_show_system_message_on_invite_and_join", false);
		$is_need_show_system_message_on_leave_and_kicked = $this->post(\Formatter::TYPE_INT, "is_need_show_system_message_on_leave_and_kicked", false);
		$is_need_show_system_deleted_message             = $this->post(\Formatter::TYPE_INT, "is_need_show_system_deleted_message", false);

		// проверяем и подготавливаем изменяемые опции
		$modifiable_options = self::_checkAndPrepareModifiableOptions(
			$is_show_history_for_new_members, $is_can_commit_worked_hours, $need_system_message_on_dismissal,
			$is_need_show_system_message_on_invite_and_join, $is_need_show_system_message_on_leave_and_kicked, $is_need_show_system_deleted_message
		);

		// инкрементим блокировку
		$this->_throwIfBlocked(
			$is_show_history_for_new_members, $is_can_commit_worked_hours, $need_system_message_on_dismissal,
			$is_need_show_system_message_on_invite_and_join, $is_need_show_system_message_on_leave_and_kicked, $is_need_show_system_deleted_message
		);

		// получаем мету диалога
		$meta_row = Type_Conversation_Meta::get($conversation_map);

		// проверяем, можем ли мы совершать действия над диалогом такого типа
		Type_Conversation_Action::assertAction((int) $meta_row["type"], Type_Conversation_Action::SET_OPTIONS_FROM_CONVERSATION);

		// выбрасываем ошибку, если пользователь не является участником группы
		if (!Type_Conversation_Meta_Users::isMember($this->user_id, $meta_row["users"])) {
			return $this->error(501, "User is not conversation member");
		}

		// выбрасываем ошибку, если пользователь не имеет прав на изменение опций группы
		if (!Type_Conversation_Meta_Users::isGroupAdmin($this->user_id, $meta_row["users"])) {
			return $this->error(514, "You are not allowed to do this action");
		}

		// меняем опции группы
		Helper_Groups::doChangeOptions($conversation_map, $meta_row, $modifiable_options);

		return $this->ok();
	}

	/**
	 * инкрементим блокировку и выбрасываем exception, если заблокированы
	 *
	 * @param int|false $is_show_history_for_new_members
	 * @param int|false $is_can_commit_worked_hours
	 * @param int|false $need_system_message_on_dismissal
	 * @param int|false $is_need_show_system_message_on_invite_and_join
	 * @param int|false $is_need_show_system_message_on_leave_and_kicked
	 *
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \blockException
	 * @throws \parseException
	 */
	protected function _throwIfBlocked(int|false $is_show_history_for_new_members, int|false $is_can_commit_worked_hours, int|false $need_system_message_on_dismissal,
						     int|false $is_need_show_system_message_on_invite_and_join, int|false $is_need_show_system_message_on_leave_and_kicked,
						     int|false $is_need_show_system_deleted_message):void {

		if ($is_show_history_for_new_members !== false) {
			Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_SETOPTIONS_IS_SHOW_HISTORY, "groups", "row525");
		}

		if ($is_can_commit_worked_hours !== false) {
			Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_SETOPTIONS_IS_CAN_COMMIT, "groups", "row525");
		}

		if ($need_system_message_on_dismissal !== false) {
			Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_SETOPTIONS_IS_SHOW_SYSTEM_MESSAGE, "groups", "row525");
		}

		if ($is_need_show_system_message_on_invite_and_join !== false) {
			self::_incBlockSystemMessageOnInviteAndJoin($this->user_id, $is_need_show_system_message_on_invite_and_join);
		}

		if ($is_need_show_system_message_on_leave_and_kicked !== false) {
			self::_incBlockSystemMessageOnLeaveAndKicked($this->user_id, $is_need_show_system_message_on_leave_and_kicked);
		}

		if ($is_need_show_system_deleted_message !== false) {
			self::_incBlockSystemDeletedMessage($this->user_id, $is_need_show_system_deleted_message);
		}
	}

	/**
	 * инкрементим блокировку
	 *
	 * @param int $user_id
	 * @param int $is_need_show_system_message_on_invite_and_join
	 *
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 */
	protected static function _incBlockSystemMessageOnInviteAndJoin(int $user_id, int $is_need_show_system_message_on_invite_and_join):void {

		if ($is_need_show_system_message_on_invite_and_join == 1) {

			Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_INVITE_AND_JOIN_ON);
			return;
		}

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_INVITE_AND_JOIN_OFF);
	}

	/**
	 * инкрементим блокировку
	 *
	 * @param int $user_id
	 * @param int $is_need_show_system_message_on_leave_and_kicked
	 *
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 */
	protected static function _incBlockSystemMessageOnLeaveAndKicked(int $user_id, int $is_need_show_system_message_on_leave_and_kicked):void {

		if ($is_need_show_system_message_on_leave_and_kicked == 1) {

			Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_LEAVE_AND_KICKED_ON);
			return;
		}

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_MESSAGE_ON_LEAVE_AND_KICKED_OFF);
	}

	/**
	 * инкрементим блокировку
	 *
	 * @param int $user_id
	 * @param int $is_need_show_system_deleted_message
	 *
	 * @throws \BaseFrame\Exception\Request\BlockException
	 * @throws \parseException
	 */
	protected static function _incBlockSystemDeletedMessage(int $user_id, int $is_need_show_system_deleted_message):void {

		if ($is_need_show_system_deleted_message == 1) {

			Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_DELETED_MESSAGE_ON);
			return;
		}

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::GROUPS_SETOPTIONS_IS_NEED_SHOW_SYSTEM_DELETED_MESSAGE_OFF);
	}

	/**
	 * подготавливаем массив только лишь с изменяемыми опциями
	 *
	 * @param int|false $is_show_history_for_new_members
	 * @param int|false $is_for_worked_hours
	 * @param int|false $need_system_message_on_dismissal
	 * @param int|false $is_need_show_system_message_on_invite_and_join
	 * @param int|false $is_need_show_system_message_on_leave_and_kicked
	 * @param int|false $is_need_show_system_deleted_message
	 *
	 * @return array
	 * @throws \paramException
	 */
	protected static function _checkAndPrepareModifiableOptions(int|false $is_show_history_for_new_members, int|false $is_for_worked_hours,
											int|false $need_system_message_on_dismissal, int|false $is_need_show_system_message_on_invite_and_join,
											int|false $is_need_show_system_message_on_leave_and_kicked, int|false $is_need_show_system_deleted_message):array {

		// временно сложим их сюда
		$temp = [
			"is_show_history_for_new_members"                 => $is_show_history_for_new_members,
			"is_can_commit_worked_hours"                      => $is_for_worked_hours,
			"need_system_message_on_dismissal"                => $need_system_message_on_dismissal,
			"is_need_show_system_message_on_invite_and_join"  => $is_need_show_system_message_on_invite_and_join,
			"is_need_show_system_message_on_leave_and_kicked" => $is_need_show_system_message_on_leave_and_kicked,
			"is_need_show_system_deleted_message"             => $is_need_show_system_deleted_message,
		];

		// проходимся по каждой опции и собираем массив с только лишь измененными опциями
		$output = [];
		foreach ($temp as $k => $v) {

			// если значение === false, то идем дальше
			if ($v === false) {
				continue;
			}

			// проверяем, что передали флаг
			if (!in_array($v, [0, 1])) {
				throw new ParamException(__METHOD__ . ": passed incorrect value for parameter `{$k}`");
			}

			$output[$k] = $v == 1;
		}

		// если не прислали опции для изменений
		if (count($output) < 1) {
			throw new ParamException("incorrect params is_show_history_for_new_members and is_can_commit_worked_hours");
		}

		return $output;
	}

	/**
	 * метод для очистки сообщений в диалоге для всех участников группы
	 *
	 * @return array
	 * @throws BlockException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public function clearMessagesForAll():array {

		$conversation_key = $this->post(\Formatter::TYPE_STRING, "conversation_key");
		$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::GROUPS_CLEARMESSAGESFORALL);

		try {
			Domain_Group_Scenario_Api::clearMessagesForAll($this->user_id, $conversation_map);
		} catch (cs_UserIsNotAdmin) {
			return $this->error(548, "The user is not a group administrator");
		}

		return $this->ok();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// очищаем имя группы, выкидываем \paramException если прислали некорректные данные
	protected function _tryFilterGroupName(string $group_name, string $namespace = null, string $row = null):string {

		// форматируем название группового диалога
		$group_name = Type_Api_Filter::sanitizeGroupName($group_name);

		// проверяем название группового диалога на корректность
		if (!Type_Api_Validator::isGroupName($group_name)) {

			// пишем статистику, если нужно
			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException("incorrect group name");
		}

		return $group_name;
	}

	// проверяем что пользователь является участником диалога
	protected function _throwIfUserIsNotConversationMember(array $meta_row, int $user_id, string $namespace = null, string $row = null):void {

		if (!Type_Conversation_Meta_Users::isMember($user_id, $meta_row["users"])) {

			// пишем статистику, если нужно
			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException("User is not group member");
		}
	}

	// проверяет что присланный user_id - корректный
	protected function _throwIfUserIdIsMalformed(int $user_id, string $namespace = null, string $row = null):void {

		// проверяем user_id
		if ($user_id < 1) {

			// пишем статистику, если нужно
			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException("Passed invalid user_id");
		}
	}

	// проверяет что присланный user_id не равен user_id пользователя совершающего запрос
	protected function _throwIfUserIdIsEqualWithYourself(int $user_id, string $namespace = null, string $row = null):void {

		if ($user_id == $this->user_id) {

			// пишем статистику, если нужно
			if (!is_null($namespace) && !is_null($row)) {
				Gateway_Bus_Statholder::inc($namespace, $row);
			}

			throw new ParamException("Passed yourself in user_id parameter");
		}
	}

	// получаем информацию о пользователе
	protected function _getUserInfo(int $user_id):\CompassApp\Domain\Member\Struct\Short {

		$user_info_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id], false);
		if (!isset($user_info_list[$user_id])) {
			throw new ParamException("dont found user in company cache");
		}
		return $user_info_list[$user_id];
	}

	// форматируем список диалогов для ответа
	protected function _returnLeftMenuMethodOk(array $left_menu_list = [], int $max_count = 0):array {

		// если пришел пустой список
		if (count($left_menu_list) < 1) {

			// возвращаем пустой ответ
			return $this->ok([
				"left_menu_list" => (array) [],
				"has_next"       => (int) 0,
			]);
		}

		// определяем has_next
		$has_next = count($left_menu_list) == $max_count ? 1 : 0;

		// форматируем сущности и собираем ответ
		$output = [];
		foreach ($left_menu_list as $left_menu_row) {

			// если это легаси тип, то скипаем
			if (Domain_Conversation_Entity_LegacyTypes::isLegacy($left_menu_row["type"])) {
				continue;
			}

			// подготавливаем и форматируем сущность left_menu
			$temp     = Type_Conversation_Utils::prepareLeftMenuForFormat($left_menu_row);
			$output[] = Apiv1_Format::leftMenu($temp);
		}

		return $this->ok([
			"left_menu_list" => (array) $output,
			"has_next"       => (int) $has_next,
		]);
	}

	// метод возвращает 514 вместе с ролями пользователя и собеседника
	protected function _return514ErrorWithUserAndOpponentRoles(int $opponent_user_id, array $users):array {

		// получаем наш и собеседника роли в группе
		$user_role          = Type_Conversation_Meta_Users::getRole($this->user_id, $users);
		$opponent_user_role = Type_Conversation_Meta_Users::getRole($opponent_user_id, $users);

		return $this->error(514, "You are not allowed to do this action", [
			"user_role"     => Apiv1_Format::getUserRole($user_role),
			"opponent_role" => Apiv1_Format::getUserRole($opponent_user_role),
		]);
	}
}
