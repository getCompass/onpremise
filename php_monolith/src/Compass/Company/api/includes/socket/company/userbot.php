<?php

namespace Compass\Company;

/**
 * контроллер для работы методов с пользовательским ботом
 */
class Socket_Company_Userbot extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"getUserList",
		"getUserbotIdByUserId",
		"getStatusByUserId",
		"kickFromGroup",
		"updateCommandList",
		"getCommandList",
		"getGroupList",
		"doCommand",
	];

	/**
	 * получаем список информации по пользователям
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \returnException
	 */
	public function getUserList():array {

		$count  = $this->post(\Formatter::TYPE_INT, "count", 100);
		$offset = $this->post(\Formatter::TYPE_INT, "offset", 0);

		// достаём пользователей
		$formatted_user_list = Domain_Userbot_Scenario_Socket::getUserInfo($count, $offset);

		return $this->ok([
			"user_list" => (array) $formatted_user_list,
		]);
	}

	/**
	 * получаем userbot_id бота по его id пользователя
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getUserbotIdByUserId():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		// достаём id бота
		$userbot_id = Domain_Userbot_Scenario_Socket::getUserbotId($user_id);

		return $this->ok([
			"userbot_id" => (string) $userbot_id,
		]);
	}

	/**
	 * получаем статус бота по его id пользователя
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getStatusByUserId():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id");

		// достаём статус
		$status = Domain_Userbot_Scenario_Socket::getUserbotStatus($user_id);

		return $this->ok([
			"status" => (string) $status,
		]);
	}

	/**
	 * кикаем бота из группы
	 *
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function kickFromGroup():array {

		$user_id          = $this->post(\Formatter::TYPE_INT, "user_id");
		$conversation_map = $this->post(\Formatter::TYPE_STRING, "conversation_map");

		// кикаем
		$userbot_id = Domain_Userbot_Scenario_Socket::kickUserbotFromGroup($user_id, $conversation_map);

		return $this->ok([
			"userbot_id" => (string) $userbot_id,
		]);
	}

	/**
	 * обновляем список команд бота
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \parseException
	 */
	public function updateCommandList():array {

		$userbot_id   = $this->post(\Formatter::TYPE_STRING, "userbot_id");
		$command_list = $this->post(\Formatter::TYPE_ARRAY, "command_list");

		try {
			Domain_Userbot_Scenario_Socket::updateCommandList($userbot_id, $command_list);
		} catch (Domain_Userbot_Exception_IncorrectParam) {
			return $this->error(10012, "command list is incorrect");
		}

		return $this->ok();
	}

	/**
	 * получаем список команд бота
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \BaseFrame\Exception\Request\ParamException
	 */
	public function getCommandList():array {

		$userbot_id = $this->post(\Formatter::TYPE_STRING, "userbot_id");

		$command_list = Domain_Userbot_Scenario_Socket::getCommandList($userbot_id);

		return $this->ok([
			"command_list" => (array) $command_list,
		]);
	}

	/**
	 * получаем группы бота
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \cs_DecryptHasFailed
	 * @throws \returnException
	 */
	public function getGroupList():array {

		$userbot_id = $this->post(\Formatter::TYPE_STRING, "userbot_id");

		$group_info_list = Domain_Userbot_Scenario_Socket::getGroupList($userbot_id);

		return $this->ok([
			"group_info_list" => (array) $group_info_list,
		]);
	}

	/**
	 * выполнить команду бота
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Request\ParamException
	 * @throws \parseException
	 * @throws \returnException
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 */
	public function doCommand():array {

		$payload = $this->post(\Formatter::TYPE_ARRAY, "payload");

		$token            = $payload["token"];
		$text             = $payload["text"];
		$user_id          = $payload["user_id"];
		$conversation_key = $payload["group_id"];
		$message_key      = $payload["message_id"];

		Domain_Userbot_Scenario_Socket::doCommand($token, $text, $user_id, $conversation_key, $message_key);

		return $this->ok();
	}
}
