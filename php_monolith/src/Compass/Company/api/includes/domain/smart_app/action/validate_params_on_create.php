<?php

namespace Compass\Company;

/**
 * Класс action для валидации параметров при создании приложения
 */
class Domain_SmartApp_Action_ValidateParamsOnCreate {

	/**
	 * выполняем действие
	 *
	 * @param int          $user_id
	 * @param string       $title
	 * @param int|false    $catalog_item_id
	 * @param string|false $smart_app_uniq_name
	 * @param string|false $avatar_file_key
	 * @param string|false $url
	 * @param int          $is_open_in_new_window
	 * @param int          $is_notifications_enabled
	 * @param int          $is_sound_enabled
	 * @param int          $is_background_work_enabled
	 * @param string       $size
	 *
	 * @return array
	 * @throws Domain_SmartApp_Exception_IncorrectParam
	 * @throws Domain_SmartApp_Exception_IncorrectSmartAppUniqName
	 * @throws Domain_SmartApp_Exception_IncorrectTitle
	 * @throws Domain_SmartApp_Exception_IncorrectUrl
	 * @long
	 */
	public static function do(int $user_id, string $title, int|false $catalog_item_id, string|false $smart_app_uniq_name, string|false $avatar_file_key, string|false $url,
					  int $is_open_in_new_window, int $is_notifications_enabled, int $is_sound_enabled, int $is_background_work_enabled, string $size):array {

		// проверяем данные приложения на корректность
		$title = Domain_SmartApp_Entity_Sanitizer::sanitizeTitle($title);
		Domain_SmartApp_Entity_Validator::assertCorrectTitle($title);
		Domain_SmartApp_Entity_Validator::assertCorrectFlag($is_open_in_new_window);
		Domain_SmartApp_Entity_Validator::assertCorrectFlag($is_notifications_enabled);
		Domain_SmartApp_Entity_Validator::assertCorrectFlag($is_sound_enabled);
		Domain_SmartApp_Entity_Validator::assertCorrectFlag($is_background_work_enabled);
		Domain_SmartApp_Entity_Validator::assertCorrectSize($size);

		// если это приложение НЕ из каталога
		if ($catalog_item_id === false || $catalog_item_id < 1) {

			// клиенты любят слать -1, поэтому отдельно проверяем
			if ($catalog_item_id !== false && $catalog_item_id < 0) {
				throw new Domain_SmartApp_Exception_IncorrectParam("incorrect catalog_item_id = {$catalog_item_id}");
			}

			// если отключено создание кастомных смарт аппов на сервере
			if (Domain_SmartApp_Entity_Restrictions::isCreateCustomSmartAppsDisabled()) {
				throw new Domain_SmartApp_Exception_CreateCustomSmartAppDisabled("custom smart apps creating disabled on server");
			}

			// если не передали аватар - берем дефолтный
			$is_default_avatar = 0;
			if ($avatar_file_key === false) {

				$avatar_file_key   = Gateway_Socket_Pivot::getSmartAppDefaultAvatar($user_id);
				$is_default_avatar = 1;
			}

			Domain_SmartApp_Entity_Validator::assertCorrectAvatarFileKey($avatar_file_key);

			$smart_app_uniq_name = Domain_SmartApp_Entity_Sanitizer::sanitizeSmartAppUniqName($smart_app_uniq_name);
			Domain_SmartApp_Entity_Validator::assertCorrectSmartAppUniqName($smart_app_uniq_name);

			$url = Domain_SmartApp_Entity_Sanitizer::sanitizeUrl($url);
			Domain_SmartApp_Entity_Validator::assertCorrectUrl($url);

			return [
				$title, $catalog_item_id, $smart_app_uniq_name, $avatar_file_key, $url,
				$is_open_in_new_window, $is_notifications_enabled, $is_sound_enabled, $is_background_work_enabled, $size, $is_default_avatar,
			];
		}

		Domain_SmartApp_Entity_Validator::assertCorrectCatalogItemId($catalog_item_id);

		// если отключено создание смарт аппов из каталога на сервере
		if (Domain_SmartApp_Entity_Restrictions::isCreateFromCatalogDisabled()) {
			throw new Domain_SmartApp_Exception_CreateFromCatalogDisabled("smart apps create from catalog disabled on server");
		}

		try {
			[$smart_app_uniq_name, $avatar_file_key, $url] = Gateway_Socket_Pivot::getSmartAppCatalogItem($user_id, $catalog_item_id);
		} catch (\Exception) {
			throw new Domain_SmartApp_Exception_IncorrectParam("incorrect catalog_item_id = {$catalog_item_id}");
		}

		$is_default_avatar = 1;
		Domain_SmartApp_Entity_Validator::assertCorrectAvatarFileKey($avatar_file_key);

		$smart_app_uniq_name = Domain_SmartApp_Entity_Sanitizer::sanitizeSmartAppUniqName($smart_app_uniq_name);
		Domain_SmartApp_Entity_Validator::assertCorrectSmartAppUniqName($smart_app_uniq_name);

		$url = Domain_SmartApp_Entity_Sanitizer::sanitizeUrl($url);
		Domain_SmartApp_Entity_Validator::assertCorrectUrl($url);

		return [
			$title, $catalog_item_id, $smart_app_uniq_name, $avatar_file_key, $url,
			$is_open_in_new_window, $is_notifications_enabled, $is_sound_enabled, $is_background_work_enabled, $size, $is_default_avatar,
		];
	}
}