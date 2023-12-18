<?php

namespace Compass\Conversation;

/**
 * дефолтный класс для работы с инвайтами
 */
class Type_Invite_Default {

	// создаем запись с инвайтом в cloud_user_invite
	protected static function _createInviteInUserInvite(array $invite_row):void {

		Gateway_Db_CompanyConversation_UserInviteRel::insert(
			$invite_row["user_id"],
			$invite_row["sender_user_id"],
			$invite_row["invite_map"],
			$invite_row["created_at"],
			$invite_row["status"],
			$invite_row["group_conversation_map"]
		);
	}

	// обновляем запись с инвайтом в cloud_user_invite
	protected static function _updateInviteDataInUserInvite(int $user_id, string $invite_map, int $expected_status, int $new_status, int $updated_at):void {

		Gateway_Db_CompanyConversation_UserInviteRel::set($user_id, $invite_map, $expected_status, [
			"status"     => $new_status,
			"updated_at" => $updated_at,
		]);
	}
}