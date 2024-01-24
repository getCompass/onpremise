<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\ParamException;

/**
 * класс для фильтрации устаревших типов диалогов
 */
class Domain_Conversation_Entity_LegacyTypes {

	/** @var array список устаревших типов */
	protected const _LEGACY_TYPE_LIST = [
		CONVERSATION_TYPE_GROUP_HIRING,
	];

	/**
	 * Проверяем, что тип диалога – легаси
	 *
	 * @return bool
	 */
	public static function isLegacy(string $conversation_type):bool {

		return in_array($conversation_type, self::_LEGACY_TYPE_LIST);
	}

	/**
	 * Фильтруем массив с сущностями левого меню
	 *
	 * @return array
	 */
	public static function filterLeftMenu(array $left_menu_list):array {

		return array_filter($left_menu_list, static fn(array $left_menu) => !self::isLegacy($left_menu["type"]));
	}

	/**
	 * Фильтруем массив с локациями, где имеются совпадения по поисковому запросу
	 *
	 * @return array
	 */
	public static function filterFindLocationList(array $location_list):array {

		return array_filter($location_list, function(Struct_Domain_Search_Location_Thread|Struct_Domain_Search_Location_Conversation $location) {

			// если это НЕ совпадение в диалоге, то скипаем
			if (false === ($location instanceof Struct_Domain_Search_Location_Conversation)) {
				return true;
			}

			// тут остались только совпадения в диалоге – фильтруем если это легаси типы диалогов
			return !self::isLegacy($location->conversation_meta["type"]);
		});
	}

	/**
	 * Выбрасываем исключение, если передали устаревший диалог
	 *
	 * @throws ParamException
	 * @throws \cs_DecryptHasFailed
	 */
	public static function throwIfLegacy(string $conversation_map):void {

		$meta_row = Type_Conversation_Meta::get($conversation_map);

		if (self::isLegacy($meta_row["type"])) {
			throw new ParamException("passed legacy conversation_key");
		}
	}
}
