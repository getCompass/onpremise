<?php

namespace Compass\Company;

/**
 * Базовый класс для добавления операторов в компанию
 */
class Domain_User_Action_AddOperator {

	/**
	 * выполняем действие добавления оператора в компанию
	 *
	 * @param int    $user_id
	 * @param int    $npc_type
	 * @param string $full_name
	 * @param string $avatar_file_key
	 *
	 * @return void
	 * @throws \busException
	 */
	public static function do(
		int    $user_id,
		int    $npc_type,
		string $full_name,
		string $avatar_file_key
	):array {

		$role        = \CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER;
		$permissions = \CompassApp\Domain\Member\Entity\Permission::DEFAULT;

		// инициализируем extra
		$extra = \CompassApp\Domain\Member\Entity\Extra::initExtra();
		Gateway_Db_CompanyData_MemberList::insertOrUpdate(
			$user_id, $role, $npc_type, $permissions, "", $full_name, "", $avatar_file_key, "", $extra
		);

		Gateway_Bus_CompanyCache::clearMemberCacheByUserId($user_id);

		return [$role, $permissions];
	}

}
