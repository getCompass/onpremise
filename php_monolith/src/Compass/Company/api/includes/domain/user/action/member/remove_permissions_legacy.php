<?php

namespace Compass\Company;

/**
 * Action для отключения прав пользователя
 */
class Domain_User_Action_Member_RemovePermissionsLegacy {

	/**
	 * Удалить пользователя из группы
	 *
	 * @throws \busException
	 * @throws cs_UserIsNotInGroup
	 * @throws \parseException
	 */
	public static function do(\CompassApp\Domain\Member\Struct\Short $member, array $permission_list):void {

		$permissions = $member->permissions;

		// убираем группу из маски групп пользователя
		$set["permissions"] = \CompassApp\Domain\Member\Entity\Permission::removePermissionListFromMask($permissions, $permission_list);

		// если забрали все права - убираем из администраторов
		// если мы в легаси методе этого не сделаем, пользователь навечно останется в админах, пока руководитель будет пользоваться старым клиентом
		if ($permissions === 0) {
			$set["role"] = \CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER;
		}

		Gateway_Db_CompanyData_MemberList::set($member->user_id, $set);

		// чистим кэш
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($member->user_id);

		// пушим событие о изменение permissions у пользователя
		Gateway_Event_Dispatcher::dispatch(Type_Event_Member_PermissionsChanged::create(
			$member->user_id, $member->role, $member->permissions, $set["role"] ?? $member->role, $set["permissions"]), true);
	}
}