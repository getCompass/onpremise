<?php

namespace Compass\Pivot;

use BaseFrame\Exception\ExceptionUtils;

/**
 * Крон для выполнения сервисных задач для компании
 */
class Cron_Company_ServiceTask extends \Cron_Default {

	protected const _PRODUCER_LIMIT     = 30; // сколько берем задач за раз
	protected const _NEED_WORK_INTERVAL = 60; // интервал между задачами для продюсера

	/**
	 * Получаем задачи из очереди в таблице
	 */
	public function work():void {

		// получаем задачи из базы
		$task_list = $this->_getList();

		// проверям может задачи нет
		if (count($task_list) < 1) {

			$this->say("no tasks in database, sleep 1s");
			$this->sleep(1);
			return;
		}

		// увеличиваем need_work задаче, которую взяли в работу
		$task_id_list = array_column($task_list, "task_id");
		Gateway_Db_PivotCompanyService_CompanyServiceTask::setList($task_id_list, [
			"need_work" => time() + self::_NEED_WORK_INTERVAL,
		]);

		// отправляем задачи в doWork
		$this->_sendToRabbit($task_list);
	}

	/**
	 * Функция для получения задачи из базы
	 */
	protected function _getList():array {

		$offset = $this->bot_num * self::_PRODUCER_LIMIT;
		return Gateway_Db_PivotCompanyService_CompanyServiceTask::getForWork(time(), self::_PRODUCER_LIMIT, $offset);
	}

	/**
	 * Отправляем задачи консамеру
	 *
	 * @param array $company_service_task_list
	 */
	protected function _sendToRabbit(array $company_service_task_list):void {

		foreach ($company_service_task_list as $company_service_task) {

			$company_service_task                    = (array) $company_service_task;
			$company_service_task["task_time_start"] = time() - 1;

			// отправляем задачу consumer
			$this->doQueue($company_service_task);
		}
	}

	/**
	 * Выполнить задачу
	 *
	 * @param array $company_service_task
	 *
	 * @return void
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public function doWork(array $company_service_task):void {

		// проверяем задачу полученную из реббита
		if ($company_service_task["task_time_start"] + self::_NEED_WORK_INTERVAL < time()) {
			return;
		}
		unset($company_service_task["task_time_start"]);

		$company_service_task             = self::_makeStructCompanyServiceTask($company_service_task);
		$company_service_task->started_at = time();

		// проверяем актуальность выполняемой задачи
		try {

			$task = Gateway_Db_PivotCompanyService_CompanyServiceTask::getOne($company_service_task->task_id);
			if ($task->is_failed == 1) {
				return;
			}
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			return;
		}

		// инициализируем лог и добавляем туда запись о начале таска
		$log = new \BaseFrame\System\Log();
		$log->addText("Взял в работу задачу {$company_service_task->task_id} с типом {$company_service_task->type}");

		$company_row = Gateway_Db_PivotCompany_CompanyList::getOne($company_service_task->company_id);

		// пытаемся выполнить таск
		try {
			[$is_completed, $log] = Domain_Company_Entity_ServiceTask::run($company_service_task, $company_row, $log);
		} catch (\Throwable $t) {

			// обрабатываем исключение
			self::_handleThrowable($company_service_task, $company_row, $log, $t);
			return;
		}

		// добавляем логи в объект перед тем, как поместить запись в базу
		// либо если удачно завершили, либо если не добавляли еще лог о заблокированной компании (иначе получается огромная портянка от которой падают кроны)
		if ($is_completed || !inHtml($company_service_task->logs, "Компания {$company_row->company_id} заблокирована")) {
			$company_service_task->logs .= $log->close()->text;
		}
		$company_service_task->finished_at = time();

		// пишем логи
		Gateway_Db_PivotCompanyService_CompanyServiceTask::set($company_service_task->task_id, [
			"logs" => $company_service_task->logs,
		]);

		// если таск завершился успешно - перемещаем его в историю со статусом "успешно"
		if ($is_completed) {
			Domain_Company_Entity_ServiceTask::moveToHistory($company_service_task);
		}
	}

	/**
	 * формируем структуру сервисного таска
	 */
	protected static function _makeStructCompanyServiceTask(array $company_service_task):Struct_Db_PivotCompanyService_CompanyServiceTask {

		return new Struct_Db_PivotCompanyService_CompanyServiceTask(
			$company_service_task["task_id"],
			$company_service_task["is_failed"],
			$company_service_task["need_work"],
			$company_service_task["type"],
			$company_service_task["started_at"],
			$company_service_task["finished_at"],
			$company_service_task["created_at"],
			$company_service_task["updated_at"],
			$company_service_task["company_id"],
			$company_service_task["logs"],
			$company_service_task["data"]
		);
	}

	/**
	 * Обработать исключение
	 *
	 * @param Struct_Db_PivotCompanyService_CompanyServiceTask $company_service_task
	 * @param Struct_Db_PivotCompany_Company                   $company_row
	 * @param \BaseFrame\System\Log                            $log
	 * @param \Throwable                                        $t
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _handleThrowable(Struct_Db_PivotCompanyService_CompanyServiceTask $company_service_task, Struct_Db_PivotCompany_Company $company_row, \BaseFrame\System\Log $log, \Throwable $t):void {

		// лочим компанию
		Gateway_Db_PivotCompanyService_CompanyRegistry::set($company_row->domino_id, $company_service_task->company_id, ["is_busy" => 1]);

		// составляем сообщение. Сообщение делим на две части - на то, что может улететь в чат, и полный трейс ошибки для нас
		$message = "Завершилась с ошибкой сервисная задача {$company_service_task->task_id} в компании {$company_service_task->company_id} типа {$company_service_task->type}" . PHP_EOL;

		$error_message = "{$t->getMessage()}" . PHP_EOL . "{$t->getTraceAsString()}" . PHP_EOL;

		// добавляем запись об ошибке в лог
		$company_service_task->logs .= $log->addText($message . $error_message, \BaseFrame\System\Log::LOG_ERROR)->close()->text;

		Gateway_Db_PivotCompanyService_CompanyServiceTask::set($company_service_task->task_id, [
			"is_failed"   => 1,
			"finished_at" => time(),
			"started_at"  => $company_service_task->started_at,
			"logs"        => $company_service_task->logs,
		]);

		// если ловим любое исключение - кидаем алерт, что что то сломалось
		// пока ничего не делаем, если отвалился алерт
		try {
			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, $message);
		} catch (\Exception) {
		}

		// пишем лог в файл
		$exception_message = ExceptionUtils::makeMessage($t, HTTP_CODE_500);
		ExceptionUtils::writeExceptionToLogs($t, $exception_message);
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