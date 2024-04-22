<?php

namespace Compass\Premise;

/**
 * Класс для действия обновления space_status пользователя
 */
class Domain_Premise_Action_UpdateSpaceStatus {

	/**
	 * Выполняем
	 *
	 * @param int $user_id
	 * @param int $space_status
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(int $user_id, int $space_status):void {

		$set = [
			"space_status" => $space_status,
			"updated_at"   => time(),
		];

		Gateway_Db_PremiseUser_UserList::set($user_id, $set);
	}
}