<?php

namespace Compass\Premise;

/**
 * Точка входа получения данных о premise-решении.
 */
class Socket_Premise extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"getServerInfo",
		"userRegistered",
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
}
