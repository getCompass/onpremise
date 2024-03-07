<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Крон для отправки электронных писем
 */
class Cron_Mail_Dispatcher extends \Cron_Default {

	// макс кол-во ошибок у задачи
	protected const _MAX_ERROR_COUNT = 6;

	// лимит задач, которые берет продюсер за раз
	protected const _PRODUCER_LIMIT = 30;

	// интервал need_work для продюсера
	protected const _NEED_WORK_INTERVAL = 20;

	/**
	 * Получаем задачи из очереди в таблице
	 */
	public function work():void {

		// получаем задачи из базы
		$list = Type_Mail_Queue::getList($this->bot_num, self::_PRODUCER_LIMIT);

		// проверям может задачи нет
		if (count($list) < 1) {

			$this->say("no tasks in database, sleep 1s");
			$this->sleep(1);
			return;
		}

		// обновляем задачи по need_work и увеличиваем error_count
		Type_Mail_Queue::updateTaskList(array_column($list, "message_id"), self::_NEED_WORK_INTERVAL);

		// отправляем задачу в doWork
		$this->_sendToRabbit($list);
	}

	/**
	 * Отправляем задача консамеру
	 *
	 * @param Struct_Db_PivotMailService_Task[] $list
	 */
	protected function _sendToRabbit(array $list):void {

		foreach ($list as $v) {

			// если вдруг поймаем максимальное количество ошибок, то удаляем задачу
			if ($v->error_count >= self::_MAX_ERROR_COUNT) {

				Type_Mail_Queue::deleteTask($v->message_id);
				continue;
			}

			// отправляем задачу consumer
			$this->doQueue((array) $v);
		}
	}

	/**
	 * Выполняем задачу консамером
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function doWork(array $row):void {

		// конвертим задачу в структуру
		$task = Struct_Db_PivotMailService_Task::rowToStruct($row);

		// работаем в зависимости от этапа задачи
		match ($task->stage) {
			Type_Mail_Queue::STAGE_SEND => self::_sendMailHandler($task),
			default                     => throw new ParseFatalException("unexpected stage: {$task->stage}"),
		};
	}

	/**
	 * обработчик отправки писем
	 *
	 * @throws ParseFatalException
	 * @throws \PHPMailer\PHPMailer\Exception
	 */
	protected static function _sendMailHandler(Struct_Db_PivotMailService_Task $task):void {

		// получаем провайдер, через который будем осуществлять отправку
		$provider_class = Type_Mail_Sender::getProvider();

		// клиент с помощью которого осуществляем отправку
		$smtp_connection_params = Type_Mail_Config::getSMTPConnectionParams();
		$sender                 = new $provider_class(
			$smtp_connection_params["host"],
			$smtp_connection_params["port"],
			$smtp_connection_params["encryption"],
			$smtp_connection_params["username"],
			$smtp_connection_params["password"],
			$smtp_connection_params["from"],
			"Compass On-premise"
		);

		// пытаемся отправить письмо
		$result = $sender->send($task->title, $task->content, $task->mail);

		// если письмо отправилось не успешно, то завершаем работу – всего есть self::_MAX_ERROR_COUNT попыток на отправку
		if (!$result) {
			return;
		}

		// иначе письмо отправилось успешно – удаляем задачу
		Type_Mail_Queue::deleteTask($task->message_id);
	}

	/**
	 * Возвращает экземпляр Rabbit для указанного ключа.
	 */
	protected static function _getBusInstance(string $bus_key):\Rabbit {

		return ShardingGateway::rabbit($bus_key);
	}

	/**
	 * Определяет имя крон-бота.
	 */
	protected static function _resolveBotName():string {

		return "pivot_" . parent::_resolveBotName();
	}
}