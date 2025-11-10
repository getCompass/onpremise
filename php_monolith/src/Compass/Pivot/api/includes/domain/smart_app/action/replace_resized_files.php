<?php

namespace Compass\Pivot;

/**
 * действие обновления файлов smart apps
 */
class Domain_SmartApp_Action_ReplaceResizedFiles {

	// путь к файлам
	protected const _PATH_TO_FILE = PATH_WWW . "default_file/replace_files/";

	// ключ и имя файлов изображений для замены
	protected const _SMART_APP_AVATAR_FILE_NAME_LIST = [
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_YANDEX_NOTES  => [
			[
				"name"         => "smart_app_avatar_6_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_6_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_6_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_6_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_YANDEX_MAIL  => [
			[
				"name"         => "smart_app_avatar_12_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_12_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_12_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_12_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_MIRO  => [
			[
				"name"         => "smart_app_avatar_23_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_23_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_23_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_23_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_GOOGLE_MEET  => [
			[
				"name"         => "smart_app_avatar_35_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_35_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_35_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_35_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_GITHUB       => [
			[
				"name"         => "smart_app_avatar_43_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_43_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_43_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_43_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_SENTRY       => [
			[
				"name"         => "smart_app_avatar_47_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_47_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_47_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_47_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_CHATGPT      => [
			[
				"name"         => "smart_app_avatar_54_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_54_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_54_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_54_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_DEEPSEEK     => [
			[
				"name"         => "smart_app_avatar_55_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_55_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_55_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_55_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_SABY         => [
			[
				"name"         => "smart_app_avatar_58_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_58_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_58_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_58_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_1C_FRESH         => [
			[
				"name"         => "smart_app_avatar_60_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_60_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_60_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_60_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_JIVO_SITE    => [
			[
				"name"         => "smart_app_avatar_98_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_98_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_98_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_98_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_CARROT_QUEST => [
			[
				"name"         => "smart_app_avatar_99_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_99_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_99_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_99_w400.png",
				"replace_size" => 400,
			],
		],
		Domain_SmartApp_Entity_DefaultFile::DEFAULT_FILE_KEY_CDEK         => [
			[
				"name"         => "smart_app_avatar_100_w80.png",
				"replace_size" => 80,
			],
			[
				"name"         => "smart_app_avatar_100_w180.png",
				"replace_size" => 180,
			],
			[
				"name"         => "smart_app_avatar_100_w300.png",
				"replace_size" => 300,
			],
			[
				"name"         => "smart_app_avatar_100_w400.png",
				"replace_size" => 400,
			],
		],
	];

	/**
	 * выполняем
	 */
	public static function do():void {

		foreach (self::_SMART_APP_AVATAR_FILE_NAME_LIST as $dictionary_key => $file_list) {

			foreach ($file_list as $file) {

				$file_name    = $file["name"];
				$replace_size = $file["replace_size"];

				// получаем путь к файлу для замены
				$file_path = self::_PATH_TO_FILE . $file_name;

				// достаём данные по дефолт-файлу
				$default_file = Gateway_Db_PivotSystem_DefaultFileList::get($dictionary_key);

				// отправляем замену на файловую ноду
				$url = Gateway_Socket_PivotFileBalancer::getNodeForUpload(FILE_SOURCE_AVATAR_CDN);
				Gateway_Socket_FileNode::replaceResizedDefaultFile($url, $default_file->file_key, $replace_size, $file_path);
			}
		}
	}
}