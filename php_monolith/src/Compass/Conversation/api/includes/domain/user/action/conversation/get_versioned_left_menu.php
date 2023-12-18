<?php

namespace Compass\Conversation;

/**
 * Action получить левое меню по версии
 */
class Domain_User_Action_Conversation_GetVersionedLeftMenu {

	/**
	 *
	 * Получить левое меню по версии
	 *
	 */
	public static function do(int $user_id, int $version):array {

		// получаем все измененные записи
		return Gateway_Db_CompanyConversation_UserLeftMenu::getLeftMenuByVersion($user_id, $version);
	}
}
