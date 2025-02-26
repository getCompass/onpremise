<?php

declare(strict_types = 1);

namespace Compass\Userbot;

/**
 * Крон для очереди команд для внешнего сервиса
 */
class Cron_CommandQueue extends \Cron_Default {

	protected const _PRODUCER_LIMIT  = 20; // получаем за раз несколько записей
	protected const _MAX_ERROR_COUNT = 5;  // лимит для ошибок запроса

	// таймаут для отправки запроса на вебхук
	protected const _WEBHOOK_REQUEST_CURL_TIMEOUT = 10;

	// need_work для выполнения следующих задач
	// (!!! с учётом таймаута отправки запроса, чтобы бот дважды не отправил команду на вебхук)
	protected const _NEED_WORK_INTERVAL = self::_WEBHOOK_REQUEST_CURL_TIMEOUT + 2;

	// -------------------------------------------------------
	// region PRODUCER
	// -------------------------------------------------------

	/**
	 * producer
	 */
	public function work():void {

		// получаем задачи на выполнение
		$command_list = $this->_getList();

		// проверяем, может задач нет
		if (count($command_list) < 1) {

			$this->say("no tasks in database, sleep 1s");
			$this->sleep(1);
			return;
		}

		// собираем задачи для обновления
		$task_id_list = $this->_makeIn($command_list);

		// обновляем список задач
		$this->_updateTaskList($task_id_list);
		$this->say($task_id_list);

		// отправляем в rabbit
		$this->_sendToRabbit($command_list);

		$this->sleep(0);
	}

	/**
	 * функция для получения задач из базы
	 */
	protected function _getList():array {

		$offset = $this->bot_num * self::_PRODUCER_LIMIT;
		return Gateway_Db_UserbotMain_CommandQueue::getList(self::_PRODUCER_LIMIT, $offset);
	}

	/**
	 * формируем массив идентификаторов задач для обновления
	 */
	protected function _makeIn(array $list):array {

		$task_id_list = [];

		/** @var Struct_Db_UserbotMain_Command $command */
		foreach ($list as $command) {
			$task_id_list[] = $command->task_id;
		}

		return $task_id_list;
	}

	/**
	 * функция для обновления записи с задачей в базе
	 *
	 * @param int[] $in
	 */
	protected function _updateTaskList(array $task_id_list):void {

		$set = [
			"need_work"   => time() + self::_NEED_WORK_INTERVAL,
			"error_count" => "error_count + 1",
		];
		Gateway_Db_UserbotMain_CommandQueue::updateList($task_id_list, $set);
	}

	/**
	 * функция для отправки задачи в doWork
	 *
	 * @param Struct_Db_UserbotMain_Command[] $list
	 */
	protected function _sendToRabbit(array $list):void {

		$task_id_list = [];
		foreach ($list as $command) {

			// если вдруг поймаем максимальное количество ошибок, то удаляем задачу
			if ($command->error_count >= self::_MAX_ERROR_COUNT) {

				$task_id_list[] = $command->task_id;
				continue;
			}

			// отправляем задачу consumer
			$this->doQueue((array) $command);
		}

		// удаляем задачи, которые набрали лимит ошибок при выполнении
		if (count($task_id_list) < 1) {
			return;
		}
		Gateway_Db_UserbotMain_CommandQueue::deleteList($task_id_list);
	}

	// endregion PRODUCER
	// -------------------------------------------------------

	// -------------------------------------------------------
	// region CONSUMER
	// -------------------------------------------------------

	/**
	 * consumer
	 * @long
	 */
	public function doWork(array $command):void {

		$token      = $command["params"]["token"];
		$secret_key = $command["params"]["secret_key"];
		$webhook    = $command["params"]["webhook"];

		$type            = $command["params"]["type"];
		$user_id         = $command["params"]["user_id"];
		$message_id      = $command["params"]["message_id"];
		$group_id        = $command["params"]["group_id"];
		$command_text    = $command["params"]["command"];
		$webhook_version = $command["params"]["webhook_version"] ?? Domain_Userbot_Entity_Userbot::USERBOT_WEBHOOK_VERSION_1;

		// формируем массив для отправки
		$params = [
			"group_id"   => $group_id,
			"message_id" => $message_id,
			"text"       => $command_text,
			"type"       => $type,
			"user_id"    => $user_id,
		];

		// получаем подпись
		$signature = match ($webhook_version) {
			Domain_Userbot_Entity_Userbot::USERBOT_WEBHOOK_VERSION_2 => Type_Userbot_Main::getApiSignature(toJson($params), $token, $secret_key),
			default                                                  => Type_Userbot_Main::getApiV1Signature($params, $token, $secret_key),
		};

		$curl = new \Curl();
		$curl->setTimeout(self::_WEBHOOK_REQUEST_CURL_TIMEOUT);

		// в зависимости от версии готовим данные для запроса
		switch ($webhook_version) {

			case Domain_Userbot_Entity_Userbot::USERBOT_WEBHOOK_VERSION_3:
				[$formatted_params, $headers] = $this->_getDataForV3($token, $signature, $params);
				break;

			case Domain_Userbot_Entity_Userbot::USERBOT_WEBHOOK_VERSION_2:
				[$formatted_params, $headers] = $this->_getDataForV2($token, $signature, $params);
				break;

			default:
				[$formatted_params, $headers] = $this->_getDataForV1($token, $signature, $params);
		}

		// отправляем запрос
		try {

			$response = $curl->post($webhook, $formatted_params, $headers);
			$response = fromJson($response);
		} catch (\Exception|\Error) {
			return; // если запрос не прошёл
		}

		// если от webhook сервиса пришли данные для синхронного ответа пользователю
		if (isset($response["answer"]) && $webhook_version >= Domain_Userbot_Entity_Userbot::USERBOT_WEBHOOK_VERSION_3) {

			try {
				Domain_Userbot_Action_SendWebhookAnswer::do($token, $response["answer"], $group_id, $message_id, $user_id);
			} catch (\Exception|\Error) {
				return; // если запрос не прошёл
			}
		}

		// в случае если всё ок, удаляем задачу
		Gateway_Db_UserbotMain_CommandQueue::deleteList([$command["task_id"]]);
	}

	/**
	 * получаем данные для v1 ботов
	 */
	protected function _getDataForV1(string $token, string $signature, array $params):array {

		$formatted_params = [
			"payload"   => $params,
			"signature" => $signature,
			"token"     => $token,
		];

		return [$formatted_params, []];
	}

	/**
	 * получаем данные для v2 ботов
	 */
	protected function _getDataForV2(string $token, string $signature, array $params):array {

		$headers = [
			"Authorization" => "bearer={$token}",
			"Signature"     => "signature={$signature}",
			"Content-Type"  => "application/json",
		];

		return [toJson($params), $headers];
	}

	/**
	 * получаем данные для v3 ботов
	 */
	protected function _getDataForV3(string $token, string $signature, array $params):array {

		$headers = [
			"Authorization" => "bearer={$token}",
			"Signature"     => "signature={$signature}",
			"Content-Type"  => "application/json",
		];

		return [toJson($params), $headers];
	}

	// endregion CONSUMER
	// -------------------------------------------------------
}