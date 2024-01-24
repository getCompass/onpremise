<?php

namespace Compass\Conversation;

/**
 * Проверим что у пользователя нет активных приглашений
 */
class Domain_Invite_Action_CheckClearInvites {

	/**
	 * Проверим что у пользователя нет активных приглашений
	 *
	 **/
	public static function run(int $user_id, int $limit, int $offset):bool {

		// получим активные инвайты
		$invite_list = Domain_Invite_Entity_InvitesUser::getInvitesByUserId($user_id, $limit, $offset);

		// если инвайтов нет - значит уволен
		if (count($invite_list) < $limit && count($invite_list) == 0) {
			return true;
		}

		return false;
	}
}
