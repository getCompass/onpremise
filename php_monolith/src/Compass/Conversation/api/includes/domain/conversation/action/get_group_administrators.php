<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Получить администраторов всех групп
 */
class Domain_Conversation_Action_GetGroupAdministrators {

	/**
	 * Выполняем
	 * @throws ParseFatalException
	 */
	public static function do(array $meta_row):array {

		$group_administrator_list  = Gateway_Db_CompanyData_MemberList::getByPermissionMask(Permission::GROUP_ADMINISTRATOR);
		$group_participant_id_list = array_keys($meta_row["users"]);

		return array_intersect($group_participant_id_list, array_column($group_administrator_list, "user_id"));
	}

}