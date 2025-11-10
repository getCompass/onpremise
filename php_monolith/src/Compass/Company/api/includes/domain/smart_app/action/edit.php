<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Класс action для редактирования приложения
 */
class Domain_SmartApp_Action_Edit {

	/**
	 * выполняем действие
	 *
	 * @param Struct_Db_CompanyData_SmartAppList    $smart_app
	 * @param Struct_Db_CompanyData_SmartAppUserRel $smart_app_user_rel
	 * @param string|false                          $title
	 * @param string|false                          $smart_app_uniq_name
	 * @param string|false                          $avatar_file_key
	 * @param int                                   $is_default_avatar
	 * @param string|false                          $url
	 * @param int|false                             $is_open_in_new_window
	 * @param int|false                             $is_notifications_enabled
	 * @param int|false                             $is_sound_enabled
	 * @param int|false                             $is_background_work_enabled
	 * @param string|false                          $size
	 *
	 * @throws DBShardingNotFoundException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 */
	public static function do(Struct_Db_CompanyData_SmartAppList $smart_app, Struct_Db_CompanyData_SmartAppUserRel $smart_app_user_rel,
					  string|false                       $title, string|false $smart_app_uniq_name, string|false $avatar_file_key, int $is_default_avatar,
					  string|false                       $url, int|false $is_open_in_new_window, int|false $is_notifications_enabled,
					  int|false                          $is_sound_enabled, int|false $is_background_work_enabled, string|false $size):void {

		// редактируем данные приложения
		[$smart_app, $smart_app_user_rel] = self::_editSmartAppInfo($smart_app, $smart_app_user_rel, $title, $smart_app_uniq_name, $avatar_file_key, $is_default_avatar,
			$url, $is_open_in_new_window, $is_notifications_enabled, $is_sound_enabled, $is_background_work_enabled, $size);

		// отправляем ws-событие о редактировании приложения пользователю
		Gateway_Bus_Sender::smartAppEdited(Apiv2_Format::smartApp($smart_app, $smart_app_user_rel), $smart_app_user_rel->user_id);
	}

	/**
	 * редактируем данные бота
	 *
	 * @param Struct_Db_CompanyData_SmartAppList    $smart_app
	 * @param Struct_Db_CompanyData_SmartAppUserRel $smart_app_user_rel
	 * @param string|false                          $title
	 * @param string|false                          $smart_app_uniq_name
	 * @param string|false                          $avatar_file_key
	 * @param int                                   $is_default_avatar
	 * @param string|false                          $url
	 * @param int|false                             $is_open_in_new_window
	 * @param int|false                             $is_notifications_enabled
	 * @param int|false                             $is_sound_enabled
	 * @param int|false                             $is_background_work_enabled
	 * @param string|false                          $size
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @long
	 */
	protected static function _editSmartAppInfo(Struct_Db_CompanyData_SmartAppList $smart_app, Struct_Db_CompanyData_SmartAppUserRel $smart_app_user_rel,
								  string|false                       $title, string|false $smart_app_uniq_name, string|false $avatar_file_key,
								  int                                $is_default_avatar, string|false $url, int|false $is_open_in_new_window,
								  int|false                          $is_notifications_enabled, int|false $is_sound_enabled,
								  int|false                          $is_background_work_enabled, string|false $size):array {

		$smart_app_extra          = $smart_app->extra;
		$smart_app_user_rel_extra = $smart_app_user_rel->extra;

		if ($title !== false) {
			$smart_app_user_rel_extra = Domain_SmartApp_Entity_SmartAppUserRel::setTitle($smart_app_user_rel_extra, $title);
		}

		if ($is_default_avatar !== Domain_SmartApp_Entity_SmartAppUserRel::getFlagDefaultAvatar($smart_app_user_rel_extra)) {
			$smart_app_user_rel_extra = Domain_SmartApp_Entity_SmartAppUserRel::setFlagDefaultAvatar($smart_app_user_rel_extra, $is_default_avatar);
		}

		if ($avatar_file_key !== false) {
			$smart_app_user_rel_extra = Domain_SmartApp_Entity_SmartAppUserRel::setAvatarFileKey($smart_app_user_rel_extra, $avatar_file_key);
		}

		if ($url !== false) {
			$smart_app_extra = Domain_SmartApp_Entity_SmartApp::setUrl($smart_app_extra, $url);
		}

		if ($is_open_in_new_window !== false) {
			$smart_app_user_rel_extra = Domain_SmartApp_Entity_SmartAppUserRel::setFlagOpenInNewWindow($smart_app_user_rel_extra, $is_open_in_new_window);
		}

		if ($is_notifications_enabled !== false) {
			$smart_app_user_rel_extra = Domain_SmartApp_Entity_SmartAppUserRel::setFlagNotificationsEnabled($smart_app_user_rel_extra, $is_notifications_enabled);
		}

		if ($is_sound_enabled !== false) {
			$smart_app_user_rel_extra = Domain_SmartApp_Entity_SmartAppUserRel::setFlagSoundEnabled($smart_app_user_rel_extra, $is_sound_enabled);
		}

		if ($is_background_work_enabled !== false) {
			$smart_app_user_rel_extra = Domain_SmartApp_Entity_SmartAppUserRel::setFlagBackgroundWorkEnabled($smart_app_user_rel_extra, $is_background_work_enabled);
		}

		if ($size !== false) {
			$smart_app_user_rel_extra = Domain_SmartApp_Entity_SmartAppUserRel::setSize($smart_app_user_rel_extra, $size);
		}

		// если данные поменялись в приложении
		if ($smart_app_extra != $smart_app->extra || $smart_app_uniq_name !== false) {

			$set = [
				"updated_at" => time(),
			];
			if ($smart_app_uniq_name !== false) {

				$set["smart_app_uniq_name"]     = $smart_app_uniq_name;
				$smart_app->smart_app_uniq_name = $smart_app_uniq_name;
			}
			if ($smart_app_extra != $smart_app->extra) {

				// устанавливаем новые данные extra
				$smart_app->extra = $smart_app_extra;
				$set["extra"]     = $smart_app_extra;
			}
			Gateway_Db_CompanyData_SmartAppList::set($smart_app->smart_app_id, $set);
		}

		// если данные поменялись в настройках пользователя
		if ($smart_app_user_rel_extra != $smart_app_user_rel->extra) {

			// устанавливаем новые данные extra
			$smart_app_user_rel->extra = $smart_app_user_rel_extra;

			$set = [
				"updated_at" => time(),
				"extra"      => $smart_app_user_rel_extra,
			];
			Gateway_Db_CompanyData_SmartAppUserRel::set($smart_app->smart_app_id, $smart_app_user_rel->user_id, $set);
		}

		return [$smart_app, $smart_app_user_rel];
	}
}