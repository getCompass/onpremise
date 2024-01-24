<?php

namespace Compass\Company;

use \CompassApp\Domain\Member\Entity\Member;
use CompassApp\Domain\Member\Entity\Permission;
use CompassApp\Domain\Member\Exception\IsAdministrator;

/**
 * Action для проверки - доступно ли пользователю покинуть компанию
 */
class Domain_User_Action_Member_CheckIsAllowedLeaveCompany {

	/**
	 * выполняем
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws cs_CompanyUserIsOnlyOwner
	 * @throws \cs_UserIsNotMember
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @long
	 */
	public static function do(int $user_id, int $method_version = 1):void {

		// получаем участника компании
		$user_list = Gateway_Bus_CompanyCache::getShortMemberList([$user_id]);
		if (!isset($user_list[$user_id])) {
			throw new \cs_UserIsNotMember();
		}
		$user = $user_list[$user_id];

		// если текущий пользователь собственник, то выполняем дополнительные проверки
		try {
			Member::assertUserNotAdministrator($user->role);
		} catch (IsAdministrator) {

			$config               = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::MEMBER_COUNT);
			$company_member_count = $config["value"] ?? 0;
			$user_list            = Gateway_Db_CompanyData_MemberList::getListByRoles([Member::ROLE_MEMBER, Member::ROLE_ADMINISTRATOR], $company_member_count);

			$administrator_list            = [];
			$administrator_management_list = [];
			$member_list                   = [];
			foreach ($user_list as $user) {

				if ($user->role == Member::ROLE_MEMBER) {
					$member_list[] = $user;
				}

				if ($user->role == Member::ROLE_ADMINISTRATOR) {

					$administrator_list[] = $user;

					// если имеет право Управлять администраторами
					if (Permission::hasPermissionList($user->permissions, [Permission::ADMINISTRATOR_MANAGEMENT])) {
						$administrator_management_list[] = $user;
					}
				}
			}

			self::_checkAllowIfMethodV1($method_version, $administrator_list);

			self::_checkAllowIfMethodV2($method_version, $user_id, $member_list, $administrator_list, $administrator_management_list);
		}
	}

	/**
	 * проверяем, если первая версия метода
	 *
	 * @throws cs_CompanyUserIsOnlyOwner
	 */
	protected static function _checkAllowIfMethodV1(int $method_version, array $administrator_list):void {

		if ($method_version > 1) {
			return;
		}

		// если пользователь единственный собственник
		if (count($administrator_list) == 1) {
			throw new cs_CompanyUserIsOnlyOwner();
		}
	}

	/**
	 * проверяем, если вторая версия метода
	 *
	 * @throws cs_CompanyUserIsOnlyOwner
	 */
	protected static function _checkAllowIfMethodV2(int $method_version, int $leave_user_id, array $member_list, array $administrator_list, array $administrator_management_list):void {

		if ($method_version < 2) {
			return;
		}

		// если отсутствуют другие участники и администраторы в пространстве
		if (count($member_list) == 0 && count($administrator_list) == 1) {
			return;
		}

		// если только текущий пользователь имеет право Управлять администраторами
		if (count($administrator_management_list) == 1 && $administrator_management_list[0]->user_id == $leave_user_id) {
			throw new cs_CompanyUserIsOnlyOwner();
		}
	}
}
