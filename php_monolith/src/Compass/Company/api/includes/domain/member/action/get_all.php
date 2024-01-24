<?php

namespace Compass\Company;

use CompassApp\Domain\Member\Entity\Member;

/**
 * Действие получения списка всех пользователей
 *
 * Class Domain_User_Action_Member_GetAll
 */
class Domain_Member_Action_GetAll {

	/**
	 * Выполяем action
	 */
	public static function do():array {

		$limit  = 1000;
		$offset = 0;

		$space_resident_user_id_list = [];
		$guest_user_id_list          = [];
		$system_bot_id_list          = [];

		do {

			[$user_list, $has_next] = Gateway_Db_CompanyData_MemberList::getAllActiveMemberWithPagination($offset, $limit);

			/** @var \CompassApp\Domain\Member\Struct\Main $user */
			foreach ($user_list as $user) {

				if (Type_User_Main::isHuman($user->npc_type)) {

					// если пользователь имеет роль полноценного участника пространства
					if (in_array($user->role, Member::SPACE_RESIDENT_ROLE_LIST)) {
						$space_resident_user_id_list[] = $user->user_id;
					}

					// если пользователь – гость
					if ($user->role === Member::ROLE_GUEST) {
						$guest_user_id_list[] = $user->user_id;
					}
				}

				if (Type_User_Main::isSystemBot($user->npc_type)) {
					$system_bot_id_list[] = $user->user_id;
				}
			}

			$offset += $limit;
		} while ($has_next);

		return [$space_resident_user_id_list, $guest_user_id_list, $system_bot_id_list];
	}
}
