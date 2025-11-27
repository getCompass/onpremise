<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\GatewayException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\CompanyNotServedException;
use BaseFrame\Exception\Request\ParamException;
use cs_SocketRequestIsFailed;
use JetBrains\PhpStorm\ArrayShape;
use parseException;
use returnException;

/**
 * задача класса общаться между php_pivot -> php_company
 */
class Gateway_Socket_Company {

	/**
	 * Добавляем создателя в компанию
	 *
	 * @long
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function addCreator(
		int    $user_id, int $company_id, int $npc_type, string $domino_id, string $private_key,
		string $full_name, string $avatar_file_map, int $avatar_color_id, string $locale, bool $is_trial_activated, bool $is_need_create_intercom_conversation,
		string $ip, string $user_agent, int $avg_screen_time, int $total_action_count, int $avg_message_answer_time, array $ldap_account_data):array {

		// формируем параметры для запроса
		$params = [
			"npc_type"                             => (int) $npc_type,
			"full_name"                            => (string) $full_name,
			"avatar_file_key"                      => (string) (mb_strlen($avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($avatar_file_map) : ""),
			"avatar_color_id"                      => (int) $avatar_color_id,
			"locale"                               => (string) $locale,
			"is_force_exit_task_not_exist"         => (int) (isBackendTest() && Type_System_Testing::isForceExitTaskNotExist()),
			"is_trial_activated"                   => (int) $is_trial_activated,
			"is_need_create_intercom_conversation" => (int) $is_need_create_intercom_conversation,
			"ip"                                   => (string) $ip,
			"user_agent"                           => (string) $user_agent,
			"avg_screen_time"                      => (int) $avg_screen_time,
			"total_action_count"                   => (int) $total_action_count,
			"avg_message_answer_time"              => (int) $avg_message_answer_time,
			"ldap_account_data"                    => (array) $ldap_account_data,
		];

		[$status, $response] = self::_call("company.member.addCreator", $params, $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			if ($response["error_code"] == 1220) {
				throw new cs_ExitTaskInProgress("user has not finished exit the company yet");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
		return [$response["entry_id"], $response["token"], $response["role"], $response["permissions"]];
	}

	/**
	 * Добавляем пользователя с указанной ролью в компанию
	 *
	 * @long
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function addMemberByRole(
		int    $user_id, int $user_role, int $company_id, int $npc_type, string $domino_id, string $private_key,
		string $full_name, string $avatar_file_map, int $avatar_color_id, string $locale, bool $is_trial_activated,
		string $ip, string $user_agent, int $avg_screen_time, int $total_action_count, int $avg_message_answer_time):array {

		// формируем параметры для запроса
		$params = [
			"npc_type"                     => (int) $npc_type,
			"user_role"                    => (int) $user_role,
			"full_name"                    => (string) $full_name,
			"avatar_file_key"              => (string) (mb_strlen($avatar_file_map) > 0 ? Type_Pack_File::doEncrypt($avatar_file_map) : ""),
			"avatar_color_id"              => (int) $avatar_color_id,
			"locale"                       => (string) $locale,
			"is_force_exit_task_not_exist" => (int) (isBackendTest() && Type_System_Testing::isForceExitTaskNotExist()),
			"is_trial_activated"           => (int) $is_trial_activated,
			"ip"                           => (string) $ip,
			"user_agent"                   => (string) $user_agent,
			"avg_screen_time"              => (int) $avg_screen_time,
			"total_action_count"           => (int) $total_action_count,
			"avg_message_answer_time"      => (int) $avg_message_answer_time,
		];

		[$status, $response] = self::_call("company.member.addByRole", $params, $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			if ($response["error_code"] == 1220) {
				throw new cs_ExitTaskInProgress("user has not finished exit the company yet");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
		return [$response["entry_id"], $response["token"], $response["role"], $response["permissions"]];
	}

	/**
	 * Отправляем запрос на получае количества действий
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 * @param int    $from_date_at
	 *
	 * @return int
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getActionCount(int $company_id, string $domino_id, string $private_key, int $from_date_at = 0):int {

		if ($from_date_at == 0) {
			$from_date_at = dayStart();
		}

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("system.getActionCount", [
			"from_date_at" => $from_date_at,
		], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
		return $response["count"];
	}

	/**
	 * Отправляем запрос на статистику действий в пространстве
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_SocketRequestIsFailed
	 */
	public static function getEventCountInfo(int $company_id, string $domino_id, string $private_key):array {

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("space.getEventCountInfo", [], 0, $company_id, $domino_id, $private_key);

		if ($status !== "ok") {

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
		return $response;
	}

	/**
	 * Отправляем запрос на получение количества активных пользователей
	 *
	 * @param int    $company_id
	 * @param int    $from_date_at
	 * @param int    $to_date_at
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getMemberActivityCountList(int $company_id, int $from_date_at, int $to_date_at, string $domino_id, string $private_key):array {

		// отправим запрос на получение количества
		[$status, $response] = self::_call("company.member.getActivityCountList", [
			"from_date_at" => $from_date_at,
			"to_date_at"   => $to_date_at,
		], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}

		return $response["assoc_member_count_active_list"];
	}

	/**
	 * Чистим все таблицы компании
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function clearTables(int $company_id, string $domino_id, string $private_key):void {

		[$status, $response] = self::_call("system.clearTables", [], 0, $company_id, $domino_id, $private_key);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * устанавливаем количество участников компании
	 *
	 * @param int    $company_id
	 * @param int    $member_count
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setMemberCount(int $company_id, int $member_count, int $guest_count, string $domino_id, string $private_key):void {

		$params = [
			"member_count" => $member_count,
			"guest_count"  => $guest_count,
		];
		[$status, $response] = self::_call("company.member.setMemberCount", $params, 0, $company_id, $domino_id, $private_key);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * получаем количество участников компании
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return int
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getMemberCount(int $company_id, string $domino_id, string $private_key):int {

		[$status, $response] = self::_call("company.member.getMemberCount", [], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["member_count"];
	}

	/**
	 * Отправляет запрос на изменение данных пользователя для компании.
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function updateUserInfo(Struct_User_Info $user_info, int $profile_created_at, string $client_launch_uuid, int $is_deleted, int $disabled_at, int $company_id, string $domino_id, string $private_key):void {

		$params = [
			"user_id"                 => $user_info->user_id,
			"full_name"               => $user_info->full_name,
			"avatar_file_key"         => $user_info->avatar_file_map !== "" ? Type_Pack_File::doEncrypt($user_info->avatar_file_map) : "",
			"avatar_color_id"         => $user_info->avatar_color_id,
			"avg_screen_time"         => $user_info->avg_screen_time,
			"total_action_count"      => $user_info->total_action_count,
			"avg_message_answer_time" => $user_info->avg_message_answer_time,
			"profile_created_at"      => $profile_created_at,
			"client_launch_uuid"      => $client_launch_uuid,
			"is_deleted"              => $is_deleted,
			"disabled_at"             => $disabled_at,
		];

		// обновляем данные в компании
		[$status, $response] = self::_call("company.member.updateUserInfo", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Отправляет запрос на изменение данных участника для компании.
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_SocketRequestIsFailed
	 * @long
	 */
	public static function updateMemberInfo(string $domino_id, int $company_id, string $private_key, int $user_id, string|false $description, string|false $status, string|false $badge_content, string|false $badge_color_id):void {

		if ($description === false && $status === false && $badge_content === false && $badge_color_id === false) {
			return;
		}

		$params = [
			"user_id" => $user_id,
		];
		if ($description !== false) {
			$params["description"] = $description;
		}
		if ($status !== false) {
			$params["status"] = $status;
		}
		if ($badge_content !== false) {
			$params["badge_content"] = $badge_content;
		}
		if ($badge_color_id !== false) {
			$params["badge_color_id"] = $badge_color_id;
		}
		[$status, $response] = self::_call("company.member.updateMemberInfo", $params, 0, $company_id, $domino_id, $private_key);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Выполняем действия при создании компании
	 *
	 * @param int                 $creator_user_id
	 * @param string              $company_name
	 * @param Struct_File_Default $default_file_key_list
	 * @param array               $bot_list
	 * @param int                 $company_id
	 * @param int                 $created_at
	 * @param string              $domino_id
	 * @param string              $private_key
	 * @param int                 $hibernation_immunity_till
	 * @param bool                $is_enabled_employee_card
	 * @param string              $creator_locale
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function doActionsOnCreateCompany(
		int    $creator_user_id, string $company_name, Struct_File_Default $default_file_key_list, array $bot_list, int $company_id, int $created_at,
		string $domino_id, string $private_key, int $hibernation_immunity_till, bool $is_enabled_employee_card, string $creator_locale
	):void {

		$ar_post = [
			"creator_user_id"           => $creator_user_id,
			"company_name"              => $company_name,
			"created_at"                => $created_at,
			"default_file_key_list"     => $default_file_key_list->convertToArray(),
			"bot_list"                  => $bot_list,
			"hibernation_immunity_till" => $hibernation_immunity_till,
			"is_enabled_employee_card"  => $is_enabled_employee_card ? 1 : 0,
			"creator_locale"            => $creator_locale,
		];

		// обновляем данные в компании
		[$status, $response] = self::_call("company.main.doActionsOnCreateCompany", $ar_post, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Отправляет запрос на разлогин пользовательских сессий в компании
	 *
	 * @param int    $user_id
	 * @param array  $user_company_session_token_list
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function logoutUserSessionList(int $user_id, array $user_company_session_token_list, int $company_id, string $domino_id, string $private_key):void {

		$params = [
			"user_company_session_token_list" => $user_company_session_token_list,
		];

		// обновляем данные в компании
		[$status, $response] = self::_call("company.member.logoutUserSessionList", $params, $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Разлогинить всех в компании
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function logoutAll(int $company_id, string $domino_id, string $private_key):void {

		// просто всех разлогиниваем
		[$status, $response] = self::_call("company.member.logoutAll", [], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Передает компании команду на исключение пользователя.
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function kickUser(int $user_id, int $company_id, string $domino_id, string $private_key):void {

		$params = [
			"kicked_user_id" => $user_id,
		];

		// обновляем данные в компании
		[$status, $response] = self::_call("company.member.kick", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Получаем список активных сотрудников за день
	 *
	 * @param int    $year
	 * @param int    $day_num
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return int[]
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getListOfActiveMembersByDay(int $year, int $day_num, int $company_id, string $domino_id, string $private_key):array {

		$params = [
			"year"    => $year,
			"day_num" => $day_num,
		];

		// обновляем данные в компании
		[$status, $response] = self::_call("company.member.getListOfActiveMembersByDay", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["user_list"];
	}

	/**
	 * Отправляет запрос на разлогин пользовательских сессий в компании
	 *
	 * @param int    $company_id
	 * @param int    $created_at
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function changeCompanyCreatedAt(int $company_id, int $created_at, string $domino_id, string $private_key):void {

		$params = [
			"created_at" => $created_at,
		];

		// обновляем данные в компании
		[$status, $response] = self::_call("company.main.changeCompanyCreatedAt", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Получить количество активных приглашений
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return int
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getCountOfActiveInvites(int $company_id, string $domino_id, string $private_key):int {

		[$status, $response] = self::_call("company.invite.getCountOfActiveInvites", [], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["count"];
	}

	/**
	 * Проверяем, что имеем доступ к функционалу найма в компании
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return int
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function checkIsAllowedForHiring(int $user_id, int $company_id, string $domino_id, string $private_key):int {

		// обновляем данные в компании
		[$status, $response] = self::_call("company.member.checkIsAllowedForHiring", [], $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("status != ok in company.member.checkIsAllowedForHiring");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["is_allowed"];
	}

	/**
	 * Проверяем, что имеем доступ к функционалу истории компании
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return int
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function checkIsAllowedForHiringHistory(int $user_id, int $company_id, string $domino_id, string $private_key):int {

		// обновляем данные в компании
		[$status, $response] = self::_call("company.member.checkIsAllowedForHiringHistory", [], $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["is_allowed"];
	}

	/**
	 * Получить список кикнутых пользователей
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getKickedUserList(int $company_id, string $domino_id, string $private_key):array {

		// запрашиваем список пользователей
		[$status, $response] = self::_call("system.getKickedUserList", [], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["user_list"];
	}

	/**
	 * Выполняет запрос на удаленной вызов скрипта обновления.
	 *
	 * @param string $script_name
	 * @param array  $script_data
	 * @param int    $flag_mask
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 * @param array  $proxy_modules
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function execCompanyUpdateScript(string $script_name, array $script_data, int $flag_mask, int $company_id, string $domino_id, string $private_key, array $proxy_modules = []):array {

		$params["script_data"]   = $script_data;
		$params["script_name"]   = $script_name;
		$params["flag_mask"]     = $flag_mask;
		$params["proxy_modules"] = $proxy_modules;

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("system.execCompanyUpdateScript", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {
			throw new ReturnFatalException($response["message"]);
		}

		return [$response["script_log"], $response["error_log"]];
	}

	/**
	 * Возвращаем информацию о ссылке приглашении
	 *
	 * @param int    $user_id
	 * @param string $join_link_uniq
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_JoinLinkIsNotActive
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getInviteLinkInfo(int $user_id, string $join_link_uniq, int $company_id, string $domino_id, string $private_key):array {

		$params = [
			"invite_link_uniq" => $join_link_uniq,
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("hiring.invitelink.getInfo", $params, $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			} elseif ($response["error_code"] == 404) {
				throw new cs_JoinLinkIsNotActive();
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
		return [
			$response["entry_option"],
			$response["is_postmoderation"],
			$response["inviter_user_id"],
			$response["is_exit_status_in_progress"],
			$response["was_member"] ?? false,
			$response["candidate_role"],
		];
	}

	/**
	 * получаем информацию о ссылке-приглашении для автоматического вступления в команду
	 *
	 * @return array
	 * @throws CompanyNotServedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_JoinLinkIsNotActive
	 * @throws cs_SocketRequestIsFailed
	 */
	public static function getJoinLinkInfoForAutoJoin(int $user_id, int $root_user_id, Domain_User_Entity_Auth_Config_AutoJoinEnum $auto_join_team, int $company_id, string $domino_id, string $private_key):array {

		$params = [
			"user_id"         => $user_id,
			"creator_user_id" => $root_user_id,
			"type"            => $auto_join_team->value,
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("hiring.invitelink.getForAutoJoin", $params, $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			} elseif ($response["error_code"] == 404) {
				throw new cs_JoinLinkIsNotActive();
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
		return [
			$response["join_link_uniq"],
			$response["entry_option"],
			$response["is_postmoderation"],
			$response["inviter_user_id"],
			$response["is_exit_status_in_progress"],
			$response["was_member"] ?? false,
			$response["candidate_role"],
		];
	}

	/**
	 * Возвращаем информацию о ссылке приглашении для участника компании
	 *
	 * @param int    $user_id
	 * @param string $join_link_uniq
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_JoinLinkIsNotActive
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function getJoinLinkInfoForMember(int $user_id, string $join_link_uniq, int $company_id, string $domino_id, string $private_key):array {

		$params = [
			"join_link_uniq" => $join_link_uniq,
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("hiring.joinlink.getInfoForMember", $params, $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			} elseif ($response["error_code"] == 404) {
				throw new cs_JoinLinkIsNotActive();
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
		return [
			$response["entry_option"],
			$response["is_postmoderation"],
			$response["inviter_user_id"],
			$response["is_exit_status_in_progress"],
			$response["was_member"] ?? false,
			$response["role"],
		];
	}

	/**
	 * Возвращаем ID пользователя создателя ссылки приглашения
	 *
	 * @param string $join_link_uniq
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return int
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_JoinLinkNotFound
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getInviteLinkCreatorUserId(string $join_link_uniq, int $company_id, string $domino_id, string $private_key):int {

		$params = [
			"invite_link_uniq" => $join_link_uniq,
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("hiring.invitelink.getCreatorUserId", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			} elseif ($response["error_code"] == 404) {
				throw new cs_JoinLinkNotFound();
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
		return $response["creator_user_id"];
	}

	/**
	 * Удаление аккаунта пользователя
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function deleteUser(int $user_id, int $company_id, string $domino_id, string $private_key):void {

		try {
			[$status, $response] = self::_call("company.member.deleteUser", [], $user_id, $company_id, $domino_id, $private_key);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 503) {
				throw new cs_CompanyIsHibernate();
			}
			throw $e;
		}

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Принимаем инвайт
	 *
	 * @long Большой запрос
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_ExitTaskInProgress
	 * @throws cs_JoinLinkIsNotActive
	 * @throws \cs_SocketRequestIsFailed
	 * @throws cs_Text_IsTooLong
	 */
	public static function acceptJoinLink(
		int $user_id, string $invite_link_uniq, string $comment,
		int $company_id, string $domino_id, string $private_key, Struct_Db_PivotUser_User $user_info, bool $force_postmoderation, array $ldap_account_data
	):Struct_Dto_Socket_Company_AcceptJoinLinkResponse {

		$params = [
			"invite_link_uniq"             => $invite_link_uniq,
			"comment"                      => $comment,
			"full_name"                    => $user_info->full_name,
			"avatar_file_key"              => $user_info->avatar_file_map !== "" ? Type_Pack_File::doEncrypt($user_info->avatar_file_map) : "",
			"avatar_color_id"              => Type_User_Main::getAvatarColorId($user_info->extra),
			"is_force_exit_task_not_exist" => (int) (isBackendTest() && Type_System_Testing::isForceExitTaskNotExist()),
			"locale"                       => \BaseFrame\System\Locale::getLocale(),
			"avg_screen_time"              => Type_User_Main::getAvgScreenTime($user_info->extra),
			"total_action_count"           => Type_User_Main::getTotalActionCount($user_info->extra),
			"avg_message_answer_time"      => Type_User_Main::getAvgMessageAnswerTime($user_info->extra),
			"force_postmoderation"         => (int) $force_postmoderation,
			"ldap_account_data"            => $ldap_account_data,
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("hiring.invitelink.accept", $params, $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			throw match ($response["error_code"]) {
				404     => new cs_JoinLinkIsNotActive(),
				406     => new cs_Text_IsTooLong(),
				408     => new cs_ExitTaskInProgress(),
				default => new ParseFatalException("passed unknown error_code: " . $response["error_code"])
			};
		}

		return Struct_Dto_Socket_Company_AcceptJoinLinkResponse::makeFromResponse($response);
	}

	/**
	 * Отклоняем заявку на наём
	 *
	 * @param int                      $user_id
	 * @param int                      $entry_id
	 * @param int                      $company_id
	 * @param string                   $domino_id
	 * @param string                   $private_key
	 * @param Struct_Db_PivotUser_User $user_info
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_HiringRequestNotPostmoderation
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function revokeHiringRequest(int $user_id, int $entry_id, int $company_id, string $domino_id, string $private_key, Struct_User_Info $user_info):void {

		$params = [
			"entry_id"                  => $entry_id,
			"candidate_full_name"       => $user_info->full_name,
			"candidate_avatar_file_key" => isEmptyString($user_info->avatar_file_map) ? "" : Type_Pack_File::doEncrypt($user_info->avatar_file_map),
			"candidate_avatar_color_id" => $user_info->avatar_color_id,
		];

		// отправим запрос на удаление из списка
		try {
			[$status, $response] = self::_call("hiring.hiringrequest.revoke", $params, $user_id, $company_id, $domino_id, $private_key);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 503) {
				throw new cs_CompanyIsHibernate();
			}
			throw $e;
		}

		if ($status != "ok") {

			if ($response["error_code"] == 404) {
				throw new cs_HiringRequestNotPostmoderation();
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Выполняем задачу
	 *
	 * @param int    $task_id
	 * @param int    $type
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return bool
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public static function doTask(int $task_id, int $type, int $company_id, string $domino_id, string $private_key):bool {

		$params = [
			"task_id" => (int) $task_id,
			"type"    => (int) $type,
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("company.task.doTask", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {

				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}

		return $response["complete"];
	}

	/**
	 * Отправляем запрос на удаление компании
	 *
	 * @param int    $user_id
	 * @param int    $deleted_at
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_CompanyUserIsNotOwner
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function delete(int $user_id, int $deleted_at, int $company_id, string $domino_id, string $private_key):void {

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("company.main.delete", ["deleted_at" => $deleted_at], $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if ($response["error_code"] == 655) {
				throw new \cs_CompanyUserIsNotOwner();
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	// запускаем первый этап очистки компании
	public static function purgeCompanyStepOne(int $company_id, string $domino_id, string $private_key):void {

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("system.purgeCompanyStepOne", [], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	// запускаем второй этап очистки компании
	public static function purgeCompanyStepTwo(int $company_id, string $domino_id, string $private_key):void {

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("system.purgeCompanyStepTwo", [], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	// проверяем готовность компании для занятия
	public static function checkReadyCompany(int $company_id, string $domino_id, string $private_key):void {

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("system.checkReadyCompany", [], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"] . " : " . $response["message"]);
		}
	}

	/**
	 * Выполняем команду бота
	 *
	 * @param array  $payload
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function doCommand(array $payload, int $company_id, string $domino_id, string $private_key):void {

		$ar_post = [
			"payload" => $payload,
		];

		// отправим запрос
		[$status, $response] = self::_call("company.userbot.doCommand", $ar_post, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"] . " : " . $response["message"]);
		}
	}

	/**
	 * Ввести компанию в гибернацию
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 * @param bool   $is_force_hibernate
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyHasActivity
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function hibernate(int $company_id, string $domino_id, string $private_key, bool $is_force_hibernate):void {

		// отправим запрос на удаление из списка
		$params = [
			"is_force_hibernate" => $is_force_hibernate ? 1 : 0,
		];
		[$status, $response] = self::_call("system.hibernate", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {

				throw new ReturnFatalException("wrong response");
			}

			if ($response["error_code"] == 2412001) {
				throw new Gateway_Socket_Exception_CompanyHasActivity("компания активная");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Разбудить компанию
	 *
	 * @param int    $company_id
	 * @param int    $hibernation_immunity_till
	 * @param int    $last_wakeup_at
	 * @param string $domino_id
	 * @param string $private_key
	 * @param array  $user_is_deleted_list
	 * @param array  $user_info_deleted_list
	 * @param array  $remind_bot
	 * @param array  $support_bot
	 * @param string $respect_conversation_avatar_file_key
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @long
	 */
	public static function awake(int    $company_id, int $hibernation_immunity_till, int $last_wakeup_at, string $domino_id, string $private_key,
					     array  $user_is_deleted_list, array $user_info_deleted_list, array $remind_bot = [], array $support_bot = [],
					     string $respect_conversation_avatar_file_key = "", array $user_info_update_list = []):void {

		$params = [
			"hibernation_immunity_till" => $hibernation_immunity_till,
			"last_wakeup_at"            => $last_wakeup_at,
			"user_id_is_deleted_list"   => $user_is_deleted_list,
			"user_info_is_deleted_list" => $user_info_deleted_list,
			"user_info_update_list"     => $user_info_update_list,
		];

		if (count($remind_bot) > 0) {
			$params["remind_bot_info"] = $remind_bot;
		}

		if (count($support_bot) > 0) {
			$params["support_bot_info"] = $support_bot;
		}

		if (mb_strlen($respect_conversation_avatar_file_key) > 0) {
			$params["respect_conversation_avatar_file_key"] = $respect_conversation_avatar_file_key;
		}

		// отправим запрос на удаление из списка
		try {
			[$status, $response] = self::_call("system.awake", $params, 0, $company_id, $domino_id, $private_key);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 503) {
				throw new cs_CompanyIsHibernate();
			}
			throw $e;
		}

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}
			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Проверяет, обслуживается ли компания.
	 * Вернет 0, если для компании конфиг не был найден.
	 */
	public static function getCompanyConfigStatus(Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompanyService_DominoRegistry $domino):int {

		$params = [
			"check_company_id" => $company->company_id,
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call(
			"company.main.getCompanyConfigStatus",
			$params,
			0,
			0,
			$company->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($company->extra)
		);

		if ($status !== "ok") {
			throw new ReturnFatalException("socket response is not ok, code: " . ($response["error_code"] ?? "unknown"));
		}

		return (int) $response["status"];
	}

	/**
	 * Уведомить, что скоро будет релокация
	 *
	 * @param int    $company_id
	 * @param int    $will_start_at
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function onTechnicalWorksPlanned(int $company_id, int $will_start_at, string $domino_id, string $private_key):void {

		$params = [
			"will_start_at" => $will_start_at,
			"company_id"    => $company_id,
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("system.relocateNotice", $params, 0, 0, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Уведомить, что релокация началась
	 *
	 * @param int    $company_id
	 * @param int    $will_be_available_at
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function onTechnicalWorksStarted(int $company_id, int $will_be_available_at, string $domino_id, string $private_key):void {

		$params = [
			"will_be_available_at" => $will_be_available_at,
			"company_id"           => $company_id,
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("system.relocateStart", $params, 0, 0, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Уведомить, что релокация завершена
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function onTechnicalWorksDone(int $company_id, string $domino_id, string $private_key):void {

		$params = [
			"company_id" => $company_id,
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("system.relocateEnd", $params, 0, 0, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Получить список активных пользователей
	 *
	 * @param array  $roles
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getUserRoleList(array $roles, int $company_id, string $domino_id, string $private_key):array {

		$params = [
			"roles" => $roles,
		];
		// запрашиваем список пользователей
		[$status, $response] = self::_call("company.member.getUserRoleList", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["user_role"];
	}

	/**
	 * Отправляем сообщение с файлом
	 *
	 * @param int    $sender_id
	 * @param string $file_key
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function sendMessageWithFile(int $sender_id, string $file_key, int $company_id, string $domino_id, string $private_key):void {

		// обновляем данные в компании
		$ar_post = [
			"sender_id" => $sender_id,
			"file_key"  => $file_key,
		];
		[$status, $response] = self::_call("system.sendMessageWithFile", $ar_post, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Обновляет данные премиум-статуса для пользователей.
	 *
	 * @param Struct_Premium_CompanyData[]   $premium_company_data_list
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function updatePremiumStatuses(array $premium_company_data_list, Struct_Db_PivotCompany_Company $company):void {

		if (count($premium_company_data_list) === 0) {
			return;
		}

		$ar_post = [
			"premium_company_data_list" => array_map(static fn(Struct_Premium_CompanyData $el) => (array) $el, $premium_company_data_list),
		];

		[$status] = self::_call(
			"premium.updatePremiumStatuses",
			$ar_post,
			0,
			$company->company_id,
			$company->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($company->extra)
		);

		if ($status !== "ok") {
			throw new ReturnFatalException("can not update premium status in company $company->company_id");
		}
	}

	/**
	 * Добавляем системного бота в компанию
	 *
	 * @param array  $bot_info
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function addSystemBot(array $bot_info, int $company_id, string $domino_id, string $private_key):void {

		$ar_post = [
			"bot_info" => $bot_info,
		];
		[$status, $response] = self::_call("system.addSystemBot", $ar_post, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Добавляем оператора в компанию
	 *
	 * @param array  $operator_info
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function addOperator(array $operator_info, int $company_id, string $domino_id, string $private_key):array {

		$ar_post = [
			"operator_info" => $operator_info,
		];
		[$status, $response] = self::_call("system.addOperator", $ar_post, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return [$response["role"], $response["permissions"]];
	}

	/**
	 * обновляем данные бота Напоминаний в компании
	 *
	 * @param array  $bot_info
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function updateSystemBot(array $bot_info, int $company_id, string $domino_id, string $private_key):void {

		$ar_post = [
			"bot_info" => $bot_info,
		];

		[$status, $response] = self::_call("system.updateSystemBot", $ar_post, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Проверяем, что являемся руководителем компании
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return bool
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	public static function checkCanEditSpaceSettings(int $user_id, int $company_id, string $domino_id, string $private_key):bool {

		// обновляем данные в компании
		[$status, $response] = self::_call("company.member.checkCanEditSpaceSettings", [], $user_id, $company_id, $domino_id, $private_key);

		if ($status !== "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["can_edit_space_settings"] == 1;
	}

	/**
	 * Проверяем, что можем привязать компанию в партнерке
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return bool
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function checkCanAttachSpace(int $user_id, int $company_id, string $domino_id, string $private_key):bool {

		// обновляем данные в компании
		[$status, $response] = self::_call("company.member.checkCanAttachSpace", [], $user_id, $company_id, $domino_id, $private_key);

		if ($status !== "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["can_attach_space"] == 1;
	}

	/**
	 * Получить информацию для покупки продукта для пространства
	 *
	 * @throws ParseFatalException
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getInfoForPurchase(int $user_id, Struct_Db_PivotCompany_Company $space):array {

		// обновляем данные в компании
		[$status, $response] = self::_call("space.getInfoForPurchase", [], $user_id, $space->company_id, $space->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($space->extra));

		if ($status !== "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return [(bool) $response["is_administrator"], (int) $response["space_created_at"]];
	}

	/**
	 * Был создан счет для оплаты
	 *
	 * @param int    $created_by_user_id
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function onInvoiceCreated(int $created_by_user_id, int $company_id, string $domino_id, string $private_key):void {

		$params = [
			"created_by_user_id" => $created_by_user_id,
		];
		[$status, $response] = self::_call("premium.onInvoiceCreated", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Был оплачен счет
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function onInvoicePayed(int $company_id, string $domino_id, string $private_key):void {

		[$status, $response] = self::_call("premium.onInvoicePayed", [], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Счет был отменен
	 *
	 * @param int    $company_id
	 * @param int    $invoice_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function onInvoiceCanceled(int $company_id, int $invoice_id, string $domino_id, string $private_key):void {

		$params = [
			"invoice_id" => $invoice_id,
		];
		[$status, $response] = self::_call("premium.onInvoiceCanceled", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Проверяем права пользователя
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return bool
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function checkIsOwner(int $user_id, int $company_id, string $domino_id, string $private_key):bool {

		// обновляем данные в компании
		[$status, $response] = self::_call("company.member.checkIsOwner", [], $user_id, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["is_owner"];
	}

	/**
	 * Шлем лог статуса пространства
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getCompanyAnalytic(int $company_id, string $domino_id, string $private_key):array {

		[$status, $response] = self::_call("system.getCompanyAnalytic", [], 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return [$response["member_count"], $response["last_active_at"], $response["user_id_members"], $response["user_id_admin_list"]];
	}

	/**
	 * Обновить права
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function updatePermissions(Struct_Db_PivotCompany_Company $company):void {

		// отправим запрос на удаление из списка
		try {

			[$status, $response] = self::_call("space.member.updatePermissions",
				[],
				0, $company->company_id,
				$company->domino_id,
				Domain_Company_Entity_Company::getPrivateKey($company->extra));
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 503) {
				throw new cs_CompanyIsHibernate();
			}
			throw $e;
		}

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Выдаем права пользователю
	 */
	public static function setPermissions(Struct_Db_PivotCompany_Company $company, int $member_user_id, array $permissions):void {

		$params = [
			"permissions" => toJson($permissions),
		];

		// отправим запрос на удаление из списка
		try {

			[$status, $response] = self::_call("space.member.setPermissions",
				$params,
				$member_user_id,
				$company->company_id,
				$company->domino_id,
				Domain_Company_Entity_Company::getPrivateKey($company->extra));
		} catch (cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 503) {
				throw new cs_CompanyIsHibernate();
			}
			throw $e;
		}

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Пытается запустить индексацию пространства
	 */
	public static function tryReindex(Struct_Db_PivotCompany_Company $company):void {

		try {

			[$status] = self::_call("space.search.tryReindex",
				[], 0, $company->company_id, $company->domino_id,
				Domain_Company_Entity_Company::getPrivateKey($company->extra)
			);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 503) {
				throw new cs_CompanyIsHibernate();
			}

			throw $e;
		}

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Проверяем, разблокировано ли пространство
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return bool
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function checkIsUnblocked(int $company_id, string $domino_id, string $private_key):bool {

		$params = [];

		// обновляем данные в компании
		[$status, $response] = self::_call("space.tariff.checkIsUnblocked", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return (bool) $response["is_unblocked"];
	}

	/**
	 * Отправляем сообщение в чат поддержки от лица бота поддержки
	 *
	 * @param int    $receiver_user_id
	 * @param string $text
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return void
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function addMessageFromSupportBot(int $receiver_user_id, string $text, int $company_id, string $domino_id, string $private_key):void {

		$ar_post = [
			"receiver_user_id" => $receiver_user_id,
			"text"             => $text,
		];

		[$status, $response] = self::_call("intercom.addMessageFromSupportBot", $ar_post, 0, $company_id, $domino_id, $private_key);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Создаем ссылку приглашение в компанию от лица пользователя
	 *
	 * @param int    $creator_user_id
	 * @param int    $lives_day_count
	 * @param int    $can_use_count
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Domain_Link_Exception_DontHavePermissions
	 * @throws Domain_Link_Exception_TooManyActiveInvites
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function createJoinLink(int $creator_user_id, int $lives_day_count, int $can_use_count, int $company_id, string $domino_id, string $private_key):array {

		$ar_post = [
			"creator_user_id" => $creator_user_id,
			"lives_day_count" => $lives_day_count,
			"can_use_count"   => $can_use_count,
		];

		try {
			[$status, $response] = self::_call("hiring.joinlink.add", $ar_post, $creator_user_id, $company_id, $domino_id, $private_key);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 503) {
				throw new cs_CompanyIsHibernate();
			}
			throw $e;
		}

		if ($status != "ok") {

			if ($response["error_code"] == 2208001) {
				throw new Domain_Link_Exception_DontHavePermissions("dont have permissions");
			}

			if ($response["error_code"] == 2208002) {
				throw new Domain_Link_Exception_TooManyActiveInvites("dont have permissions");
			}

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}

		return $response["join_link"];
	}

	/**
	 * Получить информацию об участнике пространства
	 *
	 * @param array  $roles
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_SocketRequestIsFailed
	 * @throws parseException
	 * @throws returnException
	 */
	public static function getUserInfo(int $user_id, int $company_id, string $domino_id, string $private_key):array {

		$params = [
			"user_id" => $user_id,
		];
		// запрашиваем список пользователей
		[$status, $response] = self::_call("company.member.getUserInfo", $params, 0, $company_id, $domino_id, $private_key);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			if ($response["error_code"] == 2305001) {
				throw new cs_UserNotFound("not found user in company");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return [$response["user"]["description"], $response["user"]["badge"], $response["user"]["badge_color_id"], $response["user"]["role"], $response["user"]["comment"]];
	}

	/**
	 * устанавливаем права участнику пространства
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_SocketRequestIsFailed
	 * @throws cs_UserNotFound
	 */
	public static function setMemberPermissions(Struct_Db_PivotCompany_Company $space, int $user_id, array $permissions):void {

		$params = [
			"permissions" => toJson($permissions),
		];
		[$status, $response] = self::_call(
			"company.member.setPermissions", $params, $user_id, $space->company_id, $space->domino_id, Domain_Company_Entity_Company::getPrivateKey($space->extra)
		);

		if ($status !== "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			if ($response["error_code"] == 2209006) {
				throw new cs_UserNotFound("member not found");
			}

			if ($response["error_code"] == 2209007) {
				throw new cs_UserNotFound("member deleted account");
			}

			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Проверяет возможность создавать медиа-конференции указанным пользователем.
	 *
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException|cs_CompanyIsHibernate
	 * @throws cs_SocketRequestIsFailed
	 */
	#[ArrayShape([0 => 'bool', 1 => "int"])]
	public static function isMediaConferenceCreatingAllowed(Struct_Db_PivotCompany_Company $space, int $user_id):array {

		$params = ["user_id" => $user_id];

		[$status, $response] = self::_call(
			"space.member.isMediaConferenceCreatingAllowed",
			$params, $user_id, $space->company_id,
			$space->domino_id,
			Domain_Company_Entity_Company::getPrivateKey($space->extra)
		);

		// если статус окей и есть разрешение, то просто говорим, что все окей
		if ($status === "ok" && $response["is_allowed"] === 1) {
			return [true, 0];
		}

		// если статус окей, разрешения нет и есть код ошибки,
		// то сообщаем, что создавать конференцию нельзя и передает причину
		if ($status === "ok" && $response["is_allowed"] === 0 && isset($response["reason"])) {
			return [false, $response["reason"]];
		}

		// что-то сильно пошло не по плану, метод всегда должен возвращать ок
		throw new ReturnFatalException("unexpected response");
	}

	/**
	 * Инкрементим статистику участия пользователя в конференции
	 */
	public static function incConferenceMembershipRating(Struct_Db_PivotCompany_Company $space, int $user_id):void {

		$params = ["user_id" => $user_id];

		try {

			[$status, $response] = self::_call(
				"space.member.incConferenceMembershipRating",
				$params,
				$user_id,
				$space->company_id,
				$space->domino_id,
				Domain_Company_Entity_Company::getPrivateKey($space->extra)
			);
		} catch (cs_CompanyIsHibernate) {

			Type_System_Admin::log("inc_conference_membership_rating_error", ["Компания {$space->company_id} спит", $params]);
			return;
		}

		if ($status !== "ok") {
			throw new ReturnFatalException("unexpected response");
		}
	}

	/**
	 * Получаем id приложений созданных пользователем из каталога
	 *
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws CompanyNotServedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_SocketRequestIsFailed
	 */
	public static function getCreatedSmartAppListByUser(int $user_id, int $company_id, string $domino_id, string $private_key):array {

		[$status, $response] = self::_call("smartapp.getCreatedSmartAppListByUser", [], $user_id, $company_id, $domino_id, $private_key);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["created_smart_app_list"];
	}

	/**
	 * Получаем статистику по количеству созданных приложений из каталога
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws CompanyNotServedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_SocketRequestIsFailed
	 */
	public static function getCreatedSmartAppsStatFromSuggestionList(int $company_id, string $domino_id, string $private_key):array {

		[$status, $response] = self::_call("smartapp.getCreatedSmartAppsStatFromSuggestionList", [], 0, $company_id, $domino_id, $private_key);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["suggestion_stat_list"];
	}

	/**
	 * Получаем созданные приложения НЕ из каталога
	 *
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws CompanyNotServedException
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws cs_CompanyIsHibernate
	 * @throws cs_SocketRequestIsFailed
	 */
	public static function getCreatedPersonalSmartApps(int $company_id, string $domino_id, string $private_key):array {

		[$status, $response] = self::_call("smartapp.getCreatedPersonalSmartApps", [], 0, $company_id, $domino_id, $private_key);
		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("wrong response");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["smart_app_list"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем вызов в php_company
	 *
	 * @param string $method
	 * @param array  $params
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $domino_id
	 * @param string $private_key
	 *
	 * @return array
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyIsHibernate
	 * @throws \cs_SocketRequestIsFailed
	 */
	protected static function _call(string $method, array $params, int $user_id, int $company_id, string $domino_id, string $private_key):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url = self::_getEntrypoint($domino_id);

		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_SSL, $private_key, $json_params);
		try {
			return Type_Socket_Main::doCall($url, $method, $json_params, $signature, $company_id, $user_id);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 404) {
				throw new Gateway_Socket_Exception_CompanyIsNotServed("company is not served");
			}
			if ($e->getHttpStatusCode() == 503) {
				throw new cs_CompanyIsHibernate("company is hibernated");
			}
			throw $e;
		}
	}

	/**
	 * получаем url
	 *
	 * @param string $domino
	 *
	 * @return string
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	protected static function _getEntryPoint(string $domino):string {

		$domino_entrypoint_config = getConfig("DOMINO_ENTRYPOINT");
		$socket_module_config     = getConfig("SOCKET_MODULE");

		if (!isset($domino_entrypoint_config[$domino])) {
			throw new \BaseFrame\Exception\Request\CompanyNotServedException("company not served");
		}
		return $domino_entrypoint_config[$domino]["private_entrypoint"] . $socket_module_config["company"]["socket_path"];
	}
}
