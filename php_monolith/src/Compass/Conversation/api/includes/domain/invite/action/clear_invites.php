<?php

namespace Compass\Conversation;

/**
 * Деактивация всех приглашений
 */
class Domain_Invite_Action_ClearInvites {

	/**
	 * Удалить пользователя из диалогов
	 *
	 */
	public static function run(int $user_id, int $limit, int $offset):bool {

		// получим активные инвайты
		$invite_list = Domain_Invite_Entity_InvitesUser::getInvitesByUserId($user_id, $limit, $offset);

		// помечаем каждый инвайт неактивным
		foreach ($invite_list as $invite) {
			self::_doInactiveInvite($user_id, $invite);
		}

		return true;
	}

	/**
	 * Пометим инвайт не активным
	 *
	 */
	protected static function _doInactiveInvite(int $user_id, array $invite):void {

		Helper_Invites::setInactiveAllUserInviteToConversation(
			$user_id,
			$invite["group_conversation_map"],
			Type_Invite_Handler::INACTIVE_REASON_BLOCKED,
			true
		);

		// дополнительно еще проходимся по таблице с инвайтами
		Type_Invite_Single::updateInviteDataForUserInvite($user_id,
			$invite["invite_map"],
			(int) $invite["status"],
			Type_Invite_Handler::STATUS_INACTIVE,
			time()
		);
	}
}
