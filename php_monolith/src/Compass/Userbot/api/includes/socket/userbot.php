<?php

namespace Compass\Userbot;

/**
 * класс для сокет-запросов пользовательского бота
 */
class Socket_Userbot extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"sendCommand",
	];

	/**
	 * метод для отправки команды во внешний сервис
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function sendCommand():array {

		$command    = $this->post(Type_Formatter::TYPE_STRING, "command");
		$token      = $this->post(Type_Formatter::TYPE_STRING, "token");
		$webhook    = $this->post(Type_Formatter::TYPE_STRING, "webhook");
		$user_id    = $this->post(Type_Formatter::TYPE_INT, "user_id");
		$message_id = $this->post(Type_Formatter::TYPE_STRING, "message_id");
		$group_id   = $this->post(Type_Formatter::TYPE_STRING, "group_id");

		Domain_Userbot_Scenario_Socket::sendCommand($token, $command, $webhook, $user_id, $message_id, $group_id);

		return $this->ok();
	}
}
