<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * контроллер для работы учатниками компании
 */
class Socket_Company_Member extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"addCreator",
		"addByRole",
		"setMemberCount",
		"updateUserInfo",
		"updateMemberInfo",
		"getUserInfo",
		"getUserRoleList",
		"logoutUserSessionList",
		"kick",
		"getListOfActiveMembersByDay",
		"logoutAll",
		"checkIsAllowedForHiringHistory",
		"getMemberCount",
		"checkCanEditSpaceSettings",
		"checkCanAttachSpace",
		"checkIsOwner",
		"deleteUser",
		"getActivityCountList",
		"getAll",
		"setPermissions",
	];

	/**
	 * Метод для добавления создателя компании
	 *
	 * @long
	 * @throws ParamException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 */
	public function addCreator():array {

		$pin_code                             = $this->post(\Formatter::TYPE_STRING, "pin_code", false);
		$full_name                            = $this->post(\Formatter::TYPE_STRING, "full_name");
		$avatar_file_key                      = $this->post(\Formatter::TYPE_STRING, "avatar_file_key");
		$avatar_color_id                      = $this->post(\Formatter::TYPE_INT, "avatar_color_id");
		$npc_type                             = $this->post(\Formatter::TYPE_INT, "npc_type", Type_User_Main::NPC_TYPE_HUMAN);
		$is_force_exit_task_not_exist         = $this->post(\Formatter::TYPE_INT, "is_force_exit_task_not_exist") == 1;
		$locale                               = $this->post(\Formatter::TYPE_STRING, "locale");
		$is_trial_activated                   = $this->post(\Formatter::TYPE_INT, "is_trial_activated") == 1;
		$is_need_create_intercom_conversation = $this->post(\Formatter::TYPE_INT, "is_need_create_intercom_conversation") == 1;
		$ip                                   = $this->post(\Formatter::TYPE_STRING, "ip");
		$user_agent                           = $this->post(\Formatter::TYPE_STRING, "user_agent");
		$avg_screen_time                      = $this->post(\Formatter::TYPE_INT, "avg_screen_time", 0);
		$total_action_count                   = $this->post(\Formatter::TYPE_INT, "total_action_count", 0);
		$avg_message_answer_time              = $this->post(\Formatter::TYPE_INT, "avg_message_answer_time", 0);

		if ($is_force_exit_task_not_exist && !isTestServer()) {
			throw new ParamException("only for test-server");
		}

		try {

			[$entry_id, $token, $role, $permissions] = Domain_User_Scenario_Socket::addCreator(
				$this->user_id,
				$pin_code,
				$full_name,
				$avatar_file_key,
				$avatar_color_id,
				$npc_type,
				$is_force_exit_task_not_exist,
				$locale,
				$is_trial_activated,
				$is_need_create_intercom_conversation,
				$ip,
				$user_agent,
				$avg_screen_time,
				$total_action_count,
				$avg_message_answer_time
			);
		} catch (cs_UsersFromSingleListErrorOrUserCannotAddToGroups $e) {

			Type_System_Admin::log("socket_member_add_creator", ["getUsersGroupsOkErrorList" => $e->getUsersGroupsOkErrorList()]);
			throw new ReturnFatalException("Users in single dialogs, or the user cannot invite to groups");
		} catch (cs_MemberExitTaskInProgress) {
			return $this->error(1220, "user has not finished exit the company yet");
		}

		return $this->ok([
			"entry_id"    => (int) $entry_id,
			"token"       => (string) $token,
			"role"        => (int) $role,
			"permissions" => (int) $permissions,
		]);
	}

	/**
	 * Метод для добавления участника компании с указанной ролью
	 *
	 * @long
	 * @throws ParamException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\LocaleTextNotFound
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 */
	public function addByRole():array {

		$user_role                    = $this->post(\Formatter::TYPE_INT, "user_role", \CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER);
		$pin_code                     = $this->post(\Formatter::TYPE_STRING, "pin_code", false);
		$full_name                    = $this->post(\Formatter::TYPE_STRING, "full_name");
		$avatar_file_key              = $this->post(\Formatter::TYPE_STRING, "avatar_file_key");
		$avatar_color_id              = $this->post(\Formatter::TYPE_INT, "avatar_color_id");
		$npc_type                     = $this->post(\Formatter::TYPE_INT, "npc_type", Type_User_Main::NPC_TYPE_HUMAN);
		$is_force_exit_task_not_exist = $this->post(\Formatter::TYPE_INT, "is_force_exit_task_not_exist") == 1;
		$locale                       = $this->post(\Formatter::TYPE_STRING, "locale");
		$is_trial_activated           = $this->post(\Formatter::TYPE_INT, "is_trial_activated") == 1;
		$ip                           = $this->post(\Formatter::TYPE_STRING, "ip");
		$user_agent                   = $this->post(\Formatter::TYPE_STRING, "user_agent");
		$avg_screen_time              = $this->post(\Formatter::TYPE_INT, "avg_screen_time", 0);
		$total_action_count           = $this->post(\Formatter::TYPE_INT, "total_action_count", 0);
		$avg_message_answer_time      = $this->post(\Formatter::TYPE_INT, "avg_message_answer_time", 0);

		if ($is_force_exit_task_not_exist && !isTestServer()) {
			throw new ParamException("only for test-server");
		}

		try {

			[$entry_id, $token, $role, $permissions] = Domain_User_Scenario_Socket::addByRole(
				$this->user_id,
				$user_role,
				$pin_code,
				$full_name,
				$avatar_file_key,
				$avatar_color_id,
				$npc_type,
				$is_force_exit_task_not_exist,
				$locale,
				$is_trial_activated,
				$ip,
				$user_agent,
				$avg_screen_time,
				$total_action_count,
				$avg_message_answer_time
			);
		} catch (cs_UsersFromSingleListErrorOrUserCannotAddToGroups $e) {

			Type_System_Admin::log("socket_member_add_by_role", ["getUsersGroupsOkErrorList" => $e->getUsersGroupsOkErrorList()]);
			throw new ReturnFatalException("Users in single dialogs, or the user cannot invite to groups");
		} catch (cs_MemberExitTaskInProgress) {
			return $this->error(1220, "user has not finished exit the company yet");
		}

		return $this->ok([
			"entry_id"    => (int) $entry_id,
			"token"       => (string) $token,
			"role"        => (int) $role,
			"permissions" => (int) $permissions,
		]);
	}

	/**
	 * Метод для исключения участника из компании.
	 *
	 * @throws
	 * @post kicked_user_id
	 */
	public function kick():array {

		$kicked_user_id = $this->post(\Formatter::TYPE_INT, "kicked_user_id");

		Domain_User_Scenario_Socket::kick($this->user_id, $kicked_user_id);

		return $this->ok();
	}

	/**
	 * Метод для установки количества участников компании
	 *
	 * @throws \Exception
	 */
	public function setMemberCount():array {

		$member_count = $this->post(\Formatter::TYPE_INT, "member_count");
		$guest_count  = $this->post(\Formatter::TYPE_INT, "guest_count");

		Domain_User_Scenario_Socket::setUsersCount($member_count, $guest_count);

		return $this->ok();
	}

	/**
	 * Метод для получения количества участников компании
	 *
	 * @throws \Exception
	 */
	public function getMemberCount():array {

		$member_count = Domain_User_Scenario_Socket::getMemberCount();

		return $this->ok([
			"member_count" => (int) $member_count,
		]);
	}

	/**
	 * Обновляем информацию пользователя
	 *
	 * @return array
	 * @throws ParamException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public function updateUserInfo():array {

		$user_id                 = $this->post(\Formatter::TYPE_INT, "user_id");
		$full_name               = $this->post(\Formatter::TYPE_STRING, "full_name");
		$avatar_file_key         = $this->post(\Formatter::TYPE_STRING, "avatar_file_key");
		$avatar_color_id         = $this->post(\Formatter::TYPE_INT, "avatar_color_id");
		$avg_screen_time         = $this->post(\Formatter::TYPE_INT, "avg_screen_time");
		$total_action_count      = $this->post(\Formatter::TYPE_INT, "total_action_count");
		$avg_message_answer_time = $this->post(\Formatter::TYPE_INT, "avg_message_answer_time");
		$profile_created_at      = $this->post(\Formatter::TYPE_INT, "profile_created_at");
		$client_launch_uuid      = $this->post(\Formatter::TYPE_STRING, "client_launch_uuid", "");
		$is_deleted              = $this->post(\Formatter::TYPE_INT, "is_deleted", 0);
		$disabled_at             = $this->post(\Formatter::TYPE_INT, "disabled_at", 0);

		// обновляем информацию пользователя
		Domain_User_Scenario_Socket::updateUserInfo($user_id, $full_name, $avatar_file_key, $avatar_color_id, $avg_screen_time, $total_action_count,
			$avg_message_answer_time, $profile_created_at, $disabled_at, $client_launch_uuid, $is_deleted);

		return $this->ok();
	}

	/**
	 * Обновляем данные карточки в компании пользователя
	 *
	 * @return array
	 * @throws ParamException
	 * @throws \cs_UserIsNotMember
	 */
	public function updateMemberInfo():array {

		$user_id        = $this->post(\Formatter::TYPE_INT, "user_id");
		$description    = $this->post(\Formatter::TYPE_STRING, "description", false);
		$status         = $this->post(\Formatter::TYPE_STRING, "status", false);
		$badge_content  = $this->post(\Formatter::TYPE_STRING, "badge_content", false);
		$badge_color_id = $this->post(\Formatter::TYPE_INT, "badge_color_id", false);

		Domain_User_Scenario_Socket::updateMemberInfo($user_id, $description, $status, $badge_content, $badge_color_id);

		return $this->ok();
	}

	/**
	 * Получаем информацию о пользователе
	 */
	public function getUserInfo():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		// получаем информацию о пользователе
		try {
			$user_info = Gateway_Bus_CompanyCache::getMember($user_id);
		} catch (\cs_RowIsEmpty) {
			return $this->error(2305001, "not found user in company");
		}

		return $this->ok([
			"user" => (object) [
				"description" => $user_info->short_description,
				"badge"       => \CompassApp\Domain\Member\Entity\Extra::getBadgeContent($user_info->extra),
				"role"        => $user_info->role,
			],
		]);
	}

	/**
	 * Получает список id пользователей с ролями
	 *
	 * @throws paramException
	 * @throws \parseException
	 * @post user_id
	 */
	public function getUserRoleList():array {

		$roles = $this->post(\Formatter::TYPE_ARRAY_INT, "roles");

		$result = Domain_User_Scenario_Socket::getUserRoleList($roles);

		return $this->ok([
			"user_role" => (object) Socket_Format::memberRoleList($result),
		]);
	}

	/**
	 * разлогиниваем сессии пользователя
	 *
	 * @throws paramException
	 * @throws \parseException|\busException
	 *
	 * @post user_id
	 */
	public function logoutUserSessionList():array {

		$user_company_session_token_list = $this->post(\Formatter::TYPE_ARRAY, "user_company_session_token_list");

		// удаляем сессии
		Domain_User_Scenario_Socket::logoutUserSessionList($this->user_id, $user_company_session_token_list);

		return $this->ok();
	}

	/**
	 * Возвращаем список активных сотрудников за день
	 *
	 * @throws paramException
	 * @throws \parseException
	 */
	public function getListOfActiveMembersByDay():array {

		$year    = $this->post(\Formatter::TYPE_INT, "year");
		$day_num = $this->post(\Formatter::TYPE_INT, "day_num");

		return $this->ok([
			"user_list" => (array) Domain_Company_Scenario_Socket::getListOfActiveMembersByDay($year, $day_num),
		]);
	}

	/**
	 * Разлогинить всех пользователей
	 *
	 * @throws \busException|\parseException
	 */
	public function logoutAll():array {

		Domain_User_Scenario_Socket::logoutAll();

		return $this->ok();
	}

	/**
	 * Может ли пользователь править настройки компании
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public function checkCanEditSpaceSettings():array {

		$is_owner = Domain_User_Scenario_Socket::checkCanEditSpaceSettings($this->user_id);

		return $this->ok([
			"can_edit_space_settings" => (int) $is_owner,
		]);
	}

	/**
	 * Может ли пользователь привязать компанию в партнерке
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public function checkCanAttachSpace():array {

		$is_owner = Domain_User_Scenario_Socket::checkCanAttachSpace($this->user_id);

		return $this->ok([
			"can_attach_space" => (int) $is_owner,
		]);
	}

	/**
	 * Является ли пользователь владельцем компании
	 *
	 * @return array
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public function checkIsOwner():array {

		$is_owner = Domain_User_Scenario_Socket::checkIsOwner($this->user_id);

		return $this->ok([
			"is_owner" => (int) $is_owner,
		]);
	}

	/**
	 * Удаление аккаунта пользователя
	 *
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function deleteUser():array {

		Domain_User_Scenario_Socket::deleteUser($this->user_id);

		return $this->ok();
	}

	/**
	 * Получаем количество активных пользователей за промежуток времени
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function getActivityCountList():array {

		$from_date_at = $this->post(\Formatter::TYPE_INT, "from_date_at");
		$to_date_at   = $this->post(\Formatter::TYPE_INT, "to_date_at");

		// валидируем параметры
		if ($from_date_at < 1 || $to_date_at < 1) {
			throw new ParamException("passed incorrect params");
		}

		// получаем записи
		$assoc_member_count_active_list = Domain_Member_Scenario_Socket::getActivityCountList($from_date_at, $to_date_at, true);
		return $this->ok([
			"assoc_member_count_active_list" => (array) $assoc_member_count_active_list,
		]);
	}

	/**
	 * Получаем участников пространства за все время
	 *
	 * @return array
	 */
	public function getAll():array {

		// получаем всех участников за все время
		$member_list = Domain_Member_Scenario_Socket::getAll();

		return $this->ok([
			"member_list" => (array) $member_list,
		]);
	}

	/**
	 * Устанавливаем разрешения участнику пространства
	 *
	 * Внимание! Метод работает даже с гостями, так что нужно быть аккуратным
	 *
	 * @return array
	 */
	public function setPermissions():array {

		$permissions = $this->post(\Formatter::TYPE_JSON, "permissions", []);

		try {
			Domain_Member_Scenario_Socket::setPermissions($this->user_id, $permissions);
		} catch (\cs_CompanyUserIncorrectRole) {
			throw new ParamException("incorrect role");
		} catch (\cs_RowIsEmpty|\CompassApp\Domain\Member\Exception\IsLeft) {
			throw new \BaseFrame\Exception\Request\CaseException(2209006, "member not found");
		} catch (\CompassApp\Domain\Member\Exception\AccountDeleted) {
			throw new \BaseFrame\Exception\Request\CaseException(2209007, "member deleted account");
		} catch (Domain_Member_Exception_IncorrectUserId) {
			throw new ParamException("incorrect user_id");
		}

		return $this->ok();
	}
}
