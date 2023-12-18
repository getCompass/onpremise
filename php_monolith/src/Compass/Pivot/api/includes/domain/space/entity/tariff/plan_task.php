<?php

namespace Compass\Pivot;

/**
 * Класс сущности "таск тарифного плана"
 */
class Domain_Space_Entity_Tariff_PlanTask {

	public const TASK_TYPE_PAYMENT_NOTIFY     = 10; // тип задачи - уведомить о необходимости оплаты
	public const TASK_TYPE_POSTPAYMENT_NOTIFY = 20; // тип задачи - уведомить о постоплатном периоде
	public const TASK_TYPE_BLOCK_NOTIFY       = 30; // тип задачи - уведомить о блокировке пространства

	// разрешенные типы тасков
	public const ALLOWED_TASK_TYPES = [
		self::TASK_TYPE_PAYMENT_NOTIFY,
		self::TASK_TYPE_POSTPAYMENT_NOTIFY,
		self::TASK_TYPE_BLOCK_NOTIFY,
	];

	// статус таска - успешно выполнен
	public const TASK_STATUS_SUCCESS = 1;

	// статус таска - выполнился с ошибкой
	public const TASK_STATUS_ERROR = 9;

	/**
	 * Добавить задачу по тарифу для пространства
	 *
	 * @param int $task_type
	 * @param int $need_work
	 * @param int $company_id
	 *
	 * @return int
	 * @throws Domain_Space_Exception_Tariff_IsNotAllowedObserverTask
	 * @throws \queryException
	 */
	public static function schedule(int $task_type, int $need_work, int $company_id):int {

		// проверяем, что передали разрешенный тип задачи
		if (!in_array($task_type, self::ALLOWED_TASK_TYPES)) {
			throw new Domain_Space_Exception_Tariff_IsNotAllowedObserverTask("tried to add not allowed task");
		}

		// добавляем задачу в базу
		$tariff_plan_task = new Struct_Db_PivotCompany_TariffPlanTask(
			0,
			$company_id,
			$task_type,
			self::TASK_STATUS_SUCCESS,
			$need_work,
			time(),
			0,
			"",
			[]
		);

		// добавляем задачу в базу
		return Gateway_Db_PivotCompany_TariffPlanTask::insert($tariff_plan_task);
	}

	/**
	 * Выполняем прилетевшую задачу
	 *
	 * @param Struct_Db_PivotCompany_TariffPlanTask $tariff_plan_task
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 */
	public static function exec(Struct_Db_PivotCompany_TariffPlanTask $tariff_plan_task):void {

		$log = (new \BaseFrame\System\Log())->addText("Начинаю выполнять задачу типа $tariff_plan_task->type для пространства $tariff_plan_task->space_id");

		$company = Gateway_Db_PivotCompany_CompanyList::getOne($tariff_plan_task->space_id);

		// если пространство неактивное - значит оно умудрилось изменить статус, пока летела задача из обсервера
		// завершаем выполнение, обсервер потом снова закинет задачу, когда пространство проснется. Либо самоуничтожится, если пространство удалили
		if ($company->status !== Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {

			$log = $log->addText("Пространство неактивно, имеет статус $company->status, завершаю выполнение");

			// сохраняем результат
			self::_storeResult($tariff_plan_task, true, $log);
			return;
		}

		try {

			[$is_success, $log] = self::_execTask($tariff_plan_task, $company, $log);

			// сохраняем результат
			self::_storeResult($tariff_plan_task, $is_success, $log);
		} catch (\Throwable $t) {

			// ловим просто все, что только можно, только чтобы крон не упал
			$log      = $log->addText($t->getMessage(), \BaseFrame\System\Log::LOG_ERROR);
			$log_text = $log->close()->text;

			Gateway_Db_PivotCompany_TariffPlanTask::set($tariff_plan_task->space_id, $tariff_plan_task->id, [
				"status" => self::TASK_STATUS_ERROR,
				"logs"   => $log_text,
			]);

			return;
		}
	}

	/**
	 * Сохраняем результат в базе
	 *
	 * @param Struct_Db_PivotCompany_TariffPlanTask $tariff_plan_task
	 * @param bool                                  $is_success
	 * @param \BaseFrame\System\Log                 $log
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	protected static function _storeResult(Struct_Db_PivotCompany_TariffPlanTask $tariff_plan_task, bool $is_success, \BaseFrame\System\Log $log):void {

		// если все хорошо, завершаем выполнение, пишем логи и перемещаем в историю
		if ($is_success) {

			$tariff_plan_task->logs = $log->close()->text;
			self::_moveToHistory($tariff_plan_task);
			return;
		}

		$log_text = $log->close()->text;
		Gateway_Db_PivotCompany_TariffPlanTask::set($tariff_plan_task->space_id, $tariff_plan_task->id, [
			"status" => self::TASK_STATUS_ERROR,
			"logs"   => $log_text,
		]);
	}

	/**
	 * Переместить задачу в историю
	 *
	 * @param Struct_Db_PivotCompany_TariffPlanTask $tariff_plan_task
	 *
	 * @return void
	 * @throws \queryException
	 */
	protected static function _moveToHistory(Struct_Db_PivotCompany_TariffPlanTask $tariff_plan_task):void {

		$tariff_plan_task_history = new Struct_Db_PivotCompany_TariffPlanTaskHistory(
			$tariff_plan_task->id,
			$tariff_plan_task->space_id,
			$tariff_plan_task->type,
			$tariff_plan_task->status,
			time() - $tariff_plan_task->need_work,
			time(),
			$tariff_plan_task->logs,
			[]
		);

		Gateway_Db_PivotCompany_TariffPlanTaskHistory::insert($tariff_plan_task_history);
		Gateway_Db_PivotCompany_TariffPlanTask::delete($tariff_plan_task->space_id, $tariff_plan_task->id);
	}

	/**
	 * Запускает выполнение задачи.
	 *
	 * @param Struct_Db_PivotCompany_TariffPlanTask $tariff_plan_task
	 * @param Struct_Db_PivotCompany_Company        $company
	 * @param \BaseFrame\System\Log                 $log
	 *
	 * @return array
	 * @throws Domain_Space_Exception_Tariff_IsNotAllowedObserverTask
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _execTask(Struct_Db_PivotCompany_TariffPlanTask $tariff_plan_task, Struct_Db_PivotCompany_Company $company,
							\BaseFrame\System\Log                 $log):array {

		// выполняем таск
		return match ($tariff_plan_task->type) {
			self::TASK_TYPE_PAYMENT_NOTIFY     => Domain_Space_Action_Tariff_PaymentNotify::do($company, $log),
			self::TASK_TYPE_POSTPAYMENT_NOTIFY => Domain_Space_Action_Tariff_PostPaymentNotify::do($company, $log),
			self::TASK_TYPE_BLOCK_NOTIFY       => Domain_Space_Action_Tariff_BlockNotify::do($company, $log),
			default                            => throw new Domain_Space_Exception_Tariff_IsNotAllowedObserverTask("invalid  task type"),
		};
	}
}