<?php declare(strict_types = 1);

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use cs_RowIsEmpty;

/**
 * Класс для сценариев api домена company
 */
class Domain_Company_Scenario_Api {

	/**
	 * Получаем список команд
	 *
	 * @throws Domain_User_Exception_UserHaveNotPermissions
	 * @throws ParseFatalException
	 * @throws cs_RowIsEmpty
	 */
	public static function getList(int $user_id):array {

		// получаем права пользователя
		$user = Gateway_Db_PremiseUser_UserList::getOne($user_id);

		// проверяем, что имеет права
		Domain_User_Entity_Permissions::assertIfNotHavePermissions((bool) $user->has_premise_permissions);

		$company_list = Gateway_Db_PivotCompany_CompanyList::getByStatus(Domain_Company_Entity_Company::COMPANY_STATUS_ACTIVE);

		$user_company_list = [];
		foreach ($company_list as $company) {
			$user_company_list[] = Struct_User_Company::createFromCompanyStruct($company, Struct_User_Company::ACTIVE_STATUS, 0);
		}

		return Apiv2_Format::userCompanyList($user_company_list);
	}
}