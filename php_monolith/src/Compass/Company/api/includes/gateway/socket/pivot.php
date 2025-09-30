<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для работы с модулей pivot
 */
class Gateway_Socket_Pivot {

	/**
	 * Метод для проверки пользовательского токена и получения данных для аутентификации.
	 *
	 * @param int    $user_id
	 * @param string $user_company_session_token
	 *
	 * @return Struct_User_AuthenticationData|false
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getCompanyAuthenticationDataByToken(int $user_id, string $user_company_session_token):Struct_User_AuthenticationData|false {

		$params = [
			"user_company_session_token" => $user_company_session_token,
		];

		[$status, $response] = self::_call("company.auth.checkUserSessionToken", $params, $user_id);

		if ($status !== "ok") {

			if ($response["error_code"] === 423 || $response["error_code"] === 1) {
				return false;
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return new Struct_User_AuthenticationData(
			(bool) $response["need_block_if_premium_inactive"],
			(int) $response["premium_active_till"]
		);
	}

	/**
	 * метод для проверки пользовательского токена
	 *
	 * @param int $user_id
	 *
	 * @return Struct_User_AuthenticationData|false
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getCompanyAuthenticationData(int $user_id):Struct_User_AuthenticationData|false {

		[$status, $response] = self::_call("company.auth.getCompanyAuthenticationData", [], $user_id);

		if ($status !== "ok") {

			if ($response["error_code"] === 423 || $response["error_code"] === 1) {
				return false;
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return new Struct_User_AuthenticationData(
			(bool) $response["need_block_if_premium_inactive"],
			(int) $response["premium_active_till"]
		);
	}

	/**
	 * метод для обновления статус_алиаса ссылки-инвайта
	 *
	 * @param int    $user_id
	 * @param string $join_link_uniq
	 * @param int    $status_alias
	 *
	 * @return void
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function updateJoinLinkStatus(int $user_id, string $join_link_uniq, int $status_alias):void {

		$params = [
			"invite_link_uniq" => $join_link_uniq,
			"status_alias"     => $status_alias,
		];
		[$status] = self::_call("company.user.updateInviteLinkStatus", $params, $user_id);
		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Установить токен уведомления для компании
	 *
	 * @param int    $user_id
	 * @param string $device_id
	 * @param string $token
	 * @param bool   $is_add
	 *
	 * @return bool
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setUserCompanyToken(int $user_id, string $device_id, string $token, bool $is_add = true):bool {

		$params = [
			"device_id"          => (string) $device_id,
			"user_company_token" => (string) $token,
			"is_add"             => (int) $is_add,
		];
		[$status, $response] = self::_call("company.notifications.setUserCompanyToken", $params, $user_id);

		if ($status != "ok") {

			if ($response["error_code"] == 423 || $response["error_code"] == 1) {
				return false;
			}
			throw new ParseFatalException("passed unknown error_code");
		}
		return true;
	}

	/**
	 * Отправляем запрос на изменение имени компании
	 *
	 * @param int    $user_id
	 * @param string $name
	 *
	 * @return bool
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setName(int $user_id, string $name):bool {

		$params = [
			"name" => $name,
		];
		[$status,] = self::_call("company.profile.setName", $params, $user_id);
		if ($status != "ok") {

			throw new ParseFatalException("passed unknown error_code");
		}
		return true;
	}

	/**
	 * Отправляем запрос на изменение цвета аватарки компании
	 *
	 * @param int $user_id
	 * @param int $avatar_color_id
	 *
	 * @return bool
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setAvatar(int $user_id, int $avatar_color_id):bool {

		$params = [
			"avatar_color_id" => $avatar_color_id,
		];
		[$status, $response] = self::_call("company.profile.setAvatar", $params, $user_id);
		if ($status != "ok") {

			if ($response["error_code"] == 423 || $response["error_code"] == 1) {
				return false;
			}
			throw new ParseFatalException("passed unknown error_code");
		}
		return true;
	}

	/**
	 * Отправляем запрос на изменение основных данных профиля компании
	 *
	 * @param int          $user_id
	 * @param string|false $name
	 * @param int|false    $avatar_color_id
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setCompanyBaseInfo(int $user_id, string|false $name, int|false $avatar_color_id):array {

		$params = [];

		if ($name !== false) {
			$params["name"] = $name;
		}

		if ($avatar_color_id !== false) {
			$params["avatar_color_id"] = $avatar_color_id;
		}

		[$status, $response] = self::_call("company.profile.setBaseInfo", $params, $user_id);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return [$response["name"], $response["avatar_color_id"]];
	}

	/**
	 * Отправляем запрос на увольнения из компании
	 *
	 * @param int  $user_id
	 * @param bool $need_add_user_lobby
	 *
	 * @return void
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function kickMember(int $user_id, int $role, bool $need_add_user_lobby, string $reason):void {

		$params = [
			"need_add_user_lobby" => $need_add_user_lobby,
			"reason"              => $reason,
			"role"                => $role,
		];
		[$status, $response] = self::_call("company.member.kick", $params, $user_id);

		if ($status != "ok") {

			if ($response["error_code"] == 423 || $response["error_code"] == 1) {

				return;
			}
			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Отправляем запрос на актвиацию пользователя в компании
	 *
	 * @return void
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function doConfirmHiringRequest(int $candidate_user_id, int $hired_by_user_id, int $approved_by_user_id, int $user_space_role, int $user_space_permissions, string $user_company_token):void {

		$params = [
			"user_company_token"     => $user_company_token,
			"inviter_user_id"        => $hired_by_user_id,
			"approved_by_user_id"    => $approved_by_user_id,
			"user_space_role"        => $user_space_role,
			"user_space_permissions" => $user_space_permissions,
		];
		[$status] = self::_call("company.member.doConfirmHiringRequest", $params, $candidate_user_id);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Отправляем запрос на обновление флага is_has_pin
	 *
	 * @param int $user_id
	 * @param int $company_id
	 * @param int $is_has_pin
	 *
	 * @return bool
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function setIsHasPin(int $user_id, int $company_id, int $is_has_pin):bool {

		return true;
	}

	/**
	 * Сгенерировать 2fa токен
	 *
	 * @param int $user_id
	 * @param int $action_type
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException|blockException
	 */
	public static function doGenerateTwoFaToken(int $user_id, int $action_type):array {

		$params = [
			"action_type" => $action_type,
			"company_id"  => COMPANY_ID,
		];

		[$status, $response] = self::_call("pivot.security.doGenerateTwoFaToken", $params, $user_id);
		if ($status != "ok") {

			if ($response["error_code"] == HTTP_CODE_423) {
				throw new BlockException("User with user_id [{$user_id}] blocked for [{$action_type}]");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return [
			"two_fa_key"  => $response["two_fa_key"],
			"action_type" => $response["action_type"],
			"expire_at"   => $response["expire_at"],
		];
	}

	/**
	 * Валидировать 2fa токен
	 *
	 * @param int    $user_id
	 * @param int    $action_type
	 * @param string $two_fa_key
	 *
	 * @return bool
	 * @throws cs_ActionForCompanyBlocked
	 * @throws cs_AnswerCommand
	 * @throws cs_TwoFaIsInvalid
	 * @throws cs_TwoFaIsNotActive
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function tryValidateTwoFaToken(int $user_id, int $action_type, string $two_fa_key):bool {

		$params = [
			"two_fa_key"  => $two_fa_key,
			"action_type" => $action_type,
			"company_id"  => COMPANY_ID,
		];

		[$status, $response] = self::_call("pivot.security.tryValidateTwoFaToken", $params, $user_id);
		if ($status != "ok") {

			$error_code = $response["error_code"];
			if ($error_code == 2301 || $error_code == 2300) {
				throw new cs_AnswerCommand("need_confirm_2fa", []);
			} elseif ($error_code == 2302) {
				throw new cs_TwoFaIsInvalid();
			} elseif ($error_code == 2303) {
				throw new cs_TwoFaIsNotActive();
			} elseif ($error_code == 423) {
				throw new cs_ActionForCompanyBlocked();
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return true;
	}

	/**
	 * Пометить токен как невалидный
	 *
	 * @param int    $user_id
	 * @param string $two_fa_key
	 *
	 * @return bool
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function setTwoFaTokenAsInactive(int $user_id, string $two_fa_key):bool {

		$params = [
			"two_fa_key" => $two_fa_key,
		];

		[$status,] = self::_call("pivot.security.setTwoFaTokenAsInactive", $params, $user_id);
		if ($status != "ok") {

			throw new ParseFatalException("passed unknown error_code");
		}

		return true;
	}

	/**
	 * Получает список данных для пользователей с pivot сервера.
	 *
	 * @throws ReturnFatalException
	 * @throws ParseFatalException
	 */
	public static function getUserInfo(int $user_id):array|false {

		// делаем запрос
		[$status, $response] = self::_call("company.user.getUserInfo", [], $user_id);

		if ($status != "ok") {

			$error_code = $response["error_code"];
			if ($error_code == 404) {
				return false;
			}

			throw new ReturnFatalException("passed unknown error_code");
		}

		return [
			new Struct_User_Info(
				$response["user_id"],
				$response["full_name"],
				$response["avatar_file_key"],
				$response["avatar_color_id"],
			),
			$response["avg_screen_time"],
			$response["total_action_count"],
			$response["avg_message_answer_time"],
		];
	}

	/**
	 * Получает список данных для пользователей с pivot сервера.
	 *
	 * @param array $user_id_list
	 *
	 * @return Struct_User_Info[]
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getUserInfoList(array $user_id_list):array {

		// делаем запрос
		[$status, $response] = self::_call("company.user.getUserInfoList", ["user_id_list" => $user_id_list], 0);

		if ($status != "ok") {

			$error_code = $response["error_code"];
			if ($error_code == 404) {
				return [];
			}

			throw new ReturnFatalException("passed unknown error_code");
		}

		$user_info_list = [];
		foreach ($response["user_info_list"] as $user_info) {

			$user_info_list[$user_info["user_id"]] = new Struct_User_Info(
				$user_info["user_id"],
				$user_info["full_name"],
				$user_info["avatar_file_key"],
				$user_info["avatar_color_id"],
			);
		}
		return $user_info_list;
	}

	/**
	 * Получает список данных для пользователей с pivot сервера. ТОЛЬКО ДЛЯ СКРИПТА
	 *
	 * @param int $user_id
	 *
	 * @return array
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getBeforeRedesignUserInfo(int $user_id):array {

		// делаем запрос ТОЛЬКО ДЛЯ СКРИПТА
		[$status, $response] = self::_call("company.user.getBeforeRedesignUserInfo", [], $user_id);

		if ($status != "ok") {
			throw new ReturnFatalException("passed unknown error_code");
		}

		return [
			$response["full_name"],
			$response["mbti_type"],
			$response["short_description"],
			$response["avatar_file_key"],
			$response["badge_content"],
			$response["badge_color_id"],
		];
	}

	/**
	 * Удаляем все пуш токены
	 *
	 * @param int    $user_id
	 * @param array  $device_list
	 * @param string $token
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function clearTokenList(int $user_id, array $device_list, string $token):array {

		$post_data = [
			"user_id"     => $user_id,
			"device_list" => $device_list,
			"token"       => $token,
		];

		// делаем запрос
		[$status, $response] = self::_call("notifications.clearTokenList", $post_data, 0);

		if ($status != "ok") {
			throw new ReturnFatalException("passed unknown error_code");
		}

		return $response;
	}

	/**
	 * Получаем id создателя
	 *
	 * @return int
	 * @throws ReturnFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getCompanyCreatorUserId():int {

		// делаем запрос
		[$status, $response] = self::_call("company.member.getCreatorUserId", [], 0);

		if ($status != "ok") {
			throw new ReturnFatalException("passed unknown error_code");
		}

		return $response["creator_user_id"];
	}

	/**
	 * Отзываем инвайт
	 *
	 * @param int $invited_user_id
	 *
	 * @return void
	 * @throws cs_HiringRequestAlreadyConfirmed
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function doRejectHiringRequest(int $inviter_user_id, int $invited_user_id):void {

		// делаем запрос
		[$status, $response] = self::_call("company.member.doRejectHiringRequest", [
			"inviter_user_id" => $inviter_user_id,
		], $invited_user_id);

		if ($status != "ok") {

			$error_code = $response["error_code"];
			if ($error_code == 750 || $error_code == 757) {
				return;
			} elseif ($error_code == 755) {
				throw new cs_HiringRequestAlreadyConfirmed();
			}
			throw new ReturnFatalException("passed unknown error_code");
		}
	}

	/**
	 * Проверяем, существует ли компания
	 *
	 * @return bool
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function checkCompanyExists():bool {

		try {

			// делаем запрос
			self::_call("pivot.company.ping", [], 0);
		} catch (ReturnFatalException $e) {

			if ($e->getCode() === 401) {

				return false;
			}
			throw $e;
		}

		return true;
	}

	/**
	 * Отправляем задачу в крон
	 *
	 * @param int    $task_id
	 * @param string $type
	 * @param int    $user_id
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function addScheduledCompanyTask(int $task_id, string $type, int $user_id):void {

		$params = [
			"task_id"    => $task_id,
			"type"       => $type,
			"company_id" => COMPANY_ID,
		];

		// делаем запрос
		[$status, $response] = self::_call("company.task.addScheduledCompanyTask", $params, $user_id);

		if ($status != "ok") {

			throw new ReturnFatalException($response);
		}
	}

	/**
	 * создаем ссылку-инвайт
	 *
	 * @param int $user_id
	 * @param int $status
	 *
	 * @return string
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function createJoinLink(int $user_id, int $status):string {

		$ar_post = ["status_alias" => $status];

		// делаем запрос
		[$status, $response] = self::_call("company.user.createInviteLink", $ar_post, $user_id);

		if ($status != "ok") {
			throw new ReturnFatalException("passed unknown error_code");
		}

		return $response["invite_link_uniq"];
	}

	/**
	 * метод для начала гибернации
	 *
	 * @return bool
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function startHibernate():bool {

		[$status] = self::_call("company.system.startHibernate", [], 0);
		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}
		return true;
	}

	/**
	 * создаём пользовательского бота
	 *
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 * @throws \parseException
	 * @throws \returnException
	 * @long - switch..case для кодов ошибок
	 */
	public static function createUserbot(string    $userbot_name, int $avatar_color_id, string|false $avatar_file_key,
							 int|false $is_react_command, string|false $webhook,
							 int|false $is_smart_app, string|false $smart_app_name, string|false $smart_app_url,
							 int|false $is_smart_app_sip, int|false $is_smart_app_mail,
							 int|false $smart_app_default_width, int|false $smart_app_default_height,
							 int       $role, int $permissions):array {

		$ar_post = [
			"userbot_name" => $userbot_name,
			"avatar_id"    => $avatar_color_id,
			"role"         => $role,
			"permissions"  => $permissions,
		];

		if ($avatar_file_key !== false) {
			$ar_post["avatar_file_key"] = $avatar_file_key;
		}

		if ($is_react_command !== false) {
			$ar_post["is_react_command"] = (int) $is_react_command;
		}

		if ($webhook !== false) {
			$ar_post["webhook"] = $webhook;
		}

		if ($is_smart_app !== false) {
			$ar_post["is_smart_app"] = (int) $is_smart_app;
		}

		if ($smart_app_name !== false) {
			$ar_post["smart_app_name"] = $smart_app_name;
		}

		if ($smart_app_url !== false) {
			$ar_post["smart_app_url"] = $smart_app_url;
		}

		if ($is_smart_app_sip !== false) {
			$ar_post["is_smart_app_sip"] = (int) $is_smart_app_sip;
		}

		if ($is_smart_app_mail !== false) {
			$ar_post["is_smart_app_mail"] = (int) $is_smart_app_mail;
		}

		if ($smart_app_default_width !== false) {
			$ar_post["smart_app_default_width"] = (int) $smart_app_default_width;
		}

		if ($smart_app_default_height !== false) {
			$ar_post["smart_app_default_height"] = (int) $smart_app_default_height;
		}

		[$status, $response] = self::_call("company.userbot.create", $ar_post, 0);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("not exist error_code");
			}

			switch ($response["error_code"]) {

				case 1434001:
					throw new Domain_Userbot_Exception_IncorrectParam("not create userbot cause incorrect param");

				case 1434002:
					throw new ReturnFatalException("something strange happened on try create userbot");
			}
			throw new ReturnFatalException("passed unknown error_code");
		}

		return [
			$response["userbot_id"],
			$response["token"],
			$response["secret_key"],
			$response["user_id"],
			$response["avatar_file_key"],
			$response["npc_type"],
		];
	}

