<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Action для очистки компании
 */
class Domain_System_Action_PurgeCompany {

	/**
	 * Очищаем компанию.
	 * Если передан флаг пересоздания, то следующая добавленная компания будет создана в слоте очищенной.
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	public static function do(Struct_Db_PivotCompany_Company $company):void {

		// ставим busy чтоб не забрал крон
		Gateway_Db_PivotCompanyService_CompanyRegistry::set($company->domino_id, $company->company_id, ["is_busy" => 1]);

		// очистка компании идет в два этапа
		// сначала стопаем работу и исключаем всех пользователей
		self::_purgeInitiate($company);

		// затем уже инициируем очистку баз и все такое
		self::_purge($company);
	}

	/**
	 * Первый этап процесса очистки компании.
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	protected static function _purgeInitiate(Struct_Db_PivotCompany_Company $company):void {

		// помечаем компанию как недоступную,
		// чтобы никто не попал в нее, пока она чистится
		Gateway_Db_PivotCompany_CompanyList::set($company->company_id, [
			"status"     => Domain_Company_Entity_Company::COMPANY_STATUS_INVALID,
			"is_deleted" => 0,
			"deleted_at" => 0,
			"updated_at" => time(),
		]);

		try {

			$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

			// выполняем запрос на разлогин пользователей
			// поскольку у нас компания помечена неактивной, то залогиниться в процессе туда не выйдет
			Gateway_Socket_Company::purgeCompanyStepOne($company->company_id, $company->domino_id, $private_key);
		} catch (ReturnFatalException $e) {

			Type_System_Admin::log("purge-company", "не удалось завершить первый этап очистки для компании $company->company_id: {$e->getMessage()}");
		}
	}

	/**
	 * Второй этап процесса очистки компании.
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_CompanyIncorrectCompanyId
	 * @throws \parseException
	 */
	protected static function _purge(Struct_Db_PivotCompany_Company $company):void {

		Domain_Company_Entity_User_Member::deleteByCompany($company->company_id);

		try {

			$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

			Gateway_Socket_Company::purgeCompanyStepTwo($company->company_id, $company->domino_id, $private_key);
		} catch (ReturnFatalException $e) {
			Type_System_Admin::log("purge-company", "не удалось завершить второй этап очистки для компании $company->company_id: {$e->getMessage()}");
		}

		// определяем время создания компании в очереди,
		// при необходимости его нужно подменить, чтобы в очереди она оказалось первой
		$created_at = time();
		$extra      = Domain_Company_Entity_Company::setMemberCount($company->extra, 0);
		$extra      = Domain_Company_Entity_Company::setGuestCount($extra, 0);

		// обнуляем задачи компании - так как компания почистится
		Gateway_Db_PivotData_CompanyTaskQueue::setCompanyPurge($company->company_id);
		Gateway_Db_PivotCompanyService_CompanyServiceTask::setCompanyPurge($company->company_id);

		// не снимаем is_busy, чтобы не забрал крон
		Gateway_Db_PivotCompanyService_CompanyRegistry::set($company->domino_id, $company->company_id, ["is_hibernated" => 0]);

		$company->status = Domain_Company_Entity_Company::COMPANY_STATUS_VACANT;

		// обнуляем компанию
		Gateway_Db_PivotCompany_CompanyList::set($company->company_id, [
			"status"             => $company->status,
			"updated_at"         => time(),
			"extra"              => $extra,
			"created_by_user_id" => 0,
			"created_at"         => $created_at,
		]);

		Gateway_Db_PivotCompanyService_CompanyInitRegistry::set($company->company_id, ["is_vacant" => 1]);

		// получим доминошу и порт
		try {

			$domino_registry_row = Gateway_Db_PivotCompanyService_DominoRegistry::getOne($company->domino_id);
			$port_registry_row   = Gateway_Db_PivotCompanyService_PortRegistry::getActiveByCompanyId($company->domino_id, $company->company_id);
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {

			// возможно это была пустая компания и можно опустить
			return;
		}

		Domain_Domino_Action_Config_UpdateMysql::do($company, $domino_registry_row, $port_registry_row, true);

		// ожидаем синка конфига
		sleep(1);

		// снимаем is_busy
		Gateway_Db_PivotCompanyService_CompanyRegistry::set($company->domino_id, $company->company_id, ["is_busy" => 0]);
	}
}
