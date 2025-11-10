<?php

declare(strict_types = 1);

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Request\BlockException;
use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;

/**
 * контроллер для работы с smart app
 */
class Apiv2_SmartApp extends \BaseFrame\Controller\Api {

	const ALLOW_METHODS = [
		"create",
		"edit",
		"delete",
		"getList",
		"getAuthorizationToken",
	];

	/**
	 * Метод для создания smart app
	 *
	 * @return array
	 * @throws CaseException
	 * @throws DBShardingNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 * @throws ReturnFatalException
	 * @throws BlockException
	 * @throws \cs_RowIsEmpty
	 * @long
	 */
	public function create():array {

		$title                      = $this->post(\Formatter::TYPE_STRING, "title");
		$catalog_item_id            = $this->post(\Formatter::TYPE_INT, "catalog_item_id", false);
		$smart_app_uniq_name        = $this->post(\Formatter::TYPE_STRING, "smart_app_uniq_name", false);
		$avatar_file_key            = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", false);
		$url                        = $this->post(\Formatter::TYPE_STRING, "url", false);
		$is_open_in_new_window      = $this->post(\Formatter::TYPE_INT, "is_open_in_new_window");
		$is_notifications_enabled   = $this->post(\Formatter::TYPE_INT, "is_notifications_enabled");
		$is_sound_enabled           = $this->post(\Formatter::TYPE_INT, "is_sound_enabled");
		$is_background_work_enabled = $this->post(\Formatter::TYPE_INT, "is_background_work_enabled");
		$size                       = $this->post( \Formatter::TYPE_STRING, "size");

		try {
			[$smart_app, $smart_app_user_rel, $sensitive_data] = Domain_SmartApp_Scenario_Api::create(
				$this->user_id, $title, $catalog_item_id, $smart_app_uniq_name, $avatar_file_key, $url, $is_open_in_new_window,
				$is_notifications_enabled, $is_sound_enabled, $is_background_work_enabled, $size
			);
		} catch (Domain_SmartApp_Exception_IncorrectTitle|Domain_SmartApp_Exception_IncorrectParam) {
			throw new CaseException(2217101, "incorrect params");
		} catch (Domain_SmartApp_Exception_IncorrectUrl) {
			throw new CaseException(2217102, "incorrect url");
		} catch (Domain_SmartApp_Exception_IncorrectSmartAppUniqName) {
			throw new CaseException(2217103, "incorrect smart_app_uniq_name");
		} catch (Domain_SmartApp_Exception_NotUniqSmartAppName) {
			throw new CaseException(2217104, "not uniq smart_app_uniq_name");
		} catch (Domain_SmartApp_Exception_CreateCustomSmartAppDisabled|Domain_SmartApp_Exception_CreateFromCatalogDisabled) {
			throw new CaseException(2217108, "create disabled on server");
		}

		return $this->ok([
			"smart_app"      => (object) Apiv2_Format::smartApp($smart_app, $smart_app_user_rel),
			"sensitive_data" => (object) Apiv2_Format::smartAppSensitiveData($sensitive_data),
		]);
	}

