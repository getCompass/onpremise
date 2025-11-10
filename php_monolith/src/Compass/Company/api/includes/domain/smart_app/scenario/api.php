<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\ParamException;

/**
 * сценарии smart app для API
 */
class Domain_SmartApp_Scenario_Api {

	/**
	 * Сценарий создания приложения
	 *
	 * @param int          $user_id
	 * @param string       $title
	 * @param int|false    $catalog_item_id
	 * @param string       $smart_app_uniq_name
	 * @param string|false $avatar_file_key
	 * @param string       $url
	 * @param int          $is_open_in_new_window
	 * @param int          $is_notifications_enabled
	 * @param int          $is_sound_enabled
	 * @param int          $is_background_work_enabled
	 * @param string       $size
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws Domain_SmartApp_Exception_IncorrectParam
	 * @throws Domain_SmartApp_Exception_IncorrectSmartAppUniqName
	 * @throws Domain_SmartApp_Exception_IncorrectTitle
	 * @throws Domain_SmartApp_Exception_IncorrectUrl
	 * @throws Domain_SmartApp_Exception_NotUniqSmartAppName
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws BlockException
	 * @throws \cs_RowIsEmpty
	 */
	public static function create(int $user_id, string $title, int|false $catalog_item_id, string|false $smart_app_uniq_name, string|false $avatar_file_key, string|false $url,
						int $is_open_in_new_window, int $is_notifications_enabled, int $is_sound_enabled, int $is_background_work_enabled, string $size):array {

		// валидируем параметры для приложения
		[
			$title, $catalog_item_id, $smart_app_uniq_name, $avatar_file_key, $url,
			$is_open_in_new_window, $is_notifications_enabled, $is_sound_enabled, $is_background_work_enabled, $size, $is_default_avatar,
		] = Domain_SmartApp_Action_ValidateParamsOnCreate::do(
			$user_id, $title, $catalog_item_id, $smart_app_uniq_name, $avatar_file_key, $url,
			$is_open_in_new_window, $is_notifications_enabled, $is_sound_enabled, $is_background_work_enabled, $size
		);

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::SMART_APP_CREATE);

		// проверяем что другого такого имени в команде нет
		Domain_SmartApp_Entity_Validator::assertUniqSmartAppName($smart_app_uniq_name, $catalog_item_id);

		// создаём приложение
		[$smart_app, $smart_app_user_rel, $sensitive_data] = Domain_SmartApp_Action_Create::do(
			$user_id, $title, $catalog_item_id, $smart_app_uniq_name, $avatar_file_key, $is_default_avatar, $url, $is_open_in_new_window,
			$is_notifications_enabled, $is_sound_enabled, $is_background_work_enabled, $size
		);

