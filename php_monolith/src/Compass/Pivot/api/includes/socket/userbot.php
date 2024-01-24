<?php declare(strict_types = 1);

namespace Compass\Pivot;

/**
 * Контроллер сокет методов для взаимодействия с данными бота
 */
class Socket_Userbot extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getInfo",
		"getInfoLastCreated",
		"setWebhookVersion",
	];

	/**
	 * получаем информацию бота
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function getInfo():array {

		$token = $this->post(\Formatter::TYPE_STRING, "token");

		try {
			[
				$userbot_id,
				$status,
				$company_url,
				$domino_entrypoint,
				$company_id,
				$secret_key,
				$is_react_command,
				$userbot_user_id,
				$extra,
			] = Domain_Userbot_Scenario_Socket::getInfo($token);
		} catch (\cs_RowIsEmpty) {
			return $this->error(1434003, "userbot or token is not found");
		}

		return $this->ok([
			"userbot_id"        => (string) $userbot_id,
			"status"            => (int) $status,
			"company_url"	  => (string) $company_url,
			"domino_entrypoint" => (string) $domino_entrypoint,
			"company_id"        => (int) $company_id,
			"secret_key"        => (string) $secret_key,
			"is_react_command"  => (int) $is_react_command,
			"userbot_user_id"   => (int) $userbot_user_id,
			"extra"             => (string) toJson($extra),
		]);
	}

	/**
	 * получаем последнего созданного  бота (Только для тестов!!!)
	 */
	public function getInfoLastCreated():array {

		assertTestServer();

		try {
			[$token, $userbot_id, $secret_key] = Domain_Userbot_Scenario_Socket::getLastCreated();
		} catch (\cs_RowIsEmpty) {
			return $this->error(1434003, "userbot not found");
		}

		return $this->ok([
			"userbot_id" => (string) $userbot_id,
			"token"      => (string) $token,
			"secret_key" => (string) $secret_key,
		]);
	}

	/**
	 * устанавливаем версию webhook боту
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function setWebhookVersion():array {

		$userbot_id = $this->post(\Formatter::TYPE_STRING, "userbot_id");
		$token      = $this->post(\Formatter::TYPE_STRING, "token");
		$version    = $this->post(\Formatter::TYPE_INT, "version");

		try {

			Domain_Userbot_Scenario_Socket::setWebhookVersion($userbot_id, $token, $version);
		} catch (Domain_Userbot_Exception_IncorrectParam) {
			return $this->error(1434001, "incorrect webhook version for userbot");
		}

		return $this->ok();
	}
}
