<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * Класс сущности "сервисный таск компании"
 */
class Domain_Company_Entity_ServiceTask {

	public const TASK_TYPE_HIBERNATION_STEP_ONE  = 10; // тип задачи - гибернация
	public const TASK_TYPE_HIBERNATION_STEP_TWO  = 11; // тип задачи - второй этап гибернации
	public const TASK_TYPE_AWAKE                 = 20; // тип задачи - пробуждение
	public const TASK_TYPE_RELOCATION_STEP_ONE   = 40; // тип задачи - релокация
	public const TASK_TYPE_RELOCATION_STEP_TWO   = 41; // тип задачи - релокация
	public const TASK_TYPE_RELOCATION_STEP_THREE = 42; // тип задачи - релокация
	public const TASK_TYPE_DELETE_COMPANY        = 51; // тип задачи - удаление компании

	// разрешенные типы сервисных тасков
	protected const _ALLOWED_TASK_TYPES = [
		self::TASK_TYPE_HIBERNATION_STEP_ONE,
		self::TASK_TYPE_HIBERNATION_STEP_TWO,
		self::TASK_TYPE_AWAKE,
		self::TASK_TYPE_RELOCATION_STEP_ONE,
		self::TASK_TYPE_RELOCATION_STEP_TWO,
		self::TASK_TYPE_RELOCATION_STEP_THREE,
		self::TASK_TYPE_DELETE_COMPANY,
	];

	/**
	 * Добавить сервисную задачу для компании
	 *
	 * @param int   $task_type
	 * @param int   $need_work
	 * @param int   $company_id
	 * @param array $data
	 *
	 * @return void
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws \queryException
	 */
	public static function schedule(int $task_type, int $need_work, int $company_id, array $data = []):int {

		// проверяем, что передали разрешенный тип задачи
		if (!in_array($task_type, self::_ALLOWED_TASK_TYPES)) {
			throw new Domain_System_Exception_IsNotAllowedServiceTask("tried to add not allowed service task");
		}

		// добавляем задачу в базу
		return Gateway_Db_PivotCompanyService_CompanyServiceTask::insert($task_type, $need_work, $company_id, $data);
	}

	/**
	 * Выполняем задачу
	 *
	 * @param Struct_Db_PivotCompanyService_CompanyServiceTask $company_service_task
	 * @param Struct_Db_PivotCompany_Company                   $company_row
	 * @param \BaseFrame\System\Log                            $log
	 *
	 * @return array
	 * @throws Domain_Company_Exception_ConfigNotExist
	 * @throws Domain_Company_Exception_IsNotHibernated
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_CompanyNotBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public static function run(Struct_Db_PivotCompanyService_CompanyServiceTask $company_service_task, Struct_Db_PivotCompany_Company $company_row, \BaseFrame\System\Log $log):array {

		// если компания вакантная - ничего не делаем с ней
		if ($company_row->status === Domain_Company_Entity_Company::COMPANY_STATUS_VACANT) {

			$log->addText("Компания {$company_row->company_id} вакантная, и не может подвергаться сервисным задачам");
			return [true, $log];
		}

		try {

			// пытаемся занять компанию для сервисной задачи
			$log = static::_execTask($company_row, $company_service_task, $log);
		} catch (Domain_Company_Exception_IsBusy) {

			$log->addText("Компания {$company_row->company_id} заблокирована, идем на следующую итерацию");
			return [false, $log];
		}

		return [true, $log];
	}

	/**
	 * Форсит выполнение задачи для указанной компании.
	 * Только для бэкдоров.
	 *
	 * @throws Domain_Company_Exception_ConfigNotExist
	 * @throws Domain_Company_Exception_IsNotHibernated
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_CompanyNotBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws Domain_Company_Exception_IsBusy
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function forceRun(Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompanyService_CompanyServiceTask $task):void {

		if (!isTestServer()) {
			throw new \BaseFrame\Exception\Domain\ReturnFatalException("method is not allowed on current environment level");
		}

		// пытаемся выполнить задачку
		static::_execTask($company, $task, new \BaseFrame\System\Log());

		// переносим таск в историю
		static::moveToHistory($task);
	}

	/**
	 * Переместить задачу в историю
	 *
	 * @param Struct_Db_PivotCompanyService_CompanyServiceTask $company_service_task
	 *
	 * @return void
	 * @throws \queryException
	 */
	public static function moveToHistory(Struct_Db_PivotCompanyService_CompanyServiceTask $company_service_task):void {

		Gateway_Db_PivotCompanyService_CompanyServiceTaskHistory::insert($company_service_task);
		Gateway_Db_PivotCompanyService_CompanyServiceTask::delete($company_service_task->task_id);
	}

