<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Permission;

/**
 * Action для установки прав в карточке пользователя
 */
class Domain_User_Action_Member_SetPermissionsProfileCard {

	/**
	 * Изменить роль пользователю
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 * @param array                                 $enabled_permission_list
	 * @param array                                 $disabled_permission_list
	 *
	 * @return int
	 * @throws \parseException
	 * @throws \busException
	 */
	public static function do(\CompassApp\Domain\Member\Struct\Main $member, array $enabled_permission_list = [], array $disabled_permission_list = []):int {

		$permissions = self::_setPermissions($member, $enabled_permission_list, $disabled_permission_list);

		// обновляем права участника, если они реально изменились
		if ($member->permissions != $permissions) {
			self::_updateRow($member->user_id, $permissions);
		}

		return $permissions;
	}

	/**
	 * Установить права в маске
	 *
	 * @param \CompassApp\Domain\Member\Struct\Main $member
	 * @param array                                 $enabled_permission_list
	 * @param array                                 $disabled_permission_list
	 *
	 * @return int
	 */
	protected static function _setPermissions(\CompassApp\Domain\Member\Struct\Main $member, array $enabled_permission_list, array $disabled_permission_list):int {

		$permissions = Permission::addPermissionListToMask($member->permissions, $enabled_permission_list);
		return Permission::removePermissionListFromMask($permissions, $disabled_permission_list);
	}

	/**
	 * Обновить запись в базе
	 *
	 * @param int $member_id
	 * @param int $permissions
	 *
	 * @return void
	 * @throws \busException
	 * @throws \parseException
	 */
	protected static function _updateRow(int $member_id, int $permissions):void {

		Gateway_Db_CompanyData_MemberList::set($member_id, [
			"permissions" => $permissions,
		]);
	}
}