	/**
	 * редактируем бота
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function editUserbot(string    $userbot_id, string $token, string|false $userbot_name,
						     int|false $avatar_color_id, string|false $avatar_file_key,
						     int|false $is_react_command, string|false $webhook,
						     int|false $is_smart_app, string|false $smart_app_name, string|false $smart_app_url,
						     int|false $is_smart_app_sip, int|false $is_smart_app_mail,
						     int|false $smart_app_default_width, int|false $smart_app_default_height):void {

		$ar_post = [
			"userbot_id"         => $userbot_id,
			"token"              => $token,
			"client_launch_uuid" => getClientLaunchUUID(),
		];

		if ($userbot_name !== false) {
			$ar_post["userbot_name"] = $userbot_name;
		}

		if ($webhook !== false) {

			$ar_post["webhook"]          = $webhook;
		}

		if ($is_react_command !== false) {

			$ar_post["is_react_command"] = (int) $is_react_command;
		}

		if ($smart_app_name !== false) {

			$ar_post["smart_app_name"] = $smart_app_name;
			$ar_post["is_smart_app"]   = (int) $is_smart_app;
		}

		if ($smart_app_url !== false) {

			$ar_post["smart_app_url"] = $smart_app_url;
			$ar_post["is_smart_app"]  = (int) $is_smart_app;
		}

		if ($avatar_color_id !== false) {
			$ar_post["avatar_color_id"] = (int) $avatar_color_id;
		}

		if ($avatar_file_key !== false) {
			$ar_post["avatar_file_key"] = (string) $avatar_file_key;
		}

		if ($is_smart_app_sip !== false) {
			$ar_post["is_smart_app_sip"] = (int) $is_smart_app_sip;
		}

		if ($is_smart_app_mail !== false) {
			$ar_post["is_smart_app_mail"] = (int) $is_smart_app_mail;
		}

		if ($smart_app_default_width !== false) {
			$ar_post["smart_app_default_width"] = (int) $smart_app_default_width;
		}

		if ($smart_app_default_height !== false) {
			$ar_post["smart_app_default_height"] = (int) $smart_app_default_height;
		}

		// делаем запрос
		[$status] = self::_call("company.userbot.edit", $ar_post, 0);

		if ($status != "ok") {

			if (isset($response["error_code"]) && $response["error_code"] == 1434003) {
				throw new Domain_Userbot_Exception_UserbotNotFound("userbot ot token is not found");
			}
			throw new ReturnFatalException("passed unknown error_code");
		}
	}

	/**
	 * включаем бота
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function enableUserbot(string $userbot_id, string $token):void {

		$ar_post = [
			"userbot_id"         => $userbot_id,
			"token"              => $token,
			"client_launch_uuid" => getClientLaunchUUID(),
		];

		// делаем запрос
		[$status,] = self::_call("company.userbot.enable", $ar_post, 0);

		if ($status != "ok") {

			if (isset($response["error_code"]) && $response["error_code"] == 1434003) {
				throw new Domain_Userbot_Exception_UserbotNotFound("userbot ot token is not found");
			}
			throw new ReturnFatalException("passed unknown error_code");
		}
	}

	/**
	 * выключаем бота
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function disableUserbot(string $userbot_id, string $token):void {

		$ar_post = [
			"userbot_id"         => $userbot_id,
			"token"              => $token,
			"client_launch_uuid" => getClientLaunchUUID(),
		];

		// делаем запрос
		[$status] = self::_call("company.userbot.disable", $ar_post, 0);

		if ($status != "ok") {

			if (isset($response["error_code"]) && $response["error_code"] == 1434003) {
				throw new Domain_Userbot_Exception_UserbotNotFound("userbot ot token is not found");
			}
			throw new ReturnFatalException("passed unknown error_code");
		}
	}

	/**
	 * удаляем бота
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function deleteUserbot(string $userbot_id, string $token):void {

		$ar_post = [
			"userbot_id"         => $userbot_id,
			"token"              => $token,
			"client_launch_uuid" => getClientLaunchUUID(),
		];

		// делаем запрос
		[$status,] = self::_call("company.userbot.delete", $ar_post, 0);

		if ($status != "ok") {

			if (isset($response["error_code"]) && $response["error_code"] == 1434003) {
				throw new Domain_Userbot_Exception_UserbotNotFound("userbot ot token is not found");
			}
			throw new ReturnFatalException("passed unknown error_code");
		}
	}

	/**
	 * обновляем ключ шифрования
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function refreshSecretKey(string $token):string {

		$ar_post = [
			"token" => $token,
		];

		// делаем запрос
		[$status, $response] = self::_call("company.userbot.refreshSecretKey", $ar_post, 0);

		if ($status != "ok") {

			if (isset($response["error_code"]) && $response["error_code"] == 1434003) {
				throw new Domain_Userbot_Exception_UserbotNotFound("userbot ot token is not found");
			}

			throw new ReturnFatalException("passed unknown error_code");
		}

		return $response["secret_key"];
	}

	/**
	 * обновляем токен бота
	 *
	 * @throws ReturnFatalException
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws ParseFatalException
	 */
	public static function refreshToken(string $token):string {

		$ar_post = [
			"token" => $token,
		];

		// делаем запрос
		[$status, $response] = self::_call("company.userbot.refreshToken", $ar_post, 0);

		if ($status != "ok") {

			if (isset($response["error_code"]) && $response["error_code"] == 1434003) {
				throw new Domain_Userbot_Exception_UserbotNotFound("userbot ot token is not found");
			}

			throw new ReturnFatalException("passed unknown error_code");
		}

		return $response["token"];
	}

