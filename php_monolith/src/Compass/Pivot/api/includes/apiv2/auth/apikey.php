<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Апи ключи
 */
class Apiv2_Auth_Apikey extends \BaseFrame\Controller\Api
{
	public const ECODE_APIKEY_NAME_EXISTS         = 1222001;
	public const ECODE_APIKEY_COUNT_EXCEEDED      = 1222002;
	public const ECODE_APIKEY_NOT_FOUND           = 1222003;
	public const ECODE_APIKEY_FOR_ANOTHER_USER    = 1222004;
	public const ECODE_APIKEY_INVALID_KEY         = 1222005;
	public const ECODE_APIKEY_INVALID_NAME        = 1222006;
	public const ECODE_APIKEY_INVALID_EXPIRES_AT  = 1222007;
	public const ECODE_APIKEY_INVALID_SCOPE_LIST  = 1222008;
	public const ECODE_APIKEY_INVALID_TEMPLATE_ID = 1222009;

	// разрешенные методы
	public const ALLOW_METHODS = [
		"getTemplateList",
		"create",
		"refresh",
		"edit",
		"remove",
		"getList",
	];

	/**
	 * Получить список готовых шаблонов для интеграций
	 */
	public function getTemplateList(): array
	{

		[$apikey_template_list, $default_file_key_list, $description_locale_list] = Domain_Apikey_Scenario_Api::getTemplateList();

		return $this->ok([
			"apikey_template_list" => Apiv2_Format::apiKeyTemplateList($apikey_template_list, $default_file_key_list, $description_locale_list),
		]);
	}

	/**
	 * Создать новый API ключ
	 */
	public function create(): array
	{

		$name        = $this->post(\Formatter::TYPE_STRING, "name", "");
		$expires_at  = $this->post(\Formatter::TYPE_INT, "expires_at", MAX_UNSIGNED_INT32);
		$scope_list  = $this->post(\Formatter::TYPE_ARRAY, "scope_list", []);
		$template_id = $this->post(\Formatter::TYPE_INT, "template_id", 0);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::APIKEY_CREATE);

		try {

			$api_key_data = Domain_Apikey_Action_Create::do($this->user_id, $name, $expires_at, $scope_list, $template_id);
		} catch (Domain_Apikey_Exception_ApikeyIncorrectName) {
			throw new CaseException(static::ECODE_APIKEY_INVALID_NAME, "incorrect name");
		} catch (Domain_Apikey_Exception_ApikeyIncorrectExpiresAt) {
			throw new CaseException(static::ECODE_APIKEY_INVALID_EXPIRES_AT, "incorrect expires_at");
		} catch (Domain_Apikey_Exception_ApikeyIncorrectScopeList) {
			throw new CaseException(static::ECODE_APIKEY_INVALID_SCOPE_LIST, "incorrect scope_list");
		} catch (Domain_Apikey_Exception_ApikeyCountExceeded) {
			throw new CaseException(static::ECODE_APIKEY_COUNT_EXCEEDED, "api key count exceeded");
		} catch (Domain_Apikey_Exception_ApikeyIncorrectTemplateId) {
			throw new CaseException(static::ECODE_APIKEY_INVALID_TEMPLATE_ID, "invalid template id");
		}

		return $this->ok([
			"api_key_data" => (object) Apiv2_Format::apiKeyData($api_key_data),
		]);
	}

	/**
	 * Обновить API ключ
	 */
	public function refresh(): array
	{

		$apikey = $this->post(\Formatter::TYPE_STRING, "api_key");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::APIKEY_REFRESH);

		try {

			$api_key_data = Domain_Apikey_Action_Refresh::do($this->user_id, $apikey);
		} catch (Domain_Apikey_Exception_ApikeyNotFound) {
			throw new CaseException(static::ECODE_APIKEY_NOT_FOUND, "apikey does not exist");
		} catch (Domain_Apikey_Exception_ApikeyIncorrect) {
			throw new CaseException(static::ECODE_APIKEY_INVALID_KEY, "invalid apikey");
		} catch (Domain_Apikey_Exception_ApikeyForAnotherUser) {
			throw new CaseException(static::ECODE_APIKEY_FOR_ANOTHER_USER, "apikey belongs to another user");
		}

		return $this->ok([
			"api_key_data" => (object) Apiv2_Format::apiKeyData($api_key_data),
		]);
	}

	/**
	 * Изменить данные API ключа
	 */
	public function edit(): array
	{

		$apikey     = $this->post(\Formatter::TYPE_STRING, "api_key");
		$name       = $this->post(\Formatter::TYPE_STRING, "name", "");
		$expires_at = $this->post(\Formatter::TYPE_INT, "expires_at", 0);
		$scope_list = $this->post(\Formatter::TYPE_ARRAY, "scope_list", []);

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::APIKEY_EDIT);

		try {

			$api_key_data = Domain_Apikey_Action_Edit::do($this->user_id, $apikey, $name, $expires_at, $scope_list);
		} catch (Domain_Apikey_Exception_ApikeyNotFound) {
			throw new CaseException(static::ECODE_APIKEY_NOT_FOUND, "apikey does not exist");
		} catch (Domain_Apikey_Exception_ApikeyIncorrectName) {
			throw new CaseException(static::ECODE_APIKEY_INVALID_NAME, "incorrect name");
		} catch (Domain_Apikey_Exception_ApikeyIncorrectExpiresAt) {
			throw new CaseException(static::ECODE_APIKEY_INVALID_EXPIRES_AT, "incorrect expires_at");
		} catch (Domain_Apikey_Exception_ApikeyIncorrectScopeList) {
			throw new CaseException(static::ECODE_APIKEY_INVALID_SCOPE_LIST, "incorrect scope_list");
		} catch (Domain_Apikey_Exception_ApikeyIncorrect) {
			throw new CaseException(static::ECODE_APIKEY_INVALID_KEY, "apikey is invalid");
		} catch (Domain_Apikey_Exception_ApikeyForAnotherUser) {
			throw new CaseException(static::ECODE_APIKEY_FOR_ANOTHER_USER, "apikey for another user");
		}

		return $this->ok([
			"api_key_data" => (object) Apiv2_Format::apiKeyData($api_key_data),
		]);
	}

	/**
	 * Удалить API ключ
	 */
	public function remove(): array
	{

		$apikey = $this->post(\Formatter::TYPE_STRING, "api_key");

		Type_Antispam_User::throwIfBlocked($this->user_id, Type_Antispam_User::APIKEY_REMOVE);

		try {

			Domain_Apikey_Action_Remove::do($this->user_id, $apikey);
		} catch (Domain_Apikey_Exception_ApikeyNotFound) {

			// отдаем ок если пытаемся удалить то чего нет
			return $this->ok();
		} catch (Domain_Apikey_Exception_ApikeyIncorrect) {
			throw new CaseException(static::ECODE_APIKEY_INVALID_KEY, "apikey is invalid");
		} catch (Domain_Apikey_Exception_ApikeyForAnotherUser) {
			throw new CaseException(static::ECODE_APIKEY_FOR_ANOTHER_USER, "apikey for another user");
		}

		return $this->ok();
	}

	/**
	 * Получить список api ключей
	 */
	public function getList(): array
	{

		$api_key_list = Domain_Apikey_Action_GetList::do($this->user_id);

		return $this->ok([
			"api_key_list" => (array) $api_key_list,
		]);
	}
}