	/**
	 * Метод для редактирования smart app
	 *
	 * @return array
	 * @throws CaseException
	 * @throws DBShardingNotFoundException
	 * @throws Domain_SmartApp_Exception_IncorrectParam
	 * @throws ParamException
	 * @throws QueryFatalException
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @long
	 */
	public function edit():array {

		$smart_app_id               = $this->post(\Formatter::TYPE_INT, "smart_app_id");
		$title                      = $this->post(\Formatter::TYPE_STRING, "title", false);
		$smart_app_uniq_name        = $this->post(\Formatter::TYPE_STRING, "smart_app_uniq_name", false);
		$avatar_file_key            = $this->post(\Formatter::TYPE_STRING, "avatar_file_key", false);
		$url                        = $this->post(\Formatter::TYPE_STRING, "url", false);
		$is_open_in_new_window      = $this->post(\Formatter::TYPE_INT, "is_open_in_new_window", false);
		$is_notifications_enabled   = $this->post(\Formatter::TYPE_INT, "is_notifications_enabled", false);
		$is_sound_enabled           = $this->post(\Formatter::TYPE_INT, "is_sound_enabled", false);
		$is_background_work_enabled = $this->post(\Formatter::TYPE_INT, "is_background_work_enabled", false);
		$size                       = $this->post(\Formatter::TYPE_STRING, "size", false);

		try {
			Domain_SmartApp_Scenario_Api::edit(
				$this->user_id, $smart_app_id, $title, $smart_app_uniq_name, $avatar_file_key, $url, $is_open_in_new_window,
				$is_notifications_enabled, $is_sound_enabled, $is_background_work_enabled, $size
			);
		} catch (Domain_SmartApp_Exception_IncorrectTitle) {
			throw new CaseException(2217101, "incorrect title");
		} catch (Domain_SmartApp_Exception_IncorrectUrl) {
			throw new CaseException(2217102, "incorrect url");
		} catch (Domain_SmartApp_Exception_IncorrectSmartAppUniqName) {
			throw new CaseException(2217103, "incorrect smart_app_uniq_name");
		} catch (Domain_SmartApp_Exception_NotUniqSmartAppName) {
			throw new CaseException(2217104, "not uniq smart_app_uniq_name");
		} catch (Domain_SmartApp_Exception_IsNotCreator) {
			throw new CaseException(2217105, "user not a creator");
		} catch (Domain_SmartApp_Exception_EmptyParams) {
			throw new CaseException(2217106, "empty params");
		} catch (Domain_SmartApp_Exception_SmartAppNotFound|Domain_SmartApp_Exception_DeletedStatus) {
			throw new CaseException(2217107, "smart app not exist or deleted");
		}

		return $this->ok();
	}

	/**
	 * удаляем приложение
	 *
	 * @return array
	 * @throws BlockException
	 * @throws CaseException
	 * @throws DBShardingNotFoundException
	 * @throws ParamException
	 * @throws ParseFatalException
	 * @throws QueryFatalException
	 */
	public function delete():array {

		$smart_app_id = $this->post(\Formatter::TYPE_INT, "smart_app_id");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::SMART_APP_DELETE);

		try {
			$deleted_at = Domain_SmartApp_Scenario_Api::delete($this->user_id, $smart_app_id);
		} catch (Domain_SmartApp_Exception_IsNotCreator) {
			throw new CaseException(2217105, "user not a creator");
		} catch (Domain_SmartApp_Exception_SmartAppNotFound|Domain_SmartApp_Exception_DeletedStatus) {
			throw new CaseException(2217107, "smart app not exist or deleted");
		}

		return $this->ok([
			"deleted_at" => (int) $deleted_at,
		]);
	}

	/**
	 * Метод для получения созданны приложений пользователем
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public function getList():array {

		$smart_app_list = Domain_SmartApp_Scenario_Api::getList($this->user_id);

		return $this->ok([
			"smart_app_list" => (array) $smart_app_list,
		]);
	}

	/**
	 * Метод для получения токена авторизации в smart app
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws ParamException
	 * @throws QueryFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public function getAuthorizationToken():array {

		$entity        = $this->post(\Formatter::TYPE_STRING, "entity", false);
		$entity_key    = $this->post(\Formatter::TYPE_STRING, "entity_key", false);
		$smart_app_id  = $this->post(\Formatter::TYPE_INT, "smart_app_id");
		$client_width  = $this->post(\Formatter::TYPE_INT, "client_width");
		$client_height = $this->post(\Formatter::TYPE_INT, "client_height");

		try {
			$authorization_token = Domain_SmartApp_Scenario_Api::getAuthorizationToken(
				$this->user_id, $entity, $entity_key, $smart_app_id, $client_width, $client_height
			);
		} catch (cs_PlatformNotFound) {
			throw new ParamException("passed incorrect params");
		}

		return $this->ok([
			"authorization_token" => (string) $authorization_token,
		]);
	}
}