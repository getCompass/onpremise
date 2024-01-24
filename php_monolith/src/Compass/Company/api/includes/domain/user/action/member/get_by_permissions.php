<?php

namespace Compass\Company;

/**
 * Action для получения учасников компании по их правам
 */
class Domain_User_Action_Member_GetByPermissions {

	public const OVER_LIMIT = 9999;

	public const IS_CONTAINS = 1; // права содержатся
	public const IS_EQUAL    = 2; // права полностью равны

	/**
	 *
	 * @param array $permission_list
	 * @param int   $search_type
	 * @param int   $limit
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(array $permission_list, int $search_type = self::IS_CONTAINS, int $limit = self::OVER_LIMIT):array {

		$permission_mask = 0;
		$permission_mask = \CompassApp\Domain\Member\Entity\Permission::addPermissionListToMask($permission_mask, $permission_list);

		return match ($search_type) {
			self::IS_CONTAINS => Gateway_Db_CompanyData_MemberList::getByPermissionMask($permission_mask, $limit),
			self::IS_EQUAL    => Gateway_Db_CompanyData_MemberList::getWithPermissions($permission_mask, $limit)
		};
	}
}
