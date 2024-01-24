<?php

namespace Compass\Pivot;

/**
 * Ввести компанию в гибернацию
 */
class Domain_Company_Action_ServiceTask_Hibernate implements Domain_Company_Action_ServiceTask_Main {

	// через какое время начинать гибернацию
	public const _HIBERNATE_TIME = 20;

	/**
	 * Ввести компанию в гибернацию
	 *
	 * @param Struct_Db_PivotCompany_Company                $company_row
	 * @param Struct_Db_PivotCompanyService_CompanyRegistry $company_registry
	 * @param \BaseFrame\System\Log                         $log
	 *
	 * @return \BaseFrame\System\Log
	 * @throws Domain_Company_Exception_ConfigNotExist
	 * @throws Domain_System_Exception_IsNotAllowedServiceTask
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long
	 */
	public static function do(Struct_Db_PivotCompany_Company $company_row, Struct_Db_PivotCompanyService_CompanyRegistry $company_registry, \BaseFrame\System\Log $log, array $data = [], bool $is_force_hibernate = false):\BaseFrame\System\Log {

		if ($company_row->status == Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED || $company_registry->is_hibernated) {

			$log->addText("company already hibernated");
			return $log;
		}

		if ($company_row->status == Domain_Company_Entity_Company::COMPANY_STATUS_DELETED) {

			$log->addText("company deleted");
			return $log;
		}

		try {

			Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company_row->domino_id);
			Gateway_Db_PivotCompanyService_PortRegistry::getActiveByCompanyId($company_row->domino_id, $company_row->company_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new Domain_Company_Exception_ConfigNotExist("no domino port assigned to company");
		}

		// отправляем сокет запрос в компанию для гибернации
		try {

			Gateway_Socket_Company::hibernate(
				$company_row->company_id, $company_row->domino_id, Domain_Company_Entity_Company::getPrivateKey($company_row->extra), $is_force_hibernate
			);
		} catch (Gateway_Socket_Exception_CompanyHasActivity) {

			// компания проявила активность, ее не нужно сейчас гибернировать
			$log->addText("Компания проявила активность, ее не нужно вводить в сон");
			return $log;
		}

		$log_text = "В компании {$company_row->company_id} опубликован анонс о гибернации компании";
		$log->addText($log_text);

		if (!isTestServer()) {
			Gateway_Notice_Sender::sendGroup(NOTICE_CHANNEL_SERVICE, $log_text);
		}

		// помечаем в реестре и в списке компанию как компанию во сне
		Gateway_Db_PivotCompanyService_CompanyRegistry::set($company_row->domino_id, $company_row->company_id, [
			"is_hibernated" => 1,
		]);

		Gateway_Db_PivotCompany_CompanyList::set($company_row->company_id, [
			"status"     => Domain_Company_Entity_Company::COMPANY_STATUS_HIBERNATED,
			"updated_at" => time(),
		]);

		// когда нужно усыпить компанию
		$need_work_at = time() + self::_HIBERNATE_TIME;

		// удаляем компаиню из обзерва
		Gateway_Db_PivotCompany_CompanyTierObserve::delete($company_row->company_id);

		// добавляем задачу для остановки базы данных компании
		Domain_Company_Entity_ServiceTask::schedule(Domain_Company_Entity_ServiceTask::TASK_TYPE_HIBERNATION_STEP_TWO, $need_work_at, $company_row->company_id);
		return self::_writeLog($log);
	}

	/**
	 * Записать логи
	 *
	 * @param \BaseFrame\System\Log $log
	 *
	 * @return \BaseFrame\System\Log
	 */
	protected static function _writeLog(\BaseFrame\System\Log $log):\BaseFrame\System\Log {

		return $log->addText("Отправлен таск на гибернацию компанию через " . self::_HIBERNATE_TIME . " секунд");
	}
}