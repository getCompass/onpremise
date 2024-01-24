<?php

namespace Compass\Conversation;

/**
 * Обновить имена сингл диалогов где в качестве оппонента переданный пользователь
 */
class Domain_Conversation_Action_UpdateSingleNames {

	protected const _LIMIT_UPDATE = 2000;

	/**
	 * Обновить имена сингл диалогов где в качестве оппонента переданный пользователь
	 *
	 * @param int    $opponent_user_id
	 * @param string $name
	 *
	 * @return void
	 */
	public static function do(int $opponent_user_id, string $name):void {

		$offset = 0;

		do {

			// получаем пользователей для обновления
			$single_by_user_id_list = Gateway_Db_CompanyConversation_UserLeftMenu::getSingleListByUserId($opponent_user_id, self::_LIMIT_UPDATE, $offset);
			$update_user_id         = array_column($single_by_user_id_list, "opponent_user_id");

			// обновляем чаты по списку пользователей и их оппоненту
			Gateway_Db_CompanyConversation_UserLeftMenu::setNameByOpponentIdInSingle($update_user_id, $opponent_user_id, $name, self::_LIMIT_UPDATE);

			$offset += self::_LIMIT_UPDATE;
		} while (count($single_by_user_id_list) == self::_LIMIT_UPDATE);
	}
}
