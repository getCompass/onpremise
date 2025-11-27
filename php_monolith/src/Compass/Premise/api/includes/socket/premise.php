<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * Точка входа получения данных о premise-решении.
 */
class Socket_Premise extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"getServerInfo",
		"userRegistered",
		"setPermissions",
	];

	/**
	 * Возвращает информацию о premise-сервере
	 */
	public function getServerInfo():array {

		[$domain, $server_uid, $secret_key] = Domain_Premise_Scenario_Socket::getServerInfo();

		return $this->ok([
			"domain"     => (string) $domain,
			"server_uid" => (string) $server_uid,
			"secret_key" => (string) $secret_key,
		]);
	}

	/**
	 * Вызывается при регистрации пользователя.
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function userRegistered():array {

		$user_id  = $this->post(\Formatter::TYPE_INT, "user_id");
		$npc_type = $this->post(\Formatter::TYPE_INT, "npc_type");
		$is_root  = $this->post(\Formatter::TYPE_INT, "is_root") == 1;

		Domain_Premise_Scenario_Socket::userRegistered($user_id, $npc_type, $is_root);

		return $this->ok();
	}

	/**
	 * Выдаем права пользователю
	 *
	 * @throws ParseFatalException
	 * @throws ReturnFatalException
	 * @throws ParamException
	 * @throws \parseException
	 */
	public function setPermissions():array {

		$premise_permissions = $this->post(\Formatter::TYPE_ARRAY, "premise_permissions");
		$member_user_id      = $this->post(\Formatter::TYPE_INT, "member_user_id");
		$admin_user_id       = $this->post(\Formatter::TYPE_INT, "admin_user_id");

		try {

			Domain_User_Scenario_Api::setPermissions($admin_user_id, $member_user_id, $premise_permissions);
		} catch (Domain_User_Exception_SelfDisabledPermissions) {
			return $this->error(7201002, "try disabled self permissions");
		} catch (Domain_User_Exception_UserHaveNotPermissions) {
			return $this->error(7201001, "user have not permissions");
		} catch (Domain_User_Exception_IsDisabled) {
			return $this->error(7201004, "user is deleted");
		} catch (Domain_User_Exception_NotFound) {
			return $this->error("user not found");
		}
		return $this->ok();
	}
}
