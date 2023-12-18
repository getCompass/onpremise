<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Action для проверки того что компания готова
 */
class Domain_System_Action_CheckReadyCompany {

	/**
	 * Проверяем готовность компании
	 */
	public static function do(Struct_Db_PivotCompany_Company $company):void {

		$company_row = Gateway_Db_PivotCompany_CompanyList::getOne($company->company_id);
		if ($company_row->status != Domain_Company_Entity_Company::COMPANY_STATUS_VACANT) {
			throw new ReturnFatalException("Company is not free");
		}
		if ($company_row->created_by_user_id != 0) {
			throw new ReturnFatalException("Company has owner");
		}

		// проверяем что компания есть в очереди свободных
		try {
			Gateway_Db_PivotCompanyService_CompanyInitRegistry::getOne($company->company_id);
		} catch (\cs_RowIsEmpty) {
			throw new ReturnFatalException("Company is not in free queue");
		}

		// проверяем что в ней нет пользователей
		$user_id_list = Gateway_Db_PivotCompany_CompanyUserList::getFullUserIdList($company->company_id);
		if (count($user_id_list) > 0) {
			throw new ReturnFatalException("Company has user");
		}
		if (Gateway_Db_PivotUser_CompanyLobbyList::existOneByCompany($company->company_id)) {
			throw new ReturnFatalException("User exist in company lobby");
		}

		if (Gateway_Db_PivotUser_CompanyList::existOneByCompany($company->company_id)) {
			throw new ReturnFatalException("User exist in company");
		}

		// сокет запрос закончится с ошибкой если что то не доконца удалилось
		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);
		Gateway_Socket_Company::checkReadyCompany($company->company_id, $company->domino_id, $private_key);
	}
}
