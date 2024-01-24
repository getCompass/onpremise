<?php

namespace Compass\Conversation;

/**
 * Action для получения мапы аватарки чата заметок
 */
class Domain_Group_Action_GetNotesDefaultAvatarFileMap {

	/**
	 * выполняем
	 */
	public static function do():string {

		// получаем аватарку из конфига компании
		$value = Domain_Conversation_Action_Config_Get::do(Domain_Conversation_Entity_Config::NOTES_AVATAR_FILE_KEY_NAME);

		$avatar_file_map = "";
		if (isset($value["value"])) {
			$avatar_file_map = $value["value"];
		}

		return $avatar_file_map;
	}
}