		return [$smart_app, $smart_app_user_rel, $sensitive_data];
	}

	/**
	 * Сценарий редактирования приложения
	 *
	 * @param int          $user_id
	 * @param int          $smart_app_id
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
	 * @return void
	 * @throws BlockException
	 * @throws DBShardingNotFoundException
	 * @throws Domain_SmartApp_Exception_DeletedStatus
	 * @throws Domain_SmartApp_Exception_EmptyParams
	 * @throws Domain_SmartApp_Exception_IncorrectParam
	 * @throws Domain_SmartApp_Exception_IncorrectSmartAppUniqName
	 * @throws Domain_SmartApp_Exception_IncorrectTitle
	 * @throws Domain_SmartApp_Exception_IncorrectUrl
	 * @throws Domain_SmartApp_Exception_IsNotCreator
	 * @throws Domain_SmartApp_Exception_NotUniqSmartAppName
	 * @throws Domain_SmartApp_Exception_SmartAppNotFound
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws \cs_RowIsEmpty
	 * @long
	 */
	public static function edit(int          $user_id, int $smart_app_id, string|false $title, string|false $smart_app_uniq_name, string|false $avatar_file_key,
					    string|false $url, int|false $is_open_in_new_window, int|false $is_notifications_enabled, int|false $is_sound_enabled,
					    int|false    $is_background_work_enabled, string|false $size):void {

		// валидируем параметры для приложения
		[$title, $smart_app_uniq_name, $url] = Domain_SmartApp_Action_ValidateParamsOnEdit::do(
			$title, $smart_app_uniq_name, $avatar_file_key, $url, $is_open_in_new_window, $is_notifications_enabled, $is_sound_enabled,
			$is_background_work_enabled, $size
		);

		Type_Antispam_User::throwIfBlocked($user_id, Type_Antispam_User::SMART_APP_EDIT);

		try {
			$smart_app_user_rel = Gateway_Db_CompanyData_SmartAppUserRel::getOne($smart_app_id, $user_id);
		} catch (Domain_SmartApp_Exception_SmartAppNotFound) {

			// если не создатель приложения
			throw new Domain_SmartApp_Exception_IsNotCreator("is not creator of smart app");
		}

		// если приложение удалено
		if ($smart_app_user_rel->status == Domain_SmartApp_Entity_SmartAppUserRel::STATUS_DELETE) {
			throw new Domain_SmartApp_Exception_DeletedStatus("smart app is deleted");
		}

		// получаем информацию по приложению
		$smart_app = Gateway_Db_CompanyData_SmartAppList::getOne($smart_app_id);

		// проверяем что другого такого имени в команде нет
		if ($smart_app_uniq_name !== false) {
			Domain_SmartApp_Entity_Validator::assertUniqSmartAppName($smart_app_uniq_name, $smart_app->catalog_item_id);
		}

		$is_default_avatar = Domain_SmartApp_Entity_SmartAppUserRel::getFlagDefaultAvatar($smart_app_user_rel->extra);
		if ($avatar_file_key !== false) {
			$is_default_avatar = 0;
		}

		// если удалили аватар - ставим дефолтный
		if ($avatar_file_key !== false && mb_strlen($avatar_file_key) < 1) {

			$avatar_file_key   = Gateway_Socket_Pivot::getSmartAppDefaultAvatar($user_id);
			$is_default_avatar = 1;
		}

		Domain_SmartApp_Entity_Validator::assertCorrectAvatarFileKey($avatar_file_key);

		// редактируем приложение
		Domain_SmartApp_Action_Edit::do(
			$smart_app, $smart_app_user_rel, $title, $smart_app_uniq_name, $avatar_file_key, $is_default_avatar, $url, $is_open_in_new_window,
			$is_notifications_enabled, $is_sound_enabled, $is_background_work_enabled, $size
		);
	}

	/**
	 * удаляем приложение
	 *
	 * @param int $user_id
	 * @param int $smart_app_id
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws Domain_SmartApp_Exception_DeletedStatus
	 * @throws Domain_SmartApp_Exception_IsNotCreator
	 * @throws Domain_SmartApp_Exception_SmartAppNotFound
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 */
	public static function delete(int $user_id, int $smart_app_id):int {

		try {
			$smart_app_user_rel = Gateway_Db_CompanyData_SmartAppUserRel::getOne($smart_app_id, $user_id);
		} catch (Domain_SmartApp_Exception_SmartAppNotFound) {

			// если не создатель приложения
			throw new Domain_SmartApp_Exception_IsNotCreator("is not creator of smart app");
		}

		// получаем информацию по приложению
		$smart_app = Gateway_Db_CompanyData_SmartAppList::getOne($smart_app_id);

		// удаляем приложение
		return Domain_SmartApp_Action_Delete::do($smart_app, $smart_app_user_rel);
	}

	/**
	 * Получить список приложений пользователя
	 *
	 * @param int $user_id
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getList(int $user_id):array {

		$smart_app_user_rel_count = Gateway_Db_CompanyData_SmartAppUserRel::getActiveCountByUserId($user_id);
		$smart_app_user_rel_list  = Gateway_Db_CompanyData_SmartAppUserRel::getActiveListByUserId($user_id, $smart_app_user_rel_count);

		$smart_app_id_list         = [];
		$smart_app_id_user_rel_map = [];
		foreach ($smart_app_user_rel_list as $smart_app_user_rel) {

			$smart_app_id_list[]                                          = $smart_app_user_rel->smart_app_id;
			$smart_app_id_user_rel_map[$smart_app_user_rel->smart_app_id] = $smart_app_user_rel;
		}

		$smart_app_list = Gateway_Db_CompanyData_SmartAppList::getList($smart_app_id_list);

		$output = [];
		foreach ($smart_app_list as $smart_app) {
			$output[] = Apiv2_Format::smartApp($smart_app, $smart_app_id_user_rel_map[$smart_app->smart_app_id]);
		}

		usort($output, function(array $a, array $b) {
			return $a["created_at"] > $b["created_at"] ? 1 : -1;
		});

		return $output;
	}

	/**
	 * Получить токен авторизации в приложении
	 *
	 * @param int          $user_id
	 * @param string|false $entity
	 * @param string|false $entity_key
	 * @param int          $smart_app_id
	 * @param int          $client_width
	 * @param int          $client_height
	 *
	 * @return string
	 * @throws DBShardingNotFoundException
	 * @throws ParamException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \returnException
	 * @throws cs_PlatformNotFound
	 */
	public static function getAuthorizationToken(int $user_id, string|false $entity, string|false $entity_key, int $smart_app_id,
								   int $client_width, int $client_height):string {

		// валидируем параметры
		Domain_SmartApp_Action_ValidateParams::do($entity, $entity_key, $smart_app_id, $client_width, $client_height);

		return Domain_SmartApp_Action_GetAuthorizationToken::do($user_id, $entity, $entity_key, $smart_app_id, $client_width, $client_height);
	}
}
