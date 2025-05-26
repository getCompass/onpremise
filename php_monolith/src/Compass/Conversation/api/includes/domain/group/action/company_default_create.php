<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Action для создания дефолтных групп компании
 */
class Domain_Group_Action_CompanyDefaultCreate {

	/**
	 * создаем дефолтные группы для компании
	 *
	 * @param int    $creator_user_id
	 * @param string $locale
	 * @param bool   $is_only_card
	 * @param string $respect_conversation_avatar_file_map
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(int $creator_user_id, string $locale, bool $is_only_card = false, string $respect_conversation_avatar_file_map = ""):void {

		self::_create($creator_user_id, Domain_Group_Entity_Company::getDefaultGroupList($is_only_card), $locale, $respect_conversation_avatar_file_map);
	}

	/**
	 * создаем грыппу
	 *
	 * @param int    $creator_user_id
	 * @param array  $group_list
	 * @param string $locale
	 * @param string $respect_conversation_avatar_file_map
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @long
	 */
	protected static function _create(int $creator_user_id, array $group_list, string $locale, string $respect_conversation_avatar_file_map):void {

		foreach ($group_list as $group_key_name) {

			$value = Domain_Conversation_Action_Config_Get::do($group_key_name);
			if (isset($value["value"]) && mb_strlen($value["value"]) > 0) {
				continue;
			}

			try {
				$group_name = Domain_Group_Entity_Company::getDefaultGroupNameByKey($group_key_name, $locale);
			} catch (LocaleTextNotFound) {
				throw new ParseFatalException("cant find group default name");
			}

			$conversation_type = CONVERSATION_TYPE_GROUP_DEFAULT;
			if ($group_key_name === Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME) {
				$conversation_type = CONVERSATION_TYPE_GROUP_GENERAL;
			}

			$avatar_file_map = "";
			if ($group_key_name === Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME) {

				$avatar_file_map   = $respect_conversation_avatar_file_map;
				$conversation_type = CONVERSATION_TYPE_GROUP_RESPECT;
			}

			// создаем дефолтную группу
			$meta_row = Type_Conversation_Group::add(
				$creator_user_id, $group_name, $conversation_type, false, false, $avatar_file_map, "",
				false, false
			);

			// добавляем ее ключ в конфиг
			Domain_Conversation_Action_Config_Add::do($meta_row["conversation_map"], $group_key_name);

			// для чата Heroes при создании включаем коммит из этого чата
			if ($group_key_name === Domain_Company_Entity_Config::HEROES_CONVERSATION_KEY_NAME) {
				Helper_Groups::doChangeOptions($creator_user_id, $meta_row["conversation_map"], $meta_row, ["is_can_commit_worked_hours" => true]);
			}
		}
	}
}
