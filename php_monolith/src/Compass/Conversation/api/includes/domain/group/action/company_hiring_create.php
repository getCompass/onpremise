<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Action для создания групп найма компании
 */
class Domain_Group_Action_CompanyHiringCreate {

	/**
	 * создаем группы найма для компании
	 *
	 * @param int                 $creator_user_id
	 * @param Struct_File_Default $default_file_key_list
	 * @param string              $locale
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws cs_DecryptHasFailed
	 */
	public static function do(int $creator_user_id, Struct_File_Default $default_file_key_list, string $locale):void {

		$hiring_conversation_avatar_file_map = self::_getHiringConversationAvatarFileMap($default_file_key_list);

		foreach (Domain_Group_Entity_Company::HIRING_GROUP_LIST_ON_CREATE_COMPANY as $group_key_name) {

			try {
				// создаем дефолтную группу
				[$_, $meta_row] = Type_Conversation_Group::add(
					$creator_user_id,
					Domain_Group_Entity_Company::getDefaultGroupNameByKey($group_key_name, $locale),
					CONVERSATION_TYPE_GROUP_HIRING,
					true,
					false,
					$hiring_conversation_avatar_file_map,
					"",
					false,
					false
				);
			} catch (LocaleTextNotFound) {
				throw new ParseFatalException("cant find hiring conversation name");
			}

			// добавляем ее ключ в конфиг
			Domain_Conversation_Action_Config_Add::do($meta_row["conversation_map"], $group_key_name);
		}
	}

	/**
	 * получаем file_map для аватара
	 *
	 * @throws cs_DecryptHasFailed
	 */
	protected static function _getHiringConversationAvatarFileMap(Struct_File_Default $default_file_key_list):string {

		if ($default_file_key_list->hiring_conversation_avatar_file_key !== "") {
			$file_map = \CompassApp\Pack\File::doDecrypt($default_file_key_list->hiring_conversation_avatar_file_key);
		} else {
			$file_map = "";
		}

		return $file_map;
	}
}