	# region protected

	/**
	 * Запускает выполнение задачи.
	 *
	 * @throws Domain_Company_Exception_ConfigNotExist
	 * @throws Domain_Company_Exception_IsBusy
	 * @throws Domain_Domino_Exception_CompanyInOnMaintenance
	 * @throws Domain_Domino_Exception_CompanyIsBound
	 * @throws Domain_Domino_Exception_CompanyNotBound
	 * @throws Domain_Domino_Exception_PortBindingIsNotAllowed
	 * @throws Domain_Domino_Exception_VoidPortsExhausted
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\SocketException
	 * @throws \busException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _execTask(Struct_Db_PivotCompany_Company $company, Struct_Db_PivotCompanyService_CompanyServiceTask $company_service_task, \BaseFrame\System\Log $log):\BaseFrame\System\Log {

		// пытаемся занять компанию для сервисной задачи
		$company_registry = static::_getLockedRegistryItem($company);

		// данные для выполнения задачи
		// просто чтобы влезало горизонтально
		$data = $company_service_task->data;

		// выполняем таск
		$log = match ($company_service_task->type) {
			self::TASK_TYPE_HIBERNATION_STEP_ONE  => Domain_Company_Action_ServiceTask_Hibernate::do($company, $company_registry, $log),
			self::TASK_TYPE_HIBERNATION_STEP_TWO  => Domain_Company_Action_ServiceTask_StopDatabase::do($company, $company_registry, $log),
			self::TASK_TYPE_AWAKE                 => Domain_Company_Action_ServiceTask_Awake::do($company, $company_registry, $log),
			self::TASK_TYPE_RELOCATION_STEP_ONE   => Domain_Company_Action_ServiceTask_NoticeOnRelocation::do($company, $company_registry, $log, $data),
			self::TASK_TYPE_RELOCATION_STEP_TWO   => Domain_Company_Action_ServiceTask_BlockOnRelocation::do($company, $company_registry, $log, $data),
			self::TASK_TYPE_RELOCATION_STEP_THREE => Domain_Company_Action_ServiceTask_RelocateCompanyData::do($company, $company_registry, $log, $data),
			self::TASK_TYPE_DELETE_COMPANY        => Domain_Company_Action_ServiceTask_DeleteCompany::do($company, $company_registry, $log, $data),
			default                               => throw new Domain_System_Exception_IsNotAllowedServiceTask("invalid company system task type"),
		};

		// разблокируем компанию
		Gateway_Db_PivotCompanyService_CompanyRegistry::set($company->domino_id, $company_service_task->company_id, ["is_busy" => 0]);
		return $log;
	}

	/**
	 * Возвращает запись из реестра компаний с блокировкой.
	 * Если запись уже заблокирована, кидает соответствующее исключение.
	 *
	 * @throws Domain_Company_Exception_IsBusy
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \returnException
	 */
	protected static function _getLockedRegistryItem(Struct_Db_PivotCompany_Company $company):Struct_Db_PivotCompanyService_CompanyRegistry {

		/** начало транзакции */
		Gateway_Db_PivotCompanyService_Main::beginTransaction();

		try {
			$company_registry = Gateway_Db_PivotCompanyService_CompanyRegistry::getForUpdate($company->domino_id, $company->company_id);
		} catch (RowNotFoundException $e) {

			Gateway_Db_PivotCompanyService_Main::rollback();
			throw $e;
		}


		// если компания занята - отправляем на следующую итерацию
		if ($company_registry->is_busy) {

			Gateway_Db_PivotCompanyService_Main::rollback();
			throw new Domain_Company_Exception_IsBusy("company is locked for service task");
		}

		$company_registry->is_busy = 1;

		// блокируем компанию
		Gateway_Db_PivotCompanyService_CompanyRegistry::set($company->domino_id, $company->company_id, ["is_busy" => $company_registry->is_busy]);
		Gateway_Db_PivotCompanyService_Main::commitTransaction();
		/** конец транзакции транзакции */

		return $company_registry;
	}

	# endregion protected
}