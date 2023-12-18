<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\BlockException;

/**
 * контроллер для работы с уведомлениями в компании
 */
class Socket_Company_Notifications extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"setUserCompanyToken",
		"updateBadgeCount",
	];

	/**
	 * Установить токен для пушей в компании
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function setUserCompanyToken():array {

		$device_id          = $this->post(\Formatter::TYPE_STRING, "device_id");
		$user_company_token = $this->post(\Formatter::TYPE_STRING, "user_company_token");
		$is_add             = $this->post(\Formatter::TYPE_INT, "is_add");

		try {
			Domain_User_Scenario_Socket::setUserCompanyToken($this->user_id, $user_company_token, $device_id, $this->company_id, $is_add);
		} catch (BlockException) {
			return $this->error(423, "block triggered");
		}

		return $this->ok();
	}

	/**
	 * Изменить количество счетчика непрочитанных сообщений
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public function updateBadgeCount():array {

		$messages_unread_count = $this->post(\Formatter::TYPE_INT, "messages_unread_count");
		$inbox_unread_count    = $this->post(\Formatter::TYPE_INT, "inbox_unread_count");
		$task_list             = $this->post(\Formatter::TYPE_ARRAY, "task_list", []);

		$conversation_key_list = [];
		$thread_key_list       = [];

		foreach ($task_list as $task) {

			$task_arr = explode(".", $task);

			switch ($task_arr[0]) {

				case "conversation":
					$conversation_key_list[] = $task_arr[1];
					break;
				case "thread":
					$thread_key_list[] = $task_arr[1];
					break;
			}
		}

		try {
			Domain_User_Scenario_Socket::updateBadgeCount($this->user_id, $this->company_id, $messages_unread_count, $inbox_unread_count, $conversation_key_list, $thread_key_list);
		} catch (BlockException) {
			return $this->error(423, "block triggered");
		}

		return $this->ok();
	}
}