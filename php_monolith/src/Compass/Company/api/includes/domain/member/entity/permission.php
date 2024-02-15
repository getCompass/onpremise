<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Permission;

/**
 * Класс для валидации разрешений пользователя
 */
class Domain_Member_Entity_Permission {

	/**
	 * Проверяем разрешение у пространства для пользователей
	 *
	 * @param int    $user_id
	 * @param int    $method_version
	 * @param string $permission_key
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \BaseFrame\Exception\Request\ControllerMethodNotFoundException
	 * @throws \CompassApp\Domain\Member\Exception\ActionNotAllowed
	 * @throws \cs_RowIsEmpty
	 * @throws \queryException
	 */
	public static function checkSpace(int $user_id, int $method_version, string $permission_key):void {

		if ($method_version < 2) {
			return;
		}

		$member = Domain_User_Action_Member_GetShort::do($user_id);

		try {

			// проверяем роль пользователя
			\CompassApp\Domain\Member\Entity\Member::assertUserAdministrator($member->role);
		} catch (\CompassApp\Domain\Member\Exception\IsNotAdministrator) {

			$member_permission = Domain_Company_Entity_Config::getValue($permission_key);
			if ($member_permission["value"] === 0) {
				throw new \CompassApp\Domain\Member\Exception\ActionNotAllowed("Action not allowed");
			}
		}
	}

	/**
	 * Проверяем разрешение у пользователя
	 *
	 * @param int $user_id
	 * @param int $member_id
	 * @param int $permission_key
	 *
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws \CompassApp\Domain\Member\Exception\ActionRestrictForUser
	 * @throws \cs_RowIsEmpty
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function checkUser(int $user_id, int $member_id, int $permission_key):void {

		$user_info = Domain_User_Action_Member_GetShort::do($user_id);

		if ($user_id !== $member_id) {
			Permission::assertCanEditMemberProfile($user_info->role, $user_info->permissions);
		}

		if (Permission::hasOneFromPermissionList($user_info->permissions, [
			Permission::MEMBER_PROFILE_EDIT,
			Permission::ADMINISTRATOR_MANAGEMENT,
			Permission::SPACE_SETTINGS,
		])) {
			return;
		}

		if (($user_id === $member_id) && Permission::hasPermission($user_info->permissions, $permission_key)) {
			throw new \CompassApp\Domain\Member\Exception\ActionRestrictForUser("Action restrict for user");
		}
	}
}
