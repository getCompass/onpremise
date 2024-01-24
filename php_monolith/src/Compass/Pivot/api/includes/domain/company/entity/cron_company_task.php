<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для взаимодействия с задачами в компанию
 */
class Domain_Company_Entity_CronCompanyTask {

	public const STATUS_IN_PROGRESS = 0; // статус задачи - в процессе
	public const STATUS_DONE        = 1; // статус задачи - выполнена
	public const STATUS_CANCELED    = 2; // статус задачи - отменена (руками, запущена очистка, не важно)

	public const TYPE_EXIT = 1; // тип задачи увольнение

	// список статусов которые нужно выполнить
	public const TASK_STATUS_LIST_NEED_CHECK = [
		self::STATUS_IN_PROGRESS,
	];

	protected const _NEED_WORK_INTERVAL = 2;

	protected const _ITERATION_LIMIT_FOR_NOTICE = 100; // количество, после которых оповещаем, что превышено попыток итераций

	/**
	 * Выполним задачу
	 *
	 * @throws cs_CompanyNotExist
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function run(Struct_Db_PivotData_CompanyTaskQueue $task):void {

		$company     = Domain_Company_Entity_Company::get($task->company_id);
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		// если компания не активная, то нет смысла пытаться выполнить в ней таск
		if ($company->status != Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE) {

			self::_updateTaskByCompanyStatus($task, $company->status);
			return;
		}

		if ($task->iteration_count > self::_ITERATION_LIMIT_FOR_NOTICE) {

			$message = "Количество итераций выполнения таска превысило лимит. Требуется убедиться, что всё ок";
			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_KEY, $message);
		}

		// установим уровень итерации и обновим need_work - чтобы другие кроны не брали
		Gateway_Db_PivotData_CompanyTaskQueue::set($task->company_task_id, [
			"iteration_count" => $task->iteration_count + 1,
			"updated_at"      => time(),
			"need_work"       => time() + self::_NEED_WORK_INTERVAL,
		]);

		if ($task->type == self::TYPE_EXIT) {
			Gateway_Bus_CollectorAgent::init()->inc("row62");
		}

		try {
			$result = Gateway_Socket_Company::doTask(self::getTaskId($task->extra), $task->type, $company->company_id, $company->domino_id, $private_key);
		} catch (\cs_SocketRequestIsFailed|ReturnFatalException|ParseFatalException) {

			// установим количество ошибок
			Gateway_Db_PivotData_CompanyTaskQueue::set($task->company_task_id, [
				"error_count" => $task->error_count + 1,
				"updated_at"  => time(),
			]);
			return;
		}

		if ($result === true) {

			$done_at = time() - $task->created_at;
			self::setStatusDone($task, $done_at);
		}

		if ($task->type == self::TYPE_EXIT) {
			Gateway_Bus_CollectorAgent::init()->inc("row63");
		}
	}

	// обновляем таск в зависимости от статуса компании
	protected static function _updateTaskByCompanyStatus(Struct_Db_PivotData_CompanyTaskQueue $task, int $company_status):void {

		// если на тестовой сервере компания уснула (из-за быстрого засыпания - отменяем задачу). Не дает сломать сервер если форсированно усыпить компанию
		// на паблике такого возникнуть не должно - компания не может уснуть сразу после увольнения, так как в компании есть активность
		if (isTestServer() && $company_status == Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED) {

			Gateway_Db_PivotData_CompanyTaskQueue::set($task->company_task_id, [
				"status"     => self::STATUS_CANCELED,
				"updated_at" => time(),
			]);

			return;
		}

		// в зависимости от статуса
		switch ($company_status) {

			case Domain_Company_Entity_Company::COMPANY_STATUS_DELETED:

				Gateway_Db_PivotData_CompanyTaskQueue::set($task->company_task_id, [
					"status"     => self::STATUS_CANCELED,
					"updated_at" => time(),
				]);
				break;

			default:

				// инкрементим количество итераций и обновляем need_work
				Gateway_Db_PivotData_CompanyTaskQueue::set($task->company_task_id, [
					"iteration_count" => $task->iteration_count + 1,
					"updated_at"      => time(),
					"need_work"       => time() + self::_NEED_WORK_INTERVAL,
				]);
				break;
		}
	}

	/**
	 * Добавляем задачу
	 *
	 * @throws \queryException
	 */
	public static function add(int $company_id, int $type, int $task_id):Struct_Db_PivotData_CompanyTaskQueue {

		return Gateway_Db_PivotData_CompanyTaskQueue::insert(
			$company_id,
			$type,
			self::STATUS_IN_PROGRESS,
			0,
			0,
			self::setTaskId(self::initExtra(), $task_id)
		);
	}

	/**
	 * Ставим статус задача в процессе
	 *
	 * @throws \parseException
	 */
	public static function setStatusInProgress(Struct_Db_PivotData_CompanyTaskQueue $company_task):void {

		try {

			Gateway_Db_PivotData_CompanyTaskQueue::set($company_task->company_task_id, [
				"status"     => self::STATUS_IN_PROGRESS,
				"updated_at" => $company_task->updated_at,
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Task row not updated");
		}
	}

	/**
	 * Ставим статус задача выполнена
	 *
	 * @throws \parseException
	 */
	public static function setStatusDone(Struct_Db_PivotData_CompanyTaskQueue $company_task, int $done_at):void {

		try {

			Gateway_Db_PivotData_CompanyTaskQueue::set($company_task->company_task_id, [
				"status"     => self::STATUS_DONE,
				"done_at"    => $done_at,
				"updated_at" => $company_task->updated_at,
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Task row not updated");
		}
	}

	/**
	 * Ставим количество итераций
	 *
	 * @throws \parseException
	 */
	public static function setIterationCount(Struct_Db_PivotData_CompanyTaskQueue $company_task, int $iteration_count):void {

		try {

			Gateway_Db_PivotData_CompanyTaskQueue::set($company_task->company_task_id, [
				"iteration_count" => $iteration_count,
				"updated_at"      => $company_task->updated_at,
			]);
		} catch (cs_RowNotUpdated) {
			throw new ParseFatalException("Task row not updated");
		}
	}

	/**
	 * Получаем заявку по ее id
	 *
	 * @throws cs_ExitTaskNotExist
	 */
	public static function get(int $task_id):Struct_Db_PivotData_CompanyTaskQueue {

		try {
			$company_task = Gateway_Db_PivotData_CompanyTaskQueue::getOne($task_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_ExitTaskNotExist();
		}

		return $company_task;
	}

	// -------------------------------------------------------
	// EXTRA SCHEMA
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 1; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"exit_task_id" => 0,
		],
	];

	/**
	 * Создать новую структуру для extra
	 *
	 */
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * устанавливаем conversation_done
	 *
	 */
	public static function setTaskId(array $extra, int $task_id):array {

		$extra                          = self::_getExtra($extra);
		$extra["extra"]["exit_task_id"] = $task_id;

		return $extra;
	}

	/**
	 * получаем check_done_list
	 *
	 */
	public static function getTaskId(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["exit_task_id"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить актуальную структуру для extra
	 *
	 */
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}
}