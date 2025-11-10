<?php

namespace Compass\Company;

/**
 * Класс action для валидации параметров при редактировании приложения
 */
class Domain_SmartApp_Action_ValidateParamsOnEdit {

	/**
	 * выполняем действие
	 *
	 * @param string|false $title
	 * @param string|false $smart_app_uniq_name
	 * @param string|false $avatar_file_key
	 * @param string|false $url
	 * @param int|false    $is_open_in_new_window
	 * @param int|false    $is_notifications_enabled
	 * @param int|false    $is_sound_enabled
	 * @param int|false    $is_background_work_enabled
	 * @param string|false $size
	 *
	 * @return array
	 * @throws Domain_SmartApp_Exception_EmptyParams
	 * @throws Domain_SmartApp_Exception_IncorrectParam
	 * @throws Domain_SmartApp_Exception_IncorrectSmartAppUniqName
	 * @throws Domain_SmartApp_Exception_IncorrectTitle
	 * @throws Domain_SmartApp_Exception_IncorrectUrl
	 * @long
	 */
	public static function do(string|false $title, string|false $smart_app_uniq_name, string|false $avatar_file_key,
					  string|false $url, int|false $is_open_in_new_window, int|false $is_notifications_enabled, int|false $is_sound_enabled,
					  int|false    $is_background_work_enabled, string|false $size):array {

		// если никакой из параметров для редактирования не передан
		if ($title === false && $smart_app_uniq_name === false && $avatar_file_key === false && $url === false && $is_open_in_new_window === false
			&& $is_notifications_enabled === false && $is_sound_enabled === false && $is_background_work_enabled === false && $size === false) {

			throw new Domain_SmartApp_Exception_EmptyParams("not have param for edit");
		}

		// если передано имя
		if ($title !== false) {

			$title = Domain_SmartApp_Entity_Sanitizer::sanitizeTitle($title);
			Domain_SmartApp_Entity_Validator::assertCorrectTitle($title);
		}

		// если передано уникальное имя
		if ($smart_app_uniq_name !== false) {

			$smart_app_uniq_name = Domain_SmartApp_Entity_Sanitizer::sanitizeSmartAppUniqName($smart_app_uniq_name);
			Domain_SmartApp_Entity_Validator::assertCorrectSmartAppUniqName($smart_app_uniq_name);
		}

		if ($url !== false) {

			$url = Domain_SmartApp_Entity_Sanitizer::sanitizeUrl($url);
			Domain_SmartApp_Entity_Validator::assertCorrectUrl($url);
		}

		if ($is_open_in_new_window !== false) {
			Domain_SmartApp_Entity_Validator::assertCorrectFlag($is_open_in_new_window);
		}

		if ($is_notifications_enabled !== false) {
			Domain_SmartApp_Entity_Validator::assertCorrectFlag($is_notifications_enabled);
		}

		if ($is_sound_enabled !== false) {
			Domain_SmartApp_Entity_Validator::assertCorrectFlag($is_sound_enabled);
		}

		if ($is_background_work_enabled !== false) {
			Domain_SmartApp_Entity_Validator::assertCorrectFlag($is_background_work_enabled);
		}

		if ($size !== false) {
			Domain_SmartApp_Entity_Validator::assertCorrectSize($size);
		}

		return [$title, $smart_app_uniq_name, $url];
	}
}