<?php

namespace Compass\Company;

/**
 * Action для установки роли kicked
 */
class Domain_User_Action_Member_SetKickedRole {

	/**
	 *
	 * @throws \apiAccessException
	 * @throws \busException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $member_id, int $role, int $left_at, bool $need_add_user_lobby, string $reason):void {

		// удаляем пользователя на pivot
		Gateway_Socket_Pivot::kickMember($member_id, $role, $need_add_user_lobby, $reason);

		// лишаем всех прав пользователя
		$permissions = \CompassApp\Domain\Member\Entity\Permission::DEFAULT;
		$role        = \CompassApp\Domain\Member\Entity\Member::ROLE_LEFT;

		Gateway_Db_CompanyData_MemberList::beginTransaction();

		try {

			$member = Gateway_Db_CompanyData_MemberList::getForUpdate($member_id);

			// устанавливаем новую роль, маску и extra
			Gateway_Db_CompanyData_MemberList::set($member_id, [
				"permissions" => $permissions,
				"role"        => $role,
				"updated_at"  => time(),
				"left_at"     => $left_at,
			]);
		} catch (\Exception $e) {

			Gateway_Db_CompanyData_MemberList::rollback();
			throw $e;
		}

		Gateway_Db_CompanyData_MemberList::commitTransaction();

		// подчищаем кэш
		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($member_id);
		Gateway_Bus_Company_Rating::disableUserInRating($member_id);

		// пушим событие о изменение permissions у пользователя
		Gateway_Event_Dispatcher::dispatch(Type_Event_Member_PermissionsChanged::create(
			$member->user_id, $member->role, $member->permissions, $role, $permissions), true);
	}
}