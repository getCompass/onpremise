<?php

namespace Compass\Company;

/**
 * действие получения списка всех программистов
 */
class Domain_Member_Action_GetAllDevelopers {

	/**
	 * выполяем action
	 */
	public static function do():array {

		$member_list            = Domain_User_Action_Member_GetByPermissions::do([\CompassApp\Domain\Member\Entity\Permission::BOT_MANAGEMENT]);
		$developer_user_id_list = [];
		foreach ($member_list as $member) {
			$developer_user_id_list[] = $member->user_id;
		}

		return $developer_user_id_list;
	}
}