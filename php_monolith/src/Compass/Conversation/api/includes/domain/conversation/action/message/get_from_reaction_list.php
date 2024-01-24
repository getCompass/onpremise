<?php

namespace Compass\Conversation;

/**
 * Действие для получения списка реакций
 */
class Domain_Conversation_Action_Message_GetFromReactionList {

	/**
	 * выполняем
	 */
	public static function do(array $reaction_user_list):array {

		$output = [];
		foreach ($reaction_user_list as $reaction => $user_list) {

			asort($user_list);
			$output[$reaction] = array_keys($user_list);
		}

		return $output;
	}
}