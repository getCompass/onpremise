<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Крон для отправки смс-сообщений и отслеживания их статуса отправки
 */
class Cron_Sms_Dispatcher extends \Cron_Default {

	// макс кол-во ошибок у задачи
	protected const _MAX_ERROR_COUNT = 6;

	protected const _DB_KEY    = "pivot_sms_service";
	protected const _TABLE_KEY = "send_queue";

	/**
	 * Получаем задачи из очереди в таблице
	 */
	public function work():void {

		// получаем задачи из базы
		$list = Type_Sms_Dispatcher::getList($this->bot_num);

		// проверям может задачи нет
		if (count($list) < 1) {

			$this->say("no tasks in database, sleep 1s");
			$this->sleep(1);
			return;
		}

		// формируем in
		[$in_need_send, $in_need_check] = $this->_makeIn($list);

		// обновляем задачи по need_work и увеличиваем error_count
		Type_Sms_Dispatcher::updateTaskList($in_need_send, $in_need_check);

		// отправляем задачу в doWork
		$this->_sendToRabbit($list);
	}

	/**
	 * Формируем два массива поделенный по этапу задачи для обновления задач
	 */
	protected function _makeIn(array $list):array {

		$in_need_send  = [];
		$in_need_check = [];
		foreach ($list as $v) {

			if ($v["stage"] === Type_Sms_Queue::STAGE_NEED_SEND_SMS) {
				$in_need_send[] = $v["sms_id"];
			} else {
				$in_need_check[] = $v["sms_id"];
			}
		}

		return [$in_need_send, $in_need_check];
	}

	/**
	 * Отправляем задача консамеру
	 */
	protected function _sendToRabbit(array $list):void {

		foreach ($list as $v) {

			// если вдруг поймаем максимальное количество ошибок, то удаляем задачу
			if ($v["error_count"] >= self::_MAX_ERROR_COUNT) {

				Type_Sms_Dispatcher::deleteTask($v["sms_id"]);
				continue;
			}

			// отправляем задачу consumer
			$this->doQueue($v);
		}
	}

	/**
	 * Выполняем задачу консамером
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function doWork(array $row):void {

		if (ServerProvider::isTest()) {
			Type_System_Admin::log("do_resend_sms_cest", ["work_time" => time(), "do_work_task" => $row]);
		}

		// конвертим задачу в структуру
		$send_queue = Struct_PivotSmsService_SendQueue::convertRowToStruct($row);

		// работаем в зависимости от этапа задачи
		switch ($row["stage"]) {

			case Type_Sms_Queue::STAGE_NEED_SEND_SMS:

				Type_Sms_Dispatcher::doWorkOnStageNeedSend($send_queue);
				break;

			case Type_Sms_Queue::STAGE_NEED_CHECK_STATUS_SMS:

				Type_Sms_Dispatcher::doWorkOnStageNeedCheckStatus($send_queue);
				break;

			default:
				throw new ParseFatalException(__METHOD__ . ": incorrect stage: " . $row["stage"]);
		}
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