	/**
	 * Получает данные бота по токену
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getUserbotInfo(string $token):array {

		// делаем запрос
		$ar_post = ["token" => $token];
		[$status, $response] = self::_call("userbot.getInfo", $ar_post, 0);

		if ($status != "ok") {

			if (isset($response["error_code"]) && $response["error_code"] == 1434003) {
				throw new Domain_Userbot_Exception_UserbotNotFound("userbot ot token is not found");
			}

			throw new ReturnFatalException("passed unknown error_code");
		}

		return [
			$response["userbot_id"],
			$response["status"],
			$response["company_url"],
			$response["secret_key"],
			$response["is_react_command"],
			$response["userbot_user_id"],
		];
	}

	/**
	 * Отправляем запрос на удаление аватара компании
	 *
	 * @param int $user_id
	 *
	 * @return void
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function clearAvatarCompany(int $user_id):void {

		[$status, $response] = self::_call("company.clearAvatar", [], $user_id);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * Отправляем запрос на изменение информации профиля компании
	 */
	public static function changeInfoCompany(int $user_id, string|false $name, string|false $avatar_file_key):array {

		$params = [];

		if ($name !== false) {
			$params["name"] = $name;
		}

		if ($avatar_file_key !== false) {
			$params["avatar_file_key"] = $avatar_file_key;
		}

		[$status, $response] = self::_call("company.changeInfo", $params, $user_id);

		if ($status != "ok") {

			if (isset($response["error_code"]) && $response["error_code"] == 143001) {
				throw new Domain_Company_Exception_IncorrectAvatarFileKey("incorrect avatar_file_key");
			}

			throw new ParseFatalException("passed unknown error_code");
		}

		return [$response["name"], $response["avatar_file_key"]];
	}

