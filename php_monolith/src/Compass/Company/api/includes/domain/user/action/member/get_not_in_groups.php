<?php

namespace Compass\Company;

/**
 * Action для получения учасников компании которые не состоят в группах
 */
class Domain_User_Action_Member_GetNotInGroups {

	/**
	 * @param int[] $groups
	 *
	 * @return \\CompassApp\Domain\Member\Struct\Main[]
	 */
	public static function do(array $groups, int $limit, bool $default_role = false):array {

		$permissions = 0;

		// собираем из групп маску. Применяем логическое ИЛИ
		$permissions = \CompassApp\Domain\Member\Entity\Permission::addPermissionListToMask($permissions, $groups);

		$member_list = Gateway_Db_CompanyData_MemberList::getListNotInPermissionMask($permissions, $limit);

		// если default_role = true, осталяем только пользователей с дефолтной ролью
		if ($default_role) {

			// фильтруем, оставляя только рядовых сотрудников
			$filtered_member_list = [];
			foreach ($member_list as $member) {

				if ($member->role == \CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER) {
					$filtered_member_list[] = $member;
				}
			}

			$member_list = $filtered_member_list;
		}

		return $member_list;
	}
}
