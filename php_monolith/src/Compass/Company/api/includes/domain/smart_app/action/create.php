<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Класс action для создания приложения
 */
class Domain_SmartApp_Action_Create {

	/**
	 * выполняем действие
	 *
	 * @param int       $creator_user_id
	 * @param string    $title
	 * @param int|false $catalog_item_id
	 * @param string    $smart_app_uniq_name
	 * @param string    $avatar_file_key
	 * @param int       $is_default_avatar
	 * @param string    $url
	 * @param int       $is_open_in_new_window
	 * @param int       $is_notifications_enabled
	 * @param int       $is_sound_enabled
	 * @param int       $is_background_work_enabled
	 * @param string    $size
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 */
	public static function do(int $creator_user_id, string $title, int|false $catalog_item_id, string $smart_app_uniq_name, string $avatar_file_key,
					  int $is_default_avatar, string $url, int $is_open_in_new_window, int $is_notifications_enabled, int $is_sound_enabled,
					  int $is_background_work_enabled, string $size):array {

		// генерируем ключи для приложения
		[$public_key, $private_key] = Domain_SmartApp_Action_GenerateSmartAppKeys::do();

		// добавляем приложение в список приложений на стороне компании
		$smart_app = Domain_SmartApp_Entity_SmartApp::create(
			$creator_user_id, $catalog_item_id, $smart_app_uniq_name, $url, $public_key, $private_key
		);

		$smart_app_user_rel = Domain_SmartApp_Entity_SmartAppUserRel::create(
			$smart_app->smart_app_id, $creator_user_id, Domain_SmartApp_Entity_SmartAppUserRel::STATUS_ENABLE, $title, $avatar_file_key, $is_default_avatar,
			$is_open_in_new_window, $is_notifications_enabled, $is_sound_enabled, $is_background_work_enabled, $size, time()
		);

		// отправляем ws-событие о создании приложения пользователю
		Gateway_Bus_Sender::smartAppCreated(Apiv2_Format::smartApp($smart_app, $smart_app_user_rel), $creator_user_id);

		// формируем структуру с чувствительными данными
		$sensitive_data = new Struct_Domain_SmartApp_SensitiveData($public_key);

		return [$smart_app, $smart_app_user_rel, $sensitive_data];
	}
}