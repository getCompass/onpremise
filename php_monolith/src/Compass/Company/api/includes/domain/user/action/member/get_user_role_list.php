<?php

namespace Compass\Company;

/**
 * Class Domain_User_Action_Member_GetUserRoleList
 */
class Domain_User_Action_Member_GetUserRoleList {

	/**
	 * Получаем пользователей по заданным ролям
	 */
	public static function do(array $roles):array {

		$owner_pretenders = [];

		// получаем общее количество участников в компании
		$config               = Domain_Company_Entity_Config::get(Domain_Company_Entity_Config::MEMBER_COUNT);
		$company_member_count = $config["value"];

		// возвращаем список пользователей с указанными ролями
		$user_roles = Gateway_Db_CompanyData_MemberList::getListByRoles($roles, $company_member_count);

		return array_replace($user_roles, $owner_pretenders);
	}
}