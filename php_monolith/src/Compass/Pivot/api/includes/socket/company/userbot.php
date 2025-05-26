<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Контроллер сокет методов для взаимодействия с данными бота между pivot сервером и компаниями
 */
class Socket_Company_Userbot extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"create",
		"edit",
		"enable",
		"disable",
		"delete",
		"refreshSecretKey",
		"refreshToken",
		"doCommand",
	];

	/**
	 * создаём бота
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function create():array {

		$userbot_name             = $this->post(\Formatter::TYPE_STRING, "userbot_name");
		$avatar_color_id          = $this->post(\Formatter::TYPE_INT, "avatar_id"); // deprecated
		$avatar_file_key          = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", false);
		$is_react_command         = $this->post(\Formatter::TYPE_INT, "is_react_command", 0);
		$webhook                  = $this->post(\Formatter::TYPE_STRING, "webhook", "");
		$is_smart_app             = $this->post(\Formatter::TYPE_INT, "is_smart_app", 0);
		$smart_app_name           = $this->post(\Formatter::TYPE_STRING, "smart_app_name", "");
		$smart_app_url            = $this->post(\Formatter::TYPE_STRING, "smart_app_url", "");
		$is_smart_app_sip         = $this->post(\Formatter::TYPE_INT, "is_smart_app_sip", 0);
		$is_smart_app_mail        = $this->post(\Formatter::TYPE_INT, "is_smart_app_mail", 0);
		$smart_app_default_width  = $this->post(\Formatter::TYPE_INT, "smart_app_default_width", 414);
		$smart_app_default_height = $this->post(\Formatter::TYPE_INT, "smart_app_default_height", 896);
		$role                     = $this->post(\Formatter::TYPE_INT, "role");
		$permissions              = $this->post(\Formatter::TYPE_INT, "permissions");

		try {

			[
				$userbot_id,
				$user_id,
				$token,
				$secret_key,
				$avatar_file_key,
				$npc_type,
			] = Domain_Userbot_Scenario_Socket::create($this->company_id, $userbot_name, $avatar_color_id, $avatar_file_key, $is_react_command, $webhook,
				$is_smart_app, $smart_app_name, $smart_app_url, $is_smart_app_sip, $is_smart_app_mail, $smart_app_default_width, $smart_app_default_height,
				$role, $permissions
			);
		} catch (Domain_Userbot_Exception_IncorrectParam) {
			return $this->error(1434001, "incorrect avatar for userbot");
		} catch (cs_CompanyIncorrectCompanyId|cs_DamagedActionException|\cs_DecryptHasFailed|\BaseFrame\Exception\Domain\InvalidPhoneNumber) {
			return $this->error(1434002, "something strange happened on try create userbot");
		}

		return $this->ok([
			"userbot_id"      => (string) $userbot_id,
			"token"           => (string) $token,
			"secret_key"      => (string) $secret_key,
			"user_id"         => (int) $user_id,
			"avatar_file_key" => (string) $avatar_file_key,
			"npc_type"        => (int) $npc_type,
		]);
	}

	/**
	 * Редактируем бота
	 *
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws cs_FileIsNotImage
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function edit():array {

		$userbot_id               = $this->post(\Formatter::TYPE_STRING, "userbot_id");
		$token                    = $this->post(\Formatter::TYPE_STRING, "token");
		$client_launch_uuid       = $this->post(\Formatter::TYPE_STRING, "client_launch_uuid");
		$userbot_name             = $this->post(\Formatter::TYPE_STRING, "userbot_name", false);
		$is_react_command         = $this->post(\Formatter::TYPE_INT, "is_react_command", false);
		$webhook                  = $this->post(\Formatter::TYPE_STRING, "webhook", false);
		$is_smart_app             = $this->post(\Formatter::TYPE_INT, "is_smart_app", false);
		$smart_app_name           = $this->post(\Formatter::TYPE_STRING, "smart_app_name", false);
		$smart_app_url            = $this->post(\Formatter::TYPE_STRING, "smart_app_url", false);
		$is_smart_app_sip         = $this->post(\Formatter::TYPE_INT, "is_smart_app_sip", false);
		$is_smart_app_mail        = $this->post(\Formatter::TYPE_INT, "is_smart_app_mail", false);
		$smart_app_default_width  = $this->post(\Formatter::TYPE_INT, "smart_app_default_width", false);
		$smart_app_default_height = $this->post(\Formatter::TYPE_INT, "smart_app_default_height", false);
		$avatar_color_id          = $this->post(\Formatter::TYPE_INT, "avatar_color_id", false); // deprecated
		$avatar_file_key          = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", false);

		try {
			Domain_Userbot_Scenario_Socket::edit($userbot_id, $token, $userbot_name, $is_react_command, $webhook,
				$is_smart_app, $smart_app_name, $smart_app_url, $is_smart_app_sip, $is_smart_app_mail, $smart_app_default_width, $smart_app_default_height,
				$avatar_color_id, $avatar_file_key, $client_launch_uuid
			);
		} catch (\cs_RowIsEmpty) {
			return $this->error(1434003, "userbot or token is not found");
		}

		return $this->ok();
	}

	/**
	 * Включаем бота
	 *
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function enable():array {

		$userbot_id         = $this->post(\Formatter::TYPE_STRING, "userbot_id");
		$token              = $this->post(\Formatter::TYPE_STRING, "token");
		$client_launch_uuid = $this->post(\Formatter::TYPE_STRING, "client_launch_uuid");

		try {
			Domain_Userbot_Scenario_Socket::enable($userbot_id, $token, $client_launch_uuid);
		} catch (\cs_RowIsEmpty) {
			return $this->error(1434003, "userbot or token is not found");
		}

		return $this->ok();
	}

	/**
	 * Отключаем бота
	 *
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function disable():array {

		$userbot_id         = $this->post(\Formatter::TYPE_STRING, "userbot_id");
		$token              = $this->post(\Formatter::TYPE_STRING, "token");
		$client_launch_uuid = $this->post(\Formatter::TYPE_STRING, "client_launch_uuid");

		try {
			Domain_Userbot_Scenario_Socket::disable($userbot_id, $token, $client_launch_uuid);
		} catch (\cs_RowIsEmpty) {
			return $this->error(1434003, "userbot or token is not found");
		}

		return $this->ok();
	}

	/**
	 * удаляем бота
	 *
	 * @throws \busException
	 * @throws \cs_DecryptHasFailed
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function delete():array {

		$userbot_id         = $this->post(\Formatter::TYPE_STRING, "userbot_id");
		$token              = $this->post(\Formatter::TYPE_STRING, "token");
		$client_launch_uuid = $this->post(\Formatter::TYPE_STRING, "client_launch_uuid");

		try {
			Domain_Userbot_Scenario_Socket::delete($userbot_id, $token, $client_launch_uuid);
		} catch (\cs_RowIsEmpty) {
			return $this->error(1434003, "userbot or token is not found");
		}

		return $this->ok();
	}

	/**
	 * обновляем ключ шифрования
	 *
	 * @throws \busException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function refreshSecretKey():array {

		$token = $this->post(\Formatter::TYPE_STRING, "token");

		try {
			$secret_key = Domain_Userbot_Scenario_Socket::refreshSecretKey($token);
		} catch (\cs_RowIsEmpty) {
			return $this->error(1434003, "userbot or token is not found");
		}

		return $this->ok([
			"secret_key" => (string) $secret_key,
		]);
	}

	/**
	 * обновляем токен бота
	 *
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function refreshToken():array {

		$token = $this->post(\Formatter::TYPE_STRING, "token");

		try {
			$new_token = Domain_Userbot_Scenario_Socket::refreshToken($token);
		} catch (\cs_RowIsEmpty) {
			return $this->error(1434003, "userbot or token is not found");
		}

		return $this->ok([
			"token" => (string) $new_token,
		]);
	}

	/**
	 * выполняем команду бота
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws cs_CompanyNotExist
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function doCommand():array {

		$payload    = $this->post(\Formatter::TYPE_ARRAY, "payload");
		$company_id = $this->post(\Formatter::TYPE_INT, "company_id");

		Domain_Userbot_Scenario_Socket::doCommand($payload, $company_id);

		return $this->ok();
	}
}
