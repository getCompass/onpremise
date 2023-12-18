<?php

namespace Compass\Pivot;

/**
 * Базовый класс для получения активных пользователей в компании
 */
class Domain_Company_Action_GetUserIdList {

	/**
	 * Получаем пользователей
	 *
	 * @param Struct_Db_PivotCompany_Company $company
	 *
	 * @return array
	 */
	public static function do(Struct_Db_PivotCompany_Company $company):array {

		$private_key = Domain_Company_Entity_Company::getPrivateKey($company->extra);

		// получим участников по ролям
		$user_role_list = Gateway_Socket_Company::getUserRoleList(
			[Domain_Company_Entity_User_Member::ROLE_MEMBER, Domain_Company_Entity_User_Member::ROLE_ADMINISTRATOR],
			$company->company_id, $company->domino_id, $private_key);

		$user_id_list = [];

		// если есть дефолтные пользователи - запишем
		if (isset($user_role_list[Domain_Company_Entity_User_Member::ROLE_MEMBER])) {
			$user_id_list = array_merge($user_id_list, $user_role_list[Domain_Company_Entity_User_Member::ROLE_MEMBER]);
		}

		// если есть владельцы пользователи - запишем
		if (isset($user_role_list[Domain_Company_Entity_User_Member::ROLE_ADMINISTRATOR])) {
			$user_id_list = array_merge($user_id_list, $user_role_list[Domain_Company_Entity_User_Member::ROLE_ADMINISTRATOR]);
		}

		return $user_id_list;
	}
}