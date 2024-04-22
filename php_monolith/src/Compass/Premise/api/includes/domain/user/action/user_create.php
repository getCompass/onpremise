<?php

namespace Compass\Premise;

/**
 * Класс для действия - создать пользователя в базе
 */
class Domain_User_Action_UserCreate {

	/**
	 * Выполняем
	 *
	 * @param int $user_id
	 * @param int $npc_type
	 * @param int $has_premise_permissions
	 * @param int $premise_permissions
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function do(int $user_id, int $npc_type, int $has_premise_permissions, int $premise_permissions):void {

		// добавляем нового пользователя
		$user = new Struct_Db_PremiseUser_User(
			$user_id,
			$npc_type,
			Domain_Premise_Entity_Space::NOT_EXIST_SPACE_STATUS,
			$has_premise_permissions,
			$premise_permissions,
			time(),
			0,
			"",
			"",
			"",
			[],
			[]
		);
		Gateway_Db_PremiseUser_UserList::insert($user);
	}
}