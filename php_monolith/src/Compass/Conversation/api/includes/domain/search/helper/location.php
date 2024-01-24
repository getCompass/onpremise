<?php

namespace Compass\Conversation;

/**
 * Вспомогательный класс для работы с локациями
 */
class Domain_Search_Helper_Location {

	/**
	 * получить список ограниченных локаций
	 */
	public static function getRestrictedLocations(int $user_id, array $location_type_list):array {

		if (!in_array(Domain_Search_Const::TYPE_CONVERSATION, $location_type_list)) {
			return [];
		}

		$support_parent_id = static::getSupportGroupParentId($user_id);

		return $support_parent_id == 0 ? [] : [$support_parent_id];
	}

	/**
	 * получить id родителя-чата поддержки
	 */
	public static function getSupportGroupParentId(int $user_id):int {

		// получаем ключ чата поддержки
		$left_menu_row    = Type_Conversation_LeftMenu::getSupportGroupByUser($user_id);
		$conversation_map = $left_menu_row["conversation_map"];

		// получаем id родителя для таблицы поиска
		$search_rel = Domain_Search_Repository_ProxyCache_EntitySearchId::load([
			new Struct_Domain_Search_AppEntity(Domain_Search_Const::TYPE_CONVERSATION, $conversation_map),
		]);

		if (!isset($search_rel[$conversation_map])) {
			return 0;
		}

		return $search_rel[$conversation_map];
	}
}