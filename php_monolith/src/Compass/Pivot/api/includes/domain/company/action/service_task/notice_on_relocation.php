<?php

namespace Compass\Pivot;

/**
 * Первый этап задачи релокации.
 * Уведомляет пользователя о начале технических работ через N времени.
 */
class Domain_Company_Action_ServiceTask_NoticeOnRelocation implements Domain_Company_Action_ServiceTask_Main {

	/** @var int дефолтная задержка перед следующей задачей */
	protected const _NEXT_STEP_DEFAULT_DELAY = 60 * 10;

	/** @var int дополнительное время для анонса, чтобы перекрыть возможную задержку начала работ */
	protected const _ANNOUNCEMENT_TTL_DELTA = 10;

	/**
	 * Уведомляет пользователей о приближающихся технических работах.
	 * По сути просто просто триггерит анонс и ставит следующую задачу на исполнение.
	 *
	 * @param Struct_Db_PivotCompany_Company                $company_row
	 * @param Struct_Db_PivotCompanyService_CompanyRegistry $company_registry
	 * @param \BaseFrame\System\Log                         $log
	 * @param array                                         $data
	 *
	 * @return \BaseFrame\System\Log
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(Struct_Db_PivotCompany_Company $company_row, Struct_Db_PivotCompanyService_CompanyRegistry $company_registry, \BaseFrame\System\Log $log, array $data = []):\BaseFrame\System\Log {

		if ($company_row->status == Domain_Company_Entity_Company::COMPANY_STATUS_DELETED) {

			$log->addText("company deleted");
			return $log;
		}

		// сохраним аналитику по началу релокации
		Type_System_Analytic::save($company_row->company_id, $company_row->domino_id, Type_System_Analytic::TYPE_RELOCATION);

		$delay = static::_calculateDelay($data);

		static::_notifyCompany($company_row, $delay, $log);
		static::_scheduleNextStep($company_row, $delay, $log, $data);

		return $log;
	}

	/**
	 * Высчитывает задержку перед началом следующего этапа.
	 */
	protected static function _calculateDelay(array $data):int {

		return isset($data["delay_before_block_company"]) ? (int) $data["delay_before_block_company"] : static::_NEXT_STEP_DEFAULT_DELAY;
	}

	/**
	 * Делает запрос в компанию, уведомляя о начале технических работ.
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 * @param int                            $delay
	 * @param \BaseFrame\System\Log          $log
	 *
	 * @throws Gateway_Socket_Exception_CompanyIsNotServed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Gateway\RowNotFoundException
	 * @throws \cs_SocketRequestIsFailed
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _notifyCompany(Struct_Db_PivotCompany_Company $company, int $delay, \BaseFrame\System\Log $log):void {

		$log->addText("уведомляю пользователей о запланированных технических работах");

		// считаем длительность анонса
		$announcement_duration = $delay + static::_ANNOUNCEMENT_TTL_DELTA;

		// приватный ключ для компании
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		try {

			// дергаем домино
			Gateway_Socket_Company::onTechnicalWorksPlanned($company->company_id, $announcement_duration, $company->domino_id, $private_key);
		} catch (\Exception $e) {

			$log->addText("ошибка — не удалось опубликовать анонс: " . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * @param Struct_Db_PivotCompany_Company $company
	 * @param int                            $delay
	 * @param \BaseFrame\System\Log          $log
	 * @param array                          $data
	 *
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws \queryException
	 */
	protected static function _scheduleNextStep(Struct_Db_PivotCompany_Company $company, int $delay, \BaseFrame\System\Log $log, array $data):void {

		$log->addText("ставлю следующий этап задачи релокации — блокировка компании");

		try {

			// добавляем задачу для остановки базы данных компании
			$task_id = Domain_Company_Entity_ServiceTask::schedule(
				Domain_Company_Entity_ServiceTask::TASK_TYPE_RELOCATION_STEP_TWO, time() + $delay, $company->company_id, $data
			);

			// помечаем в tier_observe, что завершился процесс переезда
			$company_tier = Gateway_Db_PivotCompany_CompanyTierObserve::get($company->company_id);
			Domain_Company_Entity_Tier::setRelocationNextStep($company_tier, $task_id);
		} catch (\Exception $e) {

			$log->addText("не удалось добавить задачу на блокировку компании при релокации: " . $e->getMessage());
			throw $e;
		}
	}
}
