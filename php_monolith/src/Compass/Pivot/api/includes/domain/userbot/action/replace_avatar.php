<?php

namespace Compass\Pivot;

/**
 * действие обновления аватарки пользовательского бота
 */
class Domain_Userbot_Action_ReplaceAvatar {

	// путь к файлам
	protected const _PATH_TO_FILE = PATH_WWW . "default_file/replace_files/";

	// ключ и имя файлов изображений для замены
	protected const _USERBOT_AVATAR_FILE_NAME_LIST = [
		Domain_Userbot_Entity_DefaultFile::DEFAULT_FILE_KEY_1     => "userbot_avatar_1_w400.png",
		Domain_Userbot_Entity_DefaultFile::DEFAULT_FILE_KEY_2     => "userbot_avatar_2_w400.png",
		Domain_Userbot_Entity_DefaultFile::DEFAULT_FILE_KEY_3     => "userbot_avatar_3_w400.png",
		Domain_Userbot_Entity_DefaultFile::DEFAULT_FILE_KEY_4     => "userbot_avatar_4_w400.png",
		Domain_Userbot_Entity_DefaultFile::DEFAULT_FILE_KEY_5     => "userbot_avatar_5_w400.png",
		Domain_Userbot_Entity_DefaultFile::DEFAULT_FILE_KEY_6     => "userbot_avatar_6_w400.png",
		Domain_Userbot_Entity_DefaultFile::DEFAULT_SLEEP_FILE_KEY => "userbot_avatar_sleep_w400.png",
	];

	/**
	 * выполняем
	 */
	public static function do():void {

		foreach (self::_USERBOT_AVATAR_FILE_NAME_LIST as $dictionary_key => $file_name) {

			// получаем путь к файлу для замены
			$file_path = self::_PATH_TO_FILE . $file_name;

			// достаём данные по дефолт-файлу
			$default_file = Gateway_Db_PivotSystem_DefaultFileList::get($dictionary_key);

			// отправляем замену на файловую ноду
			$url = Gateway_Socket_PivotFileBalancer::getNodeForUpload(FILE_SOURCE_AVATAR_CDN);
			Gateway_Socket_FileNode::replaceUserbotAvatar($url, $default_file->file_key, $file_path);
		}
	}
}