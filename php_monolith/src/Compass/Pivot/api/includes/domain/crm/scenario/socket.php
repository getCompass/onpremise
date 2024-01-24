<?php

namespace Compass\Pivot;

/**
 * Crm сценарии
 */
class Domain_Crm_Scenario_Socket {

	/**
	 * Сценарий получения списка всех компаний
	 *
	 * @param int  $limit
	 * @param int  $offset
	 * @param bool $only_active
	 *
	 * @return array
	 * @throws cs_CompanyIncorrectLimit
	 * @throws cs_CompanyIncorrectMinOrder
	 */
	public static function getCompanyList(int $limit, int $offset, bool $only_active):array {

		// проверяем
		Domain_Company_Entity_Validator::assertIncorrectLimit($limit);
		Domain_Company_Entity_Validator::assertIncorrectMinOrder($offset);

		//
		if ($only_active) {

			$company_list = Gateway_Db_PivotCompany_CompanyList::getActiveList($limit, $offset);
		} else {
			$company_list = Gateway_Db_PivotCompany_CompanyList::getFullList($limit, $offset);
		}

		$output = [];
		foreach ($company_list as $company) {

			$output[] = [
				"company_id"      => (int) $company->company_id,
				"name"            => (string) $company->name,
				"creator_user_id" => (int) $company->created_by_user_id,
				"member_count"    => (int) Domain_Company_Entity_Company::getMemberCount($company->extra),
				"created_at"      => (int) $company->created_at,
				"updated_at"      => (int) $company->updated_at,
			];
		}

		return $output;
	}
}
