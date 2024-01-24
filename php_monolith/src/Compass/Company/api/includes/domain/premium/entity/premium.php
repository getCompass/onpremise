<?php

namespace Compass\Company;

/**
 * Сущность премиума
 */
class Domain_Premium_Entity_Premium {

	// роли сотрудников компании для премиума
	public const MEMBER_ROLE_TITLE          = "member";
	public const OWNER_ROLE_TITLE           = "owner";
	public const ACCOUNT_DELETED_ROLE_TITLE = "account_deleted";

	/**
	 * получаем роль пользователя для премиума
	 */
	public static function getRoleTitle(\CompassApp\Domain\Member\Struct\Main $member):string {

		if ($member->role == \CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR) {
			return self::OWNER_ROLE_TITLE;
		}

		return self::MEMBER_ROLE_TITLE;
	}
}