	/**
	 * получаем время активности премиума пользователей
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function getPremiumActiveAtList(array $user_id_list):array {

		$ar_post = [
			"user_id_list" => $user_id_list,
		];

		[$status, $response] = self::_call("premium.getPremiumActiveAtList", $ar_post, 0);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return [$response["premium_active_list"], $response["deleted_user_id_list"]];
	}

	/**
	 * получаем время активности премиума пользователей
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	public static function getPremiumActiveTillListByOrder(string $sort_order):array {

		$ar_post = [
			"sort_order" => $sort_order,
		];

		[$status, $response] = self::_call("premium.getPremiumActiveTillListByOrder", $ar_post, 0);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return [$response["premium_active_list"], $response["deleted_user_id_list"]];
	}

	/**
	 * удаляем компанию
	 *
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \cs_CompanyUserIsNotOwner
	 * @throws \cs_UserIsNotMember
	 * @throws ParseFatalException
	 */
	public static function deleteCompany(int $user_id):void {

		$ar_post = [];

		try {
			[$status, $response] = self::_call("company.delete", $ar_post, $user_id);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() == 490) {
				throw new \BaseFrame\Exception\Request\CompanyIsHibernatedException("company is hibernation");
			}

			if ($e->getHttpStatusCode() == 491) {
				throw new \BaseFrame\Exception\Request\CompanyIsRelocatingException("company is relocation");
			}

			throw new ParseFatalException("passed unknown http_code");
		}

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ParseFatalException("passed unknown error_code");
			}

			throw match ($response["error_code"]) {
				1403001 => new \cs_CompanyUserIsNotOwner("user is not owner of company"),
				1403002 => new \cs_UserIsNotMember("user is not member of company"),
				1403003 => new ParseFatalException("company not exist"),
				default => new ParseFatalException("passed unknown error_code"),
			};
		}
	}

	/**
	 * получаем данные для аналитики пространства
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @long
	 */
	public static function getSpaceAnalyticsInfo():array {

		$ar_post = [];

		try {
			[$status, $response] = self::_call("company.getAnalyticsInfo", $ar_post, 0);
		} catch (\cs_SocketRequestIsFailed $e) {

			self::_throwExceptionByStatusCode($e->getHttpStatusCode(),
				new ParseFatalException("socket system.getScriptCompanyUserList failed. Error: " . $e->getMessage()));
		}

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ParseFatalException("passed unknown error_code");
			}

			throw match ($response["error_code"]) {
				1403003 => new ParseFatalException("company not exist"),
				default => new ParseFatalException("passed unknown error_code"),
			};
		}

		return [
			$response["user_id_creator"], $response["tariff_status"], $response["max_member_count"],
			$response["user_id_payer_list"], $response["space_deleted_at"],
		];
	}

	/**
	 * Увеличить лимит количества участников в пространстве
	 *
	 * @return int[]
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public static function increaseMemberCountLimit(int $user_id):array {

		try {
			[$status, $response] = self::_call("tariff.increaseMemberCountLimit", [], $user_id);
		} catch (\cs_SocketRequestIsFailed $e) {

			self::_throwExceptionByStatusCode($e->getHttpStatusCode(),
				new ParseFatalException("socket tariff.increaseMemberCountLimit failed. Error: " . $e->getMessage()));
		}

		self::_throwOnUnknownErrorCode($status, $response);

		return [(bool) $response["can_increase"], (bool) $response["is_trial_activated"]];
	}

	/**
	 * Увеличить лимит количества участников в пространстве
	 *
	 * @return int[]
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public static function tryStartTrial(int $user_id):bool {

		try {
			[$status, $response] = self::_call("tariff.tryStartTrial", [], $user_id);
		} catch (\cs_SocketRequestIsFailed $e) {

			self::_throwExceptionByStatusCode($e->getHttpStatusCode(),
				new ParseFatalException("socket tariff.tryStartTrial failed. Error: " . $e->getMessage()));
		}

		self::_throwOnUnknownErrorCode($status, $response);

		return $response["is_trial_activated"];
	}

	/**
	 * Получить статистику пользователя по экранному времени
	 *
	 * @param int $user_id
	 * @param int $days_count
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public static function getScreenTimeStat(int $user_id, int $days_count):array {

		try {
			$ar_post = [
				"user_id"    => $user_id,
				"days_count" => $days_count,
			];
			[$status, $response] = self::_call("user.getScreenTimeStat", $ar_post, 0);
		} catch (\cs_SocketRequestIsFailed $e) {

			self::_throwExceptionByStatusCode($e->getHttpStatusCode(), new ParseFatalException("socket user.getScreenTimeStat failed. Error: " . $e->getMessage()));
		}

		self::_throwOnUnknownErrorCode($status, $response);

		return $response["stat_list"];
	}

	/**
	 * При переводе гостя в роль участника пространства
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws \Throwable
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 */
	public static function onUpgradeGuest():void {

		try {
			[$status, $response] = self::_call("company.member.onUpgradeGuest", [], 0);
		} catch (\cs_SocketRequestIsFailed $e) {

			self::_throwExceptionByStatusCode($e->getHttpStatusCode(), new ParseFatalException("socket user.getScreenTimeStat failed. Error: " . $e->getMessage()));
		}

		self::_throwOnUnknownErrorCode($status, $response);
	}

	/**
	 * Получить user_id рут-пользователя он-премайз.
	 */
	public static function getRootUserId():int {

		try {
			[$status, $response] = self::_call("system.getRootUserId", [], 0);
		} catch (\cs_SocketRequestIsFailed $e) {

			self::_throwExceptionByStatusCode($e->getHttpStatusCode(), new ParseFatalException("socket user.getScreenTimeStat failed. Error: " . $e->getMessage()));
		}

		self::_throwOnUnknownErrorCode($status, $response);

		return $response["user_id"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получаем подпись из массива параметров.
	 *
	 * @param string $method
	 * @param array  $params
	 * @param int    $user_id
	 *
	 * @return array
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 */
	protected static function _call(string $method, array $params, int $user_id):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url       = self::_getUrl();
		$signature = \BaseFrame\Socket\Authorization\Handler::getSignature(
			\BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_SSL, COMPANY_TO_PIVOT_PRIVATE_KEY, $json_params
		);

		return \BaseFrame\Socket\Main::doCall($url, $method, $json_params, $signature, CURRENT_MODULE, COMPANY_ID, $user_id);
	}

	/**
	 * Выбрасываем нужно исключение, ориентируясь на http status code
	 *
	 * @param int       $http_status_code
	 * @param Throwable $default_exception
	 *
	 * @throws \BaseFrame\Exception\Request\CompanyIsRelocatingException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \Throwable
	 * @throws \BaseFrame\Exception\Request\CompanyIsHibernatedException
	 */
	protected static function _throwExceptionByStatusCode(int $http_status_code, \Throwable $default_exception):void {

		match ($http_status_code) {
			490     => throw new \BaseFrame\Exception\Request\CompanyIsHibernatedException("company is hibernation"),
			491     => throw new \BaseFrame\Exception\Request\CompanyIsRelocatingException("company is relocation"),
			404     => throw new \BaseFrame\Exception\Request\CompanyNotServedException("company is not served"),
			default => throw $default_exception,
		};
	}

	/**
	 * Выбрасываем исключение, если пришла неизвестная ошибка
	 *
	 * @throws ParseFatalException
	 */
	protected static function _throwOnUnknownErrorCode(string $status, array $response):void {

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ParseFatalException("passed unknown error_code");
			}
		}
	}

	/**
	 * получаем url
	 *
	 * @return string
	 */
	protected static function _getUrl():string {

		$socket_url_config    = getConfig("SOCKET_URL");
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $socket_url_config["pivot"] . $socket_module_config["pivot"]["socket_path"];
	}
}
