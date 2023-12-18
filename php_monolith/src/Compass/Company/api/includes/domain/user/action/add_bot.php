<?php

namespace Compass\Company;

/**
 * Базовый класс для добавления ботов в компанию
 */
class Domain_User_Action_AddBot {

	/**
	 * выполняем действие добавления бота в компанию
	 *
	 * @param int    $user_id
	 * @param int    $npc_type
	 * @param string $full_name
	 * @param string $avatar_file_key
	 *
	 * @return void
	 */
	public static function do(
		int    $user_id,
		int    $npc_type,
		string $mbti_type,
		string $full_name,
		string $avatar_file_key,
		string $comment
	):void {

		$role        = \CompassApp\Domain\Member\Entity\Member::ROLE_MEMBER;
		$permissions = \CompassApp\Domain\Member\Entity\Permission::DEFAULT;

		// инициализируем extra
		$extra = \CompassApp\Domain\Member\Entity\Extra::initExtra();
		Gateway_Db_CompanyData_MemberList::insertOrUpdate(
			$user_id, $role, $npc_type, $permissions, $mbti_type, $full_name, "", $avatar_file_key, $comment, $extra
		);
	}

}
