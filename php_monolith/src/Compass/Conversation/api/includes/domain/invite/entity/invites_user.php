<?php

namespace Compass\Conversation;

/**
 * Класс для работы с приглашениями пользователя
 */
class Domain_Invite_Entity_InvitesUser {

	/**
	 * Получим все приглашения пользователя
	 *
	 */
	public static function getInvitesByUserId(int $user_id, int $limit = 500, int $offset = 0, int $status = Type_Invite_Handler::STATUS_ACTIVE):array {

		return Type_Invite_Single::getInviteListByUserIdAndStatusInUserInvite($user_id, $status, $limit, $offset);
	}
}
