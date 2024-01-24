<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Action для добавления пользователя в группы из заявки
 */
class Domain_Group_Action_CompanyRequestAdd {

	/**
	 * добавляем пользователя в группы из заявки
	 *
	 * @param int   $user_id
	 * @param array $group_conversation_autojoin_item_list
	 *
	 * @throws ParseFatalException
	 */
	public static function do(int $user_id, array $group_conversation_autojoin_item_list):void {

		// если список групп пустой, выходим
		if (count($group_conversation_autojoin_item_list) < 1) {
			return;
		}

		// получаем список map групп для вступления
		$conversation_map_list_to_join = self::_getConversationMapListToJoin($group_conversation_autojoin_item_list);

		// добавляем пользователя в каждую группу из списка
		foreach ($conversation_map_list_to_join as $v) {
			Helper_Groups::doJoin($v, $user_id);
		}
	}

	/**
	 * получаем массив map для групп
	 *
	 */
	protected static function _getConversationMapListToJoin(array $group_conversation_autojoin_item_list):array {

		$active_conversation_map_list_to_join = [];
		foreach ($group_conversation_autojoin_item_list as $v) {

			if ($v["status"] == 1) {

				$conversation_key = $v["conversation_key"];

				// преобразуем key в map
				try {
					$conversation_map = \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
				} catch (\Exception) {
					continue;
				}

				$active_conversation_map_list_to_join[] = $conversation_map;
			}
		}

		return self::_getNotEmptyConversationMapList($active_conversation_map_list_to_join);
	}

	/**
	 * Получить только заполненные группы
	 *
	 */
	protected static function _getNotEmptyConversationMapList(array $conversation_map_list):array {

		$conversation_meta_list          = Type_Conversation_Meta::getAll($conversation_map_list);
		$not_empty_conversation_map_list = [];
		foreach ($conversation_meta_list as $meta_row) {

			$member_count = 0;
			foreach ($meta_row["users"] as $k => $v) {

				if (Type_Conversation_Meta_Users::isMember($k, $meta_row["users"])) {
					$member_count++;
				}
			}
			if ($member_count !== 0) {
				$not_empty_conversation_map_list[] = $meta_row["conversation_map"];
			}
		}

		return $not_empty_conversation_map_list;
	}
}
