<?php

namespace Compass\Conversation;

/**
 * Action для получения информации об оппонентах из списка мет
 */
class Domain_Conversation_Action_GetOpponentUserInfoListFromConversationList {

	/**
	 * получения информацию об оппонентах из списка мет диалогов
	 *
	 * @throws \busException
	 */
	public static function do(int $user_id, array $meta_list):array {

		// получаем id всех оппонентов
		$opponent_user_id_list = [];
		foreach ($meta_list as $v) {

			if (Type_Conversation_Meta::isSubtypeOfSingle($v["type"])) {
				$opponent_user_id_list[] = Type_Conversation_Meta_Users::getOpponentId($user_id, $v["users"]);
			}
		}

		// получаем инфу об оппонентах
		return Gateway_Bus_CompanyCache::getMemberList($opponent_user_id_list);
	